<?php

namespace App\Services;

class CompatibilityScorer
{
    public static function score(array $a, array $b): int
    {
        $score = 0;

        // 1) looking_for_type (10)
        if (($a['looking_for_type'] ?? null) && ($a['looking_for_type'] === $b['looking_for_type'])) {
            $score += 10;
        }

        // 2) city (13)
        if (($a['city'] ?? null) && (mb_strtolower($a['city']) === mb_strtolower($b['city']))) {
            $score += 13;
        }

        // 3) budget (13)
        $score += self::budgetScore(
            (int)$a['budget_min'], (int)$a['budget_max'],
            (int)$b['budget_min'], (int)$b['budget_max']
        );

        // 4) hobbies: 10 booleans × 4 (max 40)
        $hobbies = [
            'hobby_gym','hobby_gaming','hobby_cooking','hobby_creative','hobby_hiking',
            'hobby_music','hobby_movies','hobby_foodie','hobby_partying','hobby_reading'
        ];
        foreach ($hobbies as $k) {
            if (!empty($a[$k]) && !empty($b[$k])) $score += 4;
        }

        // 5) preferences: 8 booleans × 3 (max 24)
        $prefs = [
            'pref_smoking','pref_drinking','pref_introverted','pref_clean_high',
            'pref_noise_sensitive','pref_guests_ok','pref_student','pref_night_owl'
        ];
        foreach ($prefs as $k) {
            if (!empty($a[$k]) && !empty($b[$k])) $score += 3;
        }

        // Clamp 0..100
        return max(0, min(100, $score));
    }

    private static function budgetScore(int $aMin, int $aMax, int $bMin, int $bMax): int
    {
        // Full points if ranges overlap
        $overlap = !($aMax < $bMin || $bMax < $aMin);
        if ($overlap) return 13;

        // Otherwise score by how "close" the ranges are (gap between ranges)
        $gap = ($aMax < $bMin) ? ($bMin - $aMax) : ($aMin - $bMax);

        // Simple tiered scoring (tweakable)
        if ($gap <= 100) return 10;
        if ($gap <= 200) return 7;
        if ($gap <= 300) return 4;
        return 0;
    }
}