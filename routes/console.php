<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Services\CompatibilityScorer;

Artisan::command('compat:compute {--user_id=}', function () {
    $latest = DB::table('quiz_responses as qr')
        ->join(DB::raw('(SELECT user_id, MAX(created_at) AS max_created_at
                        FROM quiz_responses
                        GROUP BY user_id) latest'), function ($join) {
            $join->on('qr.user_id', '=', 'latest.user_id')
                 ->on('qr.created_at', '=', 'latest.max_created_at');
        })
        ->select('qr.*')
        ->get()
        ->keyBy('user_id');

    if ($latest->count() < 2) {
        $this->warn('Not enough quiz responses to compute compatibility.');
        return;
    }

    $filterUserId = $this->option('user_id');
    $ids = $latest->keys()->values();
    $now = now();
    $rowsUpserted = 0;

    for ($i = 0; $i < $ids->count(); $i++) {
        $aId = (int)$ids[$i];
        if ($filterUserId && (int)$filterUserId !== $aId) continue;

        $a = (array)$latest[$aId];

        for ($j = $i + 1; $j < $ids->count(); $j++) {
            $bId = (int)$ids[$j];
            $b = (array)$latest[$bId];

            $score = CompatibilityScorer::score($a, $b);

            $u1 = min($aId, $bId);
            $u2 = max($aId, $bId);

            DB::table('compatibility')->updateOrInsert(
                ['user_id_a' => $u1, 'user_id_b' => $u2],
                ['score_100' => $score, 'generated_at' => $now]
            );

            $rowsUpserted++;
        }
    }

    $this->info("Done. Upserted {$rowsUpserted} compatibility rows.");
})->purpose('Compute compatibility scores (0–100) and store them');