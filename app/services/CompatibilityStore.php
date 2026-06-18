<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CompatibilityStore
{
    public static function recomputeForUser(int $userId): void
    {
        // latest quiz for this user
        $meQuiz = DB::table('quiz_responses')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->first();

        if (!$meQuiz) return;

        // latest quiz for all OTHER users
        $others = DB::table('quiz_responses as qr')
            ->join(DB::raw('(SELECT user_id, MAX(created_at) AS max_created_at
                            FROM quiz_responses
                            GROUP BY user_id) latest'), function ($join) {
                $join->on('qr.user_id', '=', 'latest.user_id')
                     ->on('qr.created_at', '=', 'latest.max_created_at');
            })
            ->where('qr.user_id', '!=', $userId)
            ->select('qr.*')
            ->get();

        $now = now();
        $meArr = (array)$meQuiz;

        foreach ($others as $o) {
            $otherArr = (array)$o;
            $otherId = (int)$o->user_id;

            $score = \App\Services\CompatibilityScorer::score($meArr, $otherArr);

            $u1 = min($userId, $otherId);
            $u2 = max($userId, $otherId);

            DB::table('compatibility')->updateOrInsert(
                ['user_id_a' => $u1, 'user_id_b' => $u2],
                ['score_100' => $score, 'generated_at' => $now]
            );
        }
    }
}