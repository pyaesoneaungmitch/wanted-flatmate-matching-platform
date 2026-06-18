<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileDetailController extends Controller
{
    public function show(int $user_id)
    {
        $me = Auth::user()->user_id;
        $other = (int)$user_id;

        
        $public = DB::table('public_profile')->where('user_id', $other)->first();
        abort_if(!$public, 404);

        // Latest quiz for other user (hobbies/prefs/looking_for)
        $quiz = DB::table('quiz_responses')
            ->where('user_id', $other)
            ->orderByDesc('created_at')
            ->first();

        // Compatibility score (stored as ordered pair)
        $u1 = min($me, $other);
        $u2 = max($me, $other);
        $isSelf = ($other === $me);

        $score = $isSelf ? 100 : (DB::table('compatibility')
            ->where('user_id_a', $u1)->where('user_id_b', $u2)
            ->value('score_100') ?? 0);
            
        // Photos
        $photos = DB::table('gallery')
            ->where('user_id', $other)
            ->orderBy('photo_id')
            ->pluck('photo_url')
            ->values()
            ->all();

        // Private profile visibility: only if match exists AND private_share = 1
        $privateShare = false;
        $match = DB::table('matches')
        ->where('user1_id', $u1)->where('user2_id', $u2)
        ->first();

    // Default: locked
    $isSelf = ($other === $me);

if ($isSelf) {
    $score = 100;

    $photos = DB::table('gallery')
        ->where('user_id', $me)
        ->orderBy('photo_id')
        ->pluck('photo_url')
        ->values()
        ->all();

        $private = DB::table('private_profile')->where('user_id', $me)->first();

        return view('profiles.show', [
            'public' => $public,
            'quiz' => $quiz,
            'score' => $score,
            'photos' => $photos,
            'privateShare' => true,
            'private' => $private,          // IMPORTANT
            'otherUserId' => $me,
            'from' => request()->query('from'),
        ]);
    }
    else {

    if ($match) {
        // If I'm viewing OTHER user's private profile:
        // I can see it only if OTHER has shared.
        if ($other === (int)$match->user1_id) {
            $privateShare = ((int)$match->user1_shared === 1);
        } else {
            $privateShare = ((int)$match->user2_shared === 1);
        }
    }

        $private = null;
        if ($privateShare) {
            $private = DB::table('private_profile')->where('user_id', $other)->first();
        }
        
        $from = request()->query('from'); // e.g. 'matches' or null

        return view('profiles.show', [
            'public' => $public,
            'quiz' => $quiz,                  // can be null if they haven't done quiz
            'score' => (int)$score,
            'photos' => $photos,
            'privateShare' => $privateShare,
            'private' => $private,
            'otherUserId' => $other,
            'from' => $from,
        ]);
    }
    }
}