<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\CompatibilityStore;

class QuizController extends Controller
{
    public function show()
    {
        return view('quiz');
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            
    'looking_for_type' => ['required'],
    'budget_min' => ['required','integer','min:0','max:10000'],
    'budget_max' => ['required','integer','min:0','max:10000','gte:budget_min'],
    'city' => ['required','string','max:60'],

    // public profile fields (from quiz)
    'display_name' => ['required','string','min:2','max:60'],
    'age' => ['nullable','integer','min:18','max:99'],
    'bio' => ['nullable','string','max:300'],

    // private profile fields (from quiz)
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
    'noise_tolerance' => ['nullable','integer','min:0','max:10'], // pick your scale; adjust if needed
]);
        

        $userId = Auth::user()->user_id;

        // booleans: if checkbox missing => 0
        $boolFields = [
          'hobby_gym','hobby_gaming','hobby_cooking','hobby_creative','hobby_hiking',
          'hobby_music','hobby_movies','hobby_foodie','hobby_partying','hobby_reading',
          'pref_smoking','pref_drinking','pref_introverted','pref_clean_high',
          'pref_noise_sensitive','pref_guests_ok','pref_student','pref_night_owl'
        ];

        foreach ($boolFields as $f) {
            $data[$f] = $request->has($f) ? 1 : 0;
        }

        DB::table('quiz_responses')->insert([
          'user_id' => $userId,
          'looking_for_type' => $data['looking_for_type'],
          'budget_min' => $data['budget_min'],
          'budget_max' => $data['budget_max'],
          'city' => $data['city'],

          // hobbies
          'hobby_gym' => $data['hobby_gym'],
          'hobby_gaming' => $data['hobby_gaming'],
          'hobby_cooking' => $data['hobby_cooking'],
          'hobby_creative' => $data['hobby_creative'],
          'hobby_hiking' => $data['hobby_hiking'],
          'hobby_music' => $data['hobby_music'],
          'hobby_movies' => $data['hobby_movies'],
          'hobby_foodie' => $data['hobby_foodie'],
          'hobby_partying' => $data['hobby_partying'],
          'hobby_reading' => $data['hobby_reading'],

          // prefs
          'pref_smoking' => $data['pref_smoking'],
          'pref_drinking' => $data['pref_drinking'],
          'pref_introverted' => $data['pref_introverted'],
          'pref_clean_high' => $data['pref_clean_high'],
          'pref_noise_sensitive' => $data['pref_noise_sensitive'],
          'pref_guests_ok' => $data['pref_guests_ok'],
          'pref_student' => $data['pref_student'],
          'pref_night_owl' => $data['pref_night_owl'],

          'created_at' => now(),
        ]);
       

// PUBLIC PROFILE (fill all fields from quiz)
DB::table('public_profile')->updateOrInsert(
    ['user_id' => $userId],
    [
        'display_name' => $data['display_name'],
        'age' => $data['age'] ?? null,
        'bio' => $data['bio'] ?? null,
        'city' => $data['city'],
        'budget_min' => $data['budget_min'],
        'budget_max' => $data['budget_max'],
        'updated_at' => now(),
    ]
);

// PRIVATE PROFILE (fill all except the 6 "personal info" fields)
DB::table('private_profile')->updateOrInsert(
    ['user_id' => $userId],
    [
        'occupation' => $data['occupation'] ?? null,
        'zodiac' => $data['zodiac'] ?? null,
        'working_hours' => $data['working_hours'] ?? null,
        'sleep_schedule' => $data['sleep_schedule'] ?? null,
        'move_in_date' => $data['move_in_date'] ?? null,
        'contract_length_months' => $data['contract_length_months'] ?? null,
        'room_preference' => $data['room_preference'] ?? null,
        'guest_policy' => $data['guest_policy'] ?? null,
        'food_allergies' => $data['food_allergies'] ?? null,
        'pet_allergies' => $data['pet_allergies'] ?? null,
        'noise_tolerance' => $data['noise_tolerance'] ?? null,

        // DO NOT TOUCH these here (personal info form later):
        // 'ethnicity', 'phone_number', 'contact_email', 'instagram', 'twitter', 'snapchat'
    ]
);
CompatibilityStore::recomputeForUser($userId);
        
        return redirect()->route('discover');
    }
}