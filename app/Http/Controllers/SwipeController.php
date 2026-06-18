<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SwipeController extends Controller
{
    public function store(Request $request)
    {
                $me = Auth::user()->user_id;
        

        $data = $request->validate([
            'to_user_id' => ['required','integer','min:1'],
            'like' => ['required','boolean'],
            'mode' => ['nullable','in:fresh,second_chance'],
        ]);
        $mode = $data['mode'] ?? 'fresh';

        $to = (int)$data['to_user_id'];
        if ($to === $me) abort(400);

        // If like, check mutual and create match
        $matchCreated = false;
        $matchId = null;

        if ($data['like']) {
            $theyLiked = DB::table('swipes')
                ->where('from_user_id', $to)
                ->where('to_user_id', $me)
                ->where('like_flag', 1)
                ->exists();

            if ($theyLiked) {
                $u1 = min($me, $to);
                $u2 = max($me, $to);

                DB::table('matches')->updateOrInsert(
                    ['user1_id' => $u1, 'user2_id' => $u2],
                    ['matched_at' => now(), 'private_share' => 0]
                );

                $matchId = DB::table('matches')
                    ->where('user1_id', $u1)->where('user2_id', $u2)
                    ->value('match_id');

                $matchCreated = true;
            }
        }

        

        $mode = $data['mode'] ?? 'fresh';

            $existing = DB::table('swipes')
            ->where('from_user_id', $me)
            ->where('to_user_id', $to)
            ->first();

            $likeFlag = $data['like'] ? 1 : 0;

            $passStage = $existing->pass_stage ?? 0;

            if ($likeFlag === 1) {
                // Liked: keep it simple
                $passStage = 0;
            } else {
                // Passed
                $passStage = ($mode === 'second_chance') ? 2 : 1;
            }

            DB::table('swipes')->updateOrInsert(
                ['from_user_id' => $me, 'to_user_id' => $to],
                [
                    'like_flag' => $likeFlag,
                    'pass_stage' => $passStage,
                    'created_at' => now(),
                ]
            );
            return response()->json([
            'ok' => true,
            'match_created' => $matchCreated,
            'match_id' => $matchId,
        ]);
    }
}