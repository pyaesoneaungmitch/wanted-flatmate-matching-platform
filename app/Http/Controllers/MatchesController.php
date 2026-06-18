<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MatchesController extends Controller
{
    public function index(Request $request)
    {
        $me = Auth::user()->user_id;
        $city = $request->query('city');
        $ageMin = $request->query('age_min');
        $ageMax = $request->query('age_max');
        $sort = $request->query('sort', 'score');

        // -----------------------------
        // A) Mutual matches
        // -----------------------------
        $matches = DB::table('matches as m')
            ->where(function ($q) use ($me) {
                $q->where('m.user1_id', $me)->orWhere('m.user2_id', $me);
            })
            ->orderByDesc('m.matched_at')
            ->get([
                'm.match_id',
                'm.user1_id',
                'm.user2_id',
                'm.matched_at',
                'm.private_share',
                DB::raw("CASE WHEN m.user1_id = {$me} THEN m.user2_id ELSE m.user1_id END AS other_user_id"),
            ]);

        $matchOtherIds = $matches->pluck('other_user_id')->map(fn($x)=>(int)$x)->unique()->values()->all();

        // -----------------------------
        // B) "Liked you" (they liked me, but no match yet)
        // -----------------------------
        // Condition:
        // - swipes.to_user_id = me AND like_flag = 1
        // - and there is NO match row between me and them
        $likedYou = DB::table('swipes as s')
            ->where('s.to_user_id', $me)
            ->where('s.like_flag', 1)
            ->whereNotExists(function ($q) use ($me) {
                $q->select(DB::raw(1))
                  ->from('matches as m')
                  ->whereRaw('m.user1_id = LEAST(?, s.from_user_id)', [$me])
                  ->whereRaw('m.user2_id = GREATEST(?, s.from_user_id)', [$me]);
            })
            ->orderByDesc('s.created_at')
            ->get([
                DB::raw('s.from_user_id as other_user_id'),
                's.created_at as liked_at'
            ]);

        $likedOtherIds = $likedYou->pluck('other_user_id')->map(fn($x)=>(int)$x)->unique()->values()->all();

        // -----------------------------
        // Shared lookup: public_profile + 1 photo + score
        // -----------------------------
        $allOtherIds = collect(array_merge($matchOtherIds, $likedOtherIds))->unique()->values()->all();

        $profiles = DB::table('public_profile')
            ->whereIn('user_id', $allOtherIds)
            ->get(['user_id','display_name','age','bio','city','budget_min','budget_max'])
            ->keyBy('user_id');

        $photos = DB::table('gallery')
            ->whereIn('user_id', $allOtherIds)
            ->select('user_id', DB::raw('MIN(photo_url) as photo_url'))
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        // Scores: grab any compatibility rows involving me and these users
        $scores = DB::table('compatibility')
            ->where(function($q) use ($me, $allOtherIds){
                // pairs are stored ordered, so match (min, max)
                $q->whereIn('user_id_a', array_map(fn($id)=>min($me,$id), $allOtherIds))
                  ->orWhereIn('user_id_b', array_map(fn($id)=>max($me,$id), $allOtherIds));
            })
            ->get(['user_id_a','user_id_b','score_100'])
            ->mapWithKeys(function($r){
                return [($r->user_id_a . '-' . $r->user_id_b) => (int)$r->score_100];
            });

        $getScore = function(int $otherId) use ($me, $scores) {
            $u1 = min($me, $otherId);
            $u2 = max($me, $otherId);
            return $scores[$u1.'-'.$u2] ?? 0;
        };

        // Build cards for view
        $matchCards = [];
        foreach ($matches as $m) {
            $oid = (int)$m->other_user_id;
            if (!isset($profiles[$oid])) continue;

            $p = $profiles[$oid];
            $matchCards[] = [
                'type' => 'MATCH',
                'match_id' => (int)$m->match_id,
                'other_user_id' => $oid,
                'score_100' => $getScore($oid),
                'display_name' => $p->display_name,
                'age' => $p->age,
                'bio' => $p->bio,
                'city' => $p->city,
                'budget_min' => $p->budget_min,
                'budget_max' => $p->budget_max,
                'photo_url' => $photos[$oid]->photo_url ?? null,
                'private_share' => (int)$m->private_share === 1,
            ];
        }

        $likedCards = [];
        foreach ($likedYou as $row) {
            $oid = (int)$row->other_user_id;
            if (!isset($profiles[$oid])) continue;

            $p = $profiles[$oid];
            $likedCards[] = [
                'type' => 'LIKED_YOU',
                'other_user_id' => $oid,
                'score_100' => $getScore($oid),
                'display_name' => $p->display_name,
                'age' => $p->age,
                'bio' => $p->bio,
                'city' => $p->city,
                'budget_min' => $p->budget_min,
                'budget_max' => $p->budget_max,
                'photo_url' => $photos[$oid]->photo_url ?? null,
                'liked_at' => $row->liked_at,
            ];
        }
                $applyFilters = function(array $cards) use ($city, $ageMin, $ageMax) {
            return array_values(array_filter($cards, function($c) use ($city, $ageMin, $ageMax) {
                if ($city && ($c['city'] ?? null) !== $city) return false;

                if ($ageMin !== null && $ageMin !== '') {
                    if (!isset($c['age']) || $c['age'] === null || (int)$c['age'] < (int)$ageMin) return false;
                }
                if ($ageMax !== null && $ageMax !== '') {
                    if (!isset($c['age']) || $c['age'] === null || (int)$c['age'] > (int)$ageMax) return false;
                }
                return true;
            }));
        };

        $matchCards = $applyFilters($matchCards);
        $likedCards = $applyFilters($likedCards);

        $sortCards = function(array &$cards) use ($sort) {
            if ($sort === 'age') {
                usort($cards, fn($a,$b) => ((int)($a['age'] ?? 999)) <=> ((int)($b['age'] ?? 999)));
            } elseif ($sort === 'budget') {
                usort($cards, fn($a,$b) => ((int)($a['budget_max'] ?? 999999)) <=> ((int)($b['budget_max'] ?? 999999)));
            } else { // score
                usort($cards, fn($a,$b) => ((int)$b['score_100']) <=> ((int)$a['score_100']));
            }
        };

        $sortCards($matchCards);
        $sortCards($likedCards);

        return view('matches', [
            'matchCards' => $matchCards,
            'likedCards' => $likedCards,
        ]);
    }
}