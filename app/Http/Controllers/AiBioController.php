<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AiBioController extends Controller
{
    public function generate(Request $request)
    {
        $me = Auth::user()->user_id;

        // Rate limit: 5/day
        $today = now()->toDateString();
        $cacheKey = "ai_bio_count_{$me}_{$today}";
        $count = cache()->get($cacheKey, 0);
        if ($count >= 5) {
            return response()->json(['ok' => false, 'error' => 'Daily limit reached. Try again tomorrow.'], 429);
        }

        // Validate input
        $validated = $request->validate([
            'tone' => ['nullable', 'in:fun,calm,premium'],
            'debug' => ['nullable', 'boolean'],
        ]);

        $tone = $validated['tone'] ?? 'fun';
        $debug = (bool)($validated['debug'] ?? false);

        // Load data
        $quiz = DB::table('quiz_responses')
            ->where('user_id', $me)
            ->orderByDesc('created_at')
            ->first();

        if (!$quiz) {
            return response()->json(['ok' => false, 'error' => 'Quiz not found.'], 400);
        }

        $public  = DB::table('public_profile')->where('user_id', $me)->first();
        $private = DB::table('private_profile')->where('user_id', $me)->first();

        $facts = [
            'display_name' => $public->display_name ?? null,
            'age' => $public->age ?? null,
            'city' => $public->city ?? null,
            'budget' => (isset($public->budget_min, $public->budget_max))
                ? ($public->budget_min . '-' . $public->budget_max)
                : null,

            'looking_for_type' => $quiz->looking_for_type ?? null,

            'sleep_schedule' => $private->sleep_schedule ?? null,
            'guest_policy' => $private->guest_policy ?? null,
            'working_hours' => $private->working_hours ?? null,
            'room_preference' => $private->room_preference ?? null,
            'noise_tolerance' => $private->noise_tolerance ?? null,

            'hobbies' => $this->listOnes((array)$quiz, [
                'hobby_gym' => 'Gym',
                'hobby_gaming' => 'Gaming',
                'hobby_cooking' => 'Cooking',
                'hobby_creative' => 'Arts',
                'hobby_hiking' => 'Outdoors',
                'hobby_music' => 'Music',
                'hobby_movies' => 'Movies/Series',
                'hobby_foodie' => 'Foodie',
                'hobby_partying' => 'Partying',
                'hobby_reading' => 'Reading',
            ]),
            'preferences' => $this->listOnes((array)$quiz, [
                'pref_clean_high' => 'Clean',
                'pref_noise_sensitive' => 'Noise-sensitive',
                'pref_student' => 'Student',
                'pref_night_owl' => 'Night owl',
                'pref_introverted' => 'Introverted',
                'pref_smoking' => 'Okay with smoking',
                'pref_drinking' => 'Okay with drinking',
                'pref_guests_ok' => 'Okay with guests',
            ]),
        ];

        $prompt = $this->buildPrompt($facts, $tone);

        $apiKey = env('GEMINI_API_KEY');
        $model  = env('GEMINI_MODEL', 'gemini-2.5-flash');

        if (!$apiKey) {
            return response()->json(['ok' => false, 'error' => 'Missing GEMINI_API_KEY in .env'], 500);
        }

        $resp = $this->callGemini($prompt, $model, $apiKey, 0.7, 1000);

        if (!$resp->ok()) {
            return response()->json([
                'ok' => false,
                'error' => 'Gemini request failed',
                'status' => $resp->status(),
                'body' => $resp->body(),
            ], 500);
        }

        // Gemini may split into multiple parts
        $parts = data_get($resp->json(), 'candidates.0.content.parts', []);
        $text  = trim((string)collect($parts)->pluck('text')->implode(''));

        // Clean up any accidental preamble/numbering
        $bio = $this->cleanBio($text);
            if (mb_strlen($bio) > 300) {
                $bio = mb_substr($bio, 0, 297) . '…';
        }

        if ($debug) {
            return response()->json([
                'ok' => true,
                'debug' => [
                    'raw_text' => $text,
                    'bio' => $bio,
                    'prompt' => $prompt,
                    'model' => $model,
                ],
            ]);
        }

        if ($bio === '' || mb_strlen($bio) < 20) {
            return response()->json([
                'ok' => false,
                'error' => 'AI bio was empty/too short.',
                'raw_text' => $text,
            ], 500);
        }

        cache()->put($cacheKey, $count + 1, now()->endOfDay());

        return response()->json([
            'ok' => true,
            'bio' => $bio,
        ]);
    }

    private function callGemini(string $prompt, string $model, string $apiKey, float $temp, int $maxTokens)
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        return Http::timeout(25)->post($url, [
            "contents" => [[
                "role" => "user",
                "parts" => [["text" => $prompt]]
            ]],
            "generationConfig" => [
                "temperature" => $temp,
                "maxOutputTokens" => $maxTokens,
            ],
        ]);
    }

    private function listOnes(array $row, array $map): array
    {
        $out = [];
        foreach ($map as $k => $label) {
            if (!empty($row[$k])) $out[] = $label;
        }
        return $out;
    }

    private function buildPrompt(array $facts, string $tone): string
    {
        $toneText = match($tone) {
            'calm' => 'calm and friendly',
            'premium' => 'clean and confident',
            default => 'fun and friendly',
        };

        $factsJson = json_encode($facts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return
"Write ONE short roommate profile bio.

Tone: {$toneText}

Rules:
- Output ONLY the bio text. No numbering, no extra words.
- 1–2 sentences.
- Max 280 characters (so it fits a 300-char box).
- Don’t start with 'Hi' or \"I'm\".
- No contact info.
- Mention 3-4 details naturally.
- End with punctuation.

Data: {$factsJson}";
    }

    private function cleanBio(string $text): string
    {
        $bio = trim($text);

        // remove common junk
        $bio = preg_replace('/^\s*(here are|bio:|profile bio:)\s*/i', '', $bio);
        $bio = preg_replace('/^\s*\d[\)\.]\s*/', '', $bio); // remove "1)" etc
        $bio = preg_replace('/\s+/', ' ', $bio);
        $bio = trim($bio, "\"' \t\n\r");

        // if model accidentally returned multiple lines, keep the first “sentence block”
        // (optional safeguard)
        return $bio;
    }
}