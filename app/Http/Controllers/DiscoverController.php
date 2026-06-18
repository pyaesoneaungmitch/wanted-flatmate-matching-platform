<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
class DiscoverController extends Controller
{
    public function index()
    {
        $me = Auth::user()->user_id;

        $mode = request()->query('mode', 'fresh');          // fresh / second_chance
        $scoreMode = request()->query('score', 'rule');     // rule / ai
        $isAi = ($scoreMode === 'ai');

        if (!in_array($mode, ['fresh','second_chance'], true)) {
            $mode = 'fresh';
        }

        $city = request()->query('city');
        $ageMin = request()->query('age_min');
        $ageMax = request()->query('age_max');
        $pref = request()->query('pref', 'any');
        $sort = request()->query('sort', 'score');
        // Candidates from compatibility table (top scores), excluding already swiped
        $otherExpr = "CASE WHEN c.user_id_a = {$me} THEN c.user_id_b ELSE c.user_id_a END";

        $base = DB::table('compatibility as c')
            ->where(function ($q) use ($me) {
                $q->where('c.user_id_a', $me)->orWhere('c.user_id_b', $me);
            });

        if ($mode === 'fresh') {
            // show only unswiped
            $base->whereNotExists(function ($q) use ($me, $otherExpr) {
                $q->select(DB::raw(1))
                ->from('swipes as s')
                ->where('s.from_user_id', $me)
                ->whereRaw("s.to_user_id = {$otherExpr}");
            });
        } else {
            // mode === 'second_chance': show only previously passed users
            $base->whereExists(function ($q) use ($me, $otherExpr) {
                $q->select(DB::raw(1))
                ->from('swipes as s')
                ->where('s.from_user_id', $me)
                ->where('s.like_flag', 0)
                ->where('s.pass_stage', 1)
                ->whereRaw("s.to_user_id = {$otherExpr}");
            });
        }

        $candidates = $base
            ->orderByDesc('c.score_100')
            ->limit(25)
            ->get([
                DB::raw("{$otherExpr} AS other_user_id"),
                'c.score_100',
            ]);

        $otherIds = $candidates->pluck('other_user_id')->all();

        // Pull public profile + one photo (optional)
        $profiles = DB::table('public_profile as p')
            ->whereIn('p.user_id', $otherIds)
            ->get(['p.user_id','p.display_name','p.age','p.bio','p.city','p.budget_min','p.budget_max'])
            ->keyBy('user_id');

        $photos = DB::table('gallery')
            ->whereIn('user_id', $otherIds)
            ->select('user_id', DB::raw('MIN(photo_url) as photo_url'))
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

            // Filter by city + age using public_profile
        $filteredIds = [];
        foreach ($otherIds as $oid) {
            $oid = (int)$oid;
            if (!isset($profiles[$oid])) continue;

            $p = $profiles[$oid];

            if ($city && $p->city !== $city) continue;

            if ($ageMin !== null && $ageMin !== '') {
                if ($p->age === null || (int)$p->age < (int)$ageMin) continue;
            }
            if ($ageMax !== null && $ageMax !== '') {
                if ($p->age === null || (int)$p->age > (int)$ageMax) continue;
            }

            $filteredIds[] = $oid;
        }

        // Preference radio (based on latest quiz of the candidate)
        if ($pref !== 'any' && count($filteredIds) > 0) {
            $prefColumn = match($pref) {
                'student' => 'pref_student',
                'gym' => 'hobby_gym',
                'quiet' => 'pref_noise_sensitive',
                default => null,
            };

            if ($prefColumn) {
                // build latest quiz per user for filtered users
                $latestQuiz = DB::table('quiz_responses as qr')
                    ->join(DB::raw('(SELECT user_id, MAX(created_at) AS max_created_at FROM quiz_responses GROUP BY user_id) latest'),
                        function($join){
                            $join->on('qr.user_id','=','latest.user_id')
                                ->on('qr.created_at','=','latest.max_created_at');
                        })
                    ->whereIn('qr.user_id', $filteredIds)
                    ->select('qr.user_id', "qr.$prefColumn")
                    ->get()
                    ->keyBy('user_id');

                $filteredIds = array_values(array_filter($filteredIds, function($uid) use ($latestQuiz, $prefColumn){
                    return isset($latestQuiz[$uid]) && (int)$latestQuiz[$uid]->{$prefColumn} === 1;
                }));
            }
}
        // Combine into an ordered list matching $candidates order
        $cards = [];
        foreach ($candidates as $c) {
            $pid = (int)$c->other_user_id;
            if (!in_array($pid, $filteredIds, true)) continue;

            $cards[] = [
                'user_id' => $pid,
                'score_100' => (int)$c->score_100,
                'display_name' => $profiles[$pid]->display_name,
                'age' => $profiles[$pid]->age,
                'bio' => $profiles[$pid]->bio,
                'city' => $profiles[$pid]->city,
                'budget_min' => $profiles[$pid]->budget_min,
                'budget_max' => $profiles[$pid]->budget_max,
                'photo_url' => $photos[$pid]->photo_url ?? null,
            ];
        }
        $hasPassed = DB::table('swipes')
            ->where('from_user_id', $me)
            ->where('like_flag', 0)
            ->exists();
            if ($sort === 'age') {
                usort($cards, fn($a,$b) => ((int)($a['age'] ?? 999)) <=> ((int)($b['age'] ?? 999)));
            } 
            elseif ($sort === 'budget') {
                usort($cards, fn($a,$b) => ((int)($a['budget_max'] ?? 999999)) <=> ((int)($b['budget_max'] ?? 999999)));
            } 
            else {
                usort($cards, fn($a,$b) => ((int)$b['score_100']) <=> ((int)$a['score_100']));
            }
        // ---- AI MODE (beta): override score ordering using Flask p_match ----
            if ($isAi) {
                $meQuiz = DB::table('quiz_responses')
                    ->where('user_id', $me)
                    ->orderByDesc('created_at')
                    ->first();

                if ($meQuiz) {
                    $aiUrl = env('AI_SCORER_URL', 'http://127.0.0.1:5005/predict');
                    $threshold = (float)env('AI_MATCH_THRESHOLD', 0.5);

                    foreach ($cards as &$card) {
                        $otherId = (int)$card['user_id'];

                        $otherQuiz = DB::table('quiz_responses')
                            ->where('user_id', $otherId)
                            ->orderByDesc('created_at')
                            ->first();

                        if (!$otherQuiz) {
                            $card['ai_p'] = null;
                            $card['ai_score_100'] = $card['score_100'];
                            continue;
                        }

                        $payload = $this->buildAiPayload((array)$meQuiz, (array)$otherQuiz);
                        $payload['_threshold'] = $threshold;

                        try {
                            $resp = \Illuminate\Support\Facades\Http::timeout(3)->post($aiUrl, $payload);
                            if (!$resp->ok()) throw new \Exception('AI API failed');

                            $p = (float)($resp->json()['p_match'] ?? 0.0);

                        $aiScore = (int) max(0, min(100, round(pow(max(0.0, $p), 0.25) * 100) + 5));

                        $card['ai_p'] = $p;
                        $card['ai_score_100'] = $aiScore;
                        $card['ai_conf_100']  = $aiScore;

                        DB::table('ai_compatibility')->updateOrInsert(
                        ['user_id_a' => $u1, 'user_id_b' => $u2],
                        ['p_match' => $p, 'ai_score_100' => $aiScore, 'generated_at' => now()]
                        )
                        ;

                        } catch (\Throwable $e) {
                            $card['ai_p'] = null;
                            $card['ai_score_100'] = $card['score_100'];
                        }
                    }
                    unset($card);

                    // sort by AI score desc (override normal sort)
                   
                }
            }

            if ($isAi) {
                $ids = array_map(fn($c) => (int)$c['user_id'], $cards);

                $aiRows = DB::table('ai_compatibility')
                    ->where(function($q) use ($me, $ids) {
                        foreach ($ids as $oid) {
                            $u1 = min($me, $oid);
                            $u2 = max($me, $oid);
                            $q->orWhere(function($qq) use ($u1, $u2) {
                                $qq->where('user_id_a', $u1)->where('user_id_b', $u2);
                            });
                        }
                    })
                    ->get(['user_id_a','user_id_b','ai_score_100','p_match'])
                    ->mapWithKeys(fn($r) => [($r->user_id_a.'-'.$r->user_id_b) => $r]);

                foreach ($cards as &$card) {
                    $oid = (int)$card['user_id'];
                    $u1 = min($me, $oid);
                    $u2 = max($me, $oid);
                    $key = $u1.'-'.$u2;

                    if (isset($aiRows[$key])) {
                        $card['ai_score_100'] = (int)$aiRows[$key]->ai_score_100;
                        $card['ai_p'] = (float)$aiRows[$key]->p_match;
                    }
                }
                unset($card);

                
                usort($cards, fn($a,$b) => ((int)($b['ai_score_100'] ?? 0)) <=> ((int)($a['ai_score_100'] ?? 0)));
            }

        return view('discover', [
            'cards' => $cards,
            'mode' => $mode,
            'scoreMode' => $scoreMode,
            'hasPassed' => $hasPassed,
        ]);
            }
    private function buildAiPayload(array $u1, array $u2): array
    {
        $keys = [
            'looking_for_type','city','budget_min','budget_max',
            'hobby_gym','hobby_gaming','hobby_cooking','hobby_creative','hobby_hiking',
            'hobby_music','hobby_movies','hobby_foodie','hobby_partying','hobby_reading',
            'pref_smoking','pref_drinking','pref_introverted','pref_clean_high',
            'pref_noise_sensitive','pref_guests_ok','pref_student','pref_night_owl'
        ];

        $payload = [];
        foreach ($keys as $k) {
            $payload["u1_{$k}"] = $u1[$k] ?? 0;
            $payload["u2_{$k}"] = $u2[$k] ?? 0;
        }
        return $payload;
    }
}
