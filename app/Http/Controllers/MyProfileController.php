<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MyProfileController extends Controller
{
    public function show()
    {
        $me = Auth::user()->user_id;

        $public = DB::table('public_profile')->where('user_id', $me)->first();
        $private = DB::table('private_profile')->where('user_id', $me)->first();

        $latestQuiz = DB::table('quiz_responses')
            ->where('user_id', $me)
            ->orderByDesc('created_at')
            ->first();

        $photos = DB::table('gallery')
            ->where('user_id', $me)
            ->orderBy('photo_id')
            ->get();

        return view('profile.index', [
            'public' => $public,
            'private' => $private,
            'latestQuiz' => $latestQuiz,
            'photos' => $photos,
        ]);
    }

    public function updatePublic(Request $request)
    {
        $me = Auth::user()->user_id;

        $data = $request->validate([
            'display_name' => ['required','string','min:2','max:60'],
            'age' => ['nullable','integer','min:16','max:99'],
            'city' => ['nullable','string','max:60'],
            'budget_min' => ['nullable','integer','min:0','max:10000'],
            'budget_max' => ['nullable','integer','min:0','max:10000'],
        ]);

        if (!empty($data['budget_min']) && !empty($data['budget_max']) && $data['budget_max'] < $data['budget_min']) {
            return back()->withErrors(['budget_max' => 'budget_max must be >= budget_min']);
        }

        DB::table('public_profile')->updateOrInsert(
            ['user_id' => $me],
            [
                'display_name' => $data['display_name'],
                'age' => $data['age'] ?? null,
                'city' => $data['city'] ?? null,
                'budget_min' => $data['budget_min'] ?? null,
                'budget_max' => $data['budget_max'] ?? null,
                'updated_at' => now(),
            ]
        );

        return back()->with('saved', 'public');
    }

    public function updateAbout(Request $request)
    {
        $me = Auth::user()->user_id;

        $data = $request->validate([
            'bio' => ['nullable','string','max:300'],
        ]);

        DB::table('public_profile')->updateOrInsert(
            ['user_id' => $me],
            [
                'bio' => $data['bio'] ?? null,
                'updated_at' => now(),
            ]
        );

        return back()->with('saved', 'about');
    }

    public function updatePrivate(Request $request)
    {
        $me = Auth::user()->user_id;

        $data = $request->validate([
            'occupation' => ['nullable','string','max:80'],
            'zodiac' => ['nullable','string','max:80'],
            'working_hours' => ['nullable','in:MORNING,DAY,NIGHT,MIXED'],
            'sleep_schedule' => ['nullable','in:EARLY_BIRD,NIGHT_OWL'],
            'move_in_date' => ['nullable','date'],
            'contract_length_months' => ['nullable','integer','min:1','max:60'],
            'room_preference' => ['nullable','in:ENSUITE,SHARED_BATH,NO_PREF'],
            'guest_policy' => ['nullable','in:OK,LIMITED'],
            'food_allergies' => ['nullable','string','max:120'],
            'pet_allergies' => ['nullable','string','max:120'],
            'noise_tolerance' => ['nullable','integer','min:0','max:10'],

            // personal info form fields (you said collected elsewhere, but owner can still edit here if you want later)
            'ethnicity' => ['nullable','string','max:50'],
            'phone_number' => ['nullable','string','max:30'],
            'contact_email' => ['nullable','email','max:255'],
            'instagram' => ['nullable','string','max:255'],
            'twitter' => ['nullable','string','max:255'],
            'snapchat' => ['nullable','string','max:255'],
        ]);

        DB::table('private_profile')->updateOrInsert(
            ['user_id' => $me],
            array_merge($data, ['user_id' => $me])
        );

        return back()->with('saved', 'private');
    }

    public function uploadPhoto(Request $request)
    {
        $me = Auth::user()->user_id;

        $count = DB::table('gallery')->where('user_id', $me)->count();
        if ($count >= 6) {
            return back()->withErrors(['photo' => 'You can upload up to 6 photos.']);
        }

        $data = $request->validate([
            'photo' => ['required','image','mimes:jpg,jpeg,png,webp','max:5120'], // 5MB
        ]);

        $path = $data['photo']->store('gallery', 'public'); // storage/app/public/gallery/...

        DB::table('gallery')->insert([
            'user_id' => $me,
            'photo_url' => '/storage/'.$path,
            'uploaded_at' => now(),
        ]);

        return back()->with('saved', 'photo');
    }

    public function deletePhoto(int $photo_id)
    {
        $me = Auth::user()->user_id;

        $row = DB::table('gallery')->where('photo_id', $photo_id)->where('user_id', $me)->first();
        if (!$row) abort(404);

        // delete file if it’s under /storage/
        if (is_string($row->photo_url) && str_starts_with($row->photo_url, '/storage/')) {
            $relative = substr($row->photo_url, strlen('/storage/'));
            Storage::disk('public')->delete($relative);
        }

        DB::table('gallery')->where('photo_id', $photo_id)->delete();

        return back()->with('saved', 'photo_deleted');
    }
}