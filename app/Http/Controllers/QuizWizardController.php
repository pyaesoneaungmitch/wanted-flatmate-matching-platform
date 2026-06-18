<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\CompatibilityStore;

class QuizWizardController extends Controller
{
    public function step1()
    {
        $quiz = session()->get('quiz_wizard', []);
        return view('quiz.step1', [
            'selected' => $quiz['looking_for_type'] ?? null,
        ]);
    }

        public function saveStep1(Request $request)
    {
        $data = $request->validate([
            'looking_for_type' => ['required','in:ROOMMATE,ROOM_AND_ROOMMATE,JOIN_GROUP'],
        ]);

        // Store progress in session
        $quiz = session()->get('quiz_wizard', []);
        $quiz['looking_for_type'] = $data['looking_for_type'];
        session()->put('quiz_wizard', $quiz);

        return redirect()->route('quiz.step2');
    }

    public function step2(Request $request)
{
    $q = (int) $request->query('q', 0);
    $quiz = session()->get('quiz_wizard', []);

    // Define Step 2 question list (order matters)
    $questions = $this->step2Questions();

    if ($q < 0) $q = 0;
    if ($q >= count($questions)) {
        // Step 2 done → go Step 3 (we'll build later)
        return redirect()->route('quiz.step3');
    }

    $current = $questions[$q];

    // previously saved value (if any)
    $saved = $quiz[$current['key']] ?? null;

    return view('quiz.step2', [
        'qIndex' => $q,
        'qTotal' => count($questions),
        'question' => $current,
        'saved' => $saved,
    ]);
}

public function saveStep2(Request $request)
{
    $q = (int) $request->input('q_index', 0);
    $questions = $this->step2Questions();
    abort_if($q < 0 || $q >= count($questions), 400);

    $current = $questions[$q];

    // validate based on type
    if ($current['type'] === 'slider') {
        $data = $request->validate([
            'answer' => ['required','integer','min:1','max:10'],
        ]);
        $value = (int)$data['answer'];
    } elseif ($current['type'] === 'select') {
        $allowed = array_column($current['options'], 'value');
        $data = $request->validate([
            'answer' => ['required', 'in:' . implode(',', $allowed)],
        ]);
        $value = $data['answer'];
    } else {
        // choice2 / yesno → store option value
        $allowed = array_column($current['options'], 'value');
        $data = $request->validate([
            'answer' => ['required', 'in:' . implode(',', $allowed)],
        ]);
        $value = $data['answer'];
    }

    // Save into session
    $quiz = session()->get('quiz_wizard', []);
    $quiz[$current['key']] = $value;
    session()->put('quiz_wizard', $quiz);

    // next
    return redirect()->route('quiz.step2', ['q' => $q + 1]);
}

/**
 * Step 2 questions definition.
 * Keys map to session keys now; later we map them into quiz_responses + private_profile.
 */
private function step2Questions(): array
{
    return [
        [
            'key' => 'sleep_schedule',
            'type' => 'choice2',
            'title' => 'Sleep schedule?',
            'options' => [
                ['value' => 'EARLY_BIRD', 'label' => 'Early Bird', 'icon' => '🌅'],
                ['value' => 'NIGHT_OWL',  'label' => 'Night Owl',  'icon' => '🌙'],
            ],
        ],
        [
            'key' => 'guest_policy',
            'type' => 'choice2',
            'title' => 'Guest policy?',
            'options' => [
                ['value' => 'OK',      'label' => 'OK',      'icon' => '✅'],
                ['value' => 'LIMITED', 'label' => 'Limited', 'icon' => '⏳'],
            ],
        ],
        [
            'key' => 'pref_smoking',
            'type' => 'choice2',
            'title' => 'Smoking?',
            'options' => [
                ['value' => 1, 'label' => 'OK',      'icon' => '🚬'],
                ['value' => 0, 'label' => 'Not OK',  'icon' => '🚫'],
            ],
        ],
        [
            'key' => 'pref_drinking',
            'type' => 'choice2',
            'title' => 'Drinking?',
            'options' => [
                ['value' => 1, 'label' => 'OK',      'icon' => '🍻'],
                ['value' => 0, 'label' => 'Not OK',  'icon' => '🚫'],
            ],
        ],
        [
            'key' => 'pref_clean_high',
            'type' => 'choice2',
            'title' => 'Cleanliness expectation?',
            'options' => [
                ['value' => 1, 'label' => 'Germaphobe',        'icon' => '🧼'],
                ['value' => 0, 'label' => 'No fuss over mess', 'icon' => '🧺'],
            ],
        ],
        [
            'key' => 'pref_student',
            'type' => 'choice2',
            'title' => 'Student?',
            'options' => [
                ['value' => 1, 'label' => 'Yes', 'icon' => '🎓'],
                ['value' => 0, 'label' => 'No',  'icon' => '💼'],
            ],
        ],
        [
            'key' => 'pref_noise_sensitive',
            'type' => 'choice2',
            'title' => 'Noise sensitivity?',
            'options' => [
                ['value' => 1, 'label' => 'Sensitive',  'icon' => '🔇'],
                ['value' => 0, 'label' => "Don’t mind", 'icon' => '🔊'],
            ],
        ],
        [
            'key' => 'noise_tolerance',
            'type' => 'slider',
            'title' => 'How tolerant are you with noise? (1–10)',
            'min' => 1,
            'max' => 10,
        ],
        [
            'key' => 'pref_guests_ok',
            'type' => 'choice2',
            'title' => 'Ok with guests?',
            'options' => [
                ['value' => 1, 'label' => 'Yes', 'icon' => '🙂'],
                ['value' => 0, 'label' => 'No',  'icon' => '🙅'],
            ],
        ],
        [
            'key' => 'pref_night_owl',
            'type' => 'choice2',
            'title' => "I'm more of a…",
            'options' => [
                ['value' => 0, 'label' => 'Morning person', 'icon' => '☀️'],
                ['value' => 1, 'label' => 'Night owl',      'icon' => '🌙'],
            ],
        ],
        [
            'key' => 'pref_introverted',
            'type' => 'choice2',
            'title' => "I'm more of a…",
            'options' => [
                ['value' => 1, 'label' => 'Introvert', 'icon' => '📚'],
                ['value' => 0, 'label' => 'Extrovert', 'icon' => '🎉'],
            ],
        ],

        // Hobbies (Y/N)
        ['key'=>'hobby_gym','type'=>'choice2','title'=>'I love Gym / Fitness','options'=>[
            ['value'=>1,'label'=>'Yes','icon'=>'💪'],['value'=>0,'label'=>'No','icon'=>'🚫']
        ]],
        ['key'=>'hobby_gaming','type'=>'choice2','title'=>'I love Gaming','options'=>[
            ['value'=>1,'label'=>'Yes','icon'=>'🎮'],['value'=>0,'label'=>'No','icon'=>'🚫']
        ]],
        ['key'=>'hobby_cooking','type'=>'choice2','title'=>'I love Cooking','options'=>[
            ['value'=>1,'label'=>'Yes','icon'=>'🍳'],['value'=>0,'label'=>'No','icon'=>'🚫']
        ]],
        ['key'=>'hobby_creative','type'=>'choice2','title'=>'I love Arts / Creative','options'=>[
            ['value'=>1,'label'=>'Yes','icon'=>'🎨'],['value'=>0,'label'=>'No','icon'=>'🚫']
        ]],
        ['key'=>'hobby_hiking','type'=>'choice2','title'=>'I love Outdoors','options'=>[
            ['value'=>1,'label'=>'Yes','icon'=>'🥾'],['value'=>0,'label'=>'No','icon'=>'🚫']
        ]],
        ['key'=>'hobby_music','type'=>'choice2','title'=>'I love Music','options'=>[
            ['value'=>1,'label'=>'Yes','icon'=>'🎵'],['value'=>0,'label'=>'No','icon'=>'🚫']
        ]],
        ['key'=>'hobby_movies','type'=>'choice2','title'=>'I love Movies / Series','options'=>[
            ['value'=>1,'label'=>'Yes','icon'=>'🎬'],['value'=>0,'label'=>'No','icon'=>'🚫']
        ]],
        ['key'=>'hobby_foodie','type'=>'choice2','title'=>'I love Food','options'=>[
            ['value'=>1,'label'=>'Yes','icon'=>'🍜'],['value'=>0,'label'=>'No','icon'=>'🚫']
        ]],
        ['key'=>'hobby_partying','type'=>'choice2','title'=>'I love Partying','options'=>[
            ['value'=>1,'label'=>'Yes','icon'=>'🪩'],['value'=>0,'label'=>'No','icon'=>'🚫']
        ]],
        ['key'=>'hobby_reading','type'=>'choice2','title'=>'I love Reading','options'=>[
            ['value'=>1,'label'=>'Yes','icon'=>'📖'],['value'=>0,'label'=>'No','icon'=>'🚫']
        ]],

        // Zodiac (private_profile.zodiac)
        [
            'key' => 'zodiac',
            'type' => 'select',
            'title' => 'Zodiac sign?',
            'options' => [
                ['value'=>'ARIES','label'=>'♈ Aries'],
                ['value'=>'TAURUS','label'=>'♉ Taurus'],
                ['value'=>'GEMINI','label'=>'♊ Gemini'],
                ['value'=>'CANCER','label'=>'♋ Cancer'],
                ['value'=>'LEO','label'=>'♌ Leo'],
                ['value'=>'VIRGO','label'=>'♍ Virgo'],
                ['value'=>'LIBRA','label'=>'♎ Libra'],
                ['value'=>'SCORPIO','label'=>'♏ Scorpio'],
                ['value'=>'SAGITTARIUS','label'=>'♐ Sagittarius'],
                ['value'=>'CAPRICORN','label'=>'♑ Capricorn'],
                ['value'=>'AQUARIUS','label'=>'♒ Aquarius'],
                ['value'=>'PISCES','label'=>'♓ Pisces'],
                ['value'=>'UNKNOWN','label'=>"I don't know my star sign"],
            ],
        ],
    ];
}

        public function step3()
        {
            $quiz = session()->get('quiz_wizard', []);

            return view('quiz.step3', [
                'v' => $quiz,  // simple alias
            ]);
        }

        public function saveStep3(Request $request)
        {
            $data = $request->validate([
                'city' => ['required','string','max:60'],
                'move_in_date' => ['nullable','date'],
                'budget_min' => ['required','integer','min:0','max:10000'],
                'budget_max' => ['required','integer','min:0','max:10000','gte:budget_min'],
                'contract_length_months' => ['nullable','integer','min:1','max:60'],
                'room_preference' => ['required','in:ENSUITE,SHARED_BATH,NO_PREF'],
                'food_allergies' => ['nullable','string','max:120'],
                'pet_allergies' => ['nullable','string','max:120'],
            ]);

            $quiz = session()->get('quiz_wizard', []);

            // store
            foreach ($data as $k => $val) {
                $quiz[$k] = $val;
            }
            session()->put('quiz_wizard', $quiz);

            return redirect()->route('quiz.step4');
        }

        public function step4()
        {
            $quiz = session()->get('quiz_wizard', []);

            return view('quiz.step4', [
                'v' => $quiz,
            ]);
        }

        public function saveStep4(Request $request)
        {
            $me = Auth::user()->user_id;

            // Step 4 validation (includes file)
            $data = $request->validate([
                // public profile
                'display_name' => ['required','string','min:2','max:60'],
                'age' => ['nullable','integer','min:16','max:99'],
                'bio' => ['nullable','string','max:300'],

                // private profile (from step4)
                'occupation' => ['nullable','string','max:80'],
                'working_hours' => ['nullable','in:MORNING,DAY,NIGHT,MIXED,RATHER_NOT_TELL'],

                // socials + contact (optional)
                'phone_number' => ['nullable','string','max:30'],
                'contact_email' => ['nullable','email','max:255'],
                'instagram' => ['nullable','string','max:255'],
                'twitter' => ['nullable','string','max:255'],
                'snapchat' => ['nullable','string','max:255'],

                // ✅ profile photo upload
                'profile_photo' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:5120'],
            ]);

            // Pull everything saved so far
            $quiz = session()->get('quiz_wizard', []);

            // Guard: make sure required earlier parts exist
            foreach (['looking_for_type','city','budget_min','budget_max','room_preference'] as $req) {
                if (!array_key_exists($req, $quiz) || $quiz[$req] === null || $quiz[$req] === '') {
                    return redirect()->route('quiz.step1')
                        ->withErrors(['quiz' => 'Quiz progress missing. Please restart the quiz.']);
                }
            }

            // Merge step4 into session so it remains consistent
            foreach ($data as $k => $v) $quiz[$k] = $v;

            // ✅ Handle photo upload (store path in session)
            if ($request->hasFile('profile_photo')) {
                // Optional: enforce max 6 photos
                $count = DB::table('gallery')->where('user_id', $me)->count();
                if ($count >= 6) {
                    return back()->withErrors(['profile_photo' => 'You already have 6 photos. Delete one first.']);
                }

                $path = $request->file('profile_photo')->store('gallery', 'public');
                $quiz['profile_photo_url'] = '/storage/' . $path;
            }

            session()->put('quiz_wizard', $quiz);

            // Helpers for booleans defaulting to 0
            $getBool = function(string $k) use ($quiz) {
                return (int)(($quiz[$k] ?? 0) ? 1 : 0);
            };

            DB::transaction(function() use ($me, $quiz, $getBool) {

                // 1) Insert quiz_responses (ONE clean row)
                DB::table('quiz_responses')->insert([
                    'user_id' => $me,
                    'looking_for_type' => $quiz['looking_for_type'],

                    'budget_min' => (int)$quiz['budget_min'],
                    'budget_max' => (int)$quiz['budget_max'],
                    'city' => $quiz['city'],

                    // hobbies
                    'hobby_gym' => $getBool('hobby_gym'),
                    'hobby_gaming' => $getBool('hobby_gaming'),
                    'hobby_cooking' => $getBool('hobby_cooking'),
                    'hobby_creative' => $getBool('hobby_creative'),
                    'hobby_hiking' => $getBool('hobby_hiking'),
                    'hobby_music' => $getBool('hobby_music'),
                    'hobby_movies' => $getBool('hobby_movies'),
                    'hobby_foodie' => $getBool('hobby_foodie'),
                    'hobby_partying' => $getBool('hobby_partying'),
                    'hobby_reading' => $getBool('hobby_reading'),

                    // prefs
                    'pref_smoking' => $getBool('pref_smoking'),
                    'pref_drinking' => $getBool('pref_drinking'),
                    'pref_introverted' => $getBool('pref_introverted'),
                    'pref_clean_high' => $getBool('pref_clean_high'),
                    'pref_noise_sensitive' => $getBool('pref_noise_sensitive'),
                    'pref_guests_ok' => $getBool('pref_guests_ok'),
                    'pref_student' => $getBool('pref_student'),
                    'pref_night_owl' => $getBool('pref_night_owl'),

                    'created_at' => now(),
                ]);

                // 2) Update public_profile
                DB::table('public_profile')->updateOrInsert(
                    ['user_id' => $me],
                    [
                        'display_name' => $quiz['display_name'],
                        'age' => $quiz['age'] ?? null,
                        'bio' => $quiz['bio'] ?? null,
                        'city' => $quiz['city'],
                        'budget_min' => (int)$quiz['budget_min'],
                        'budget_max' => (int)$quiz['budget_max'],
                        'updated_at' => now(),
                    ]
                );

                // 3) Update private_profile
                DB::table('private_profile')->updateOrInsert(
                    ['user_id' => $me],
                    [
                        'occupation' => $quiz['occupation'] ?? null,
                        'zodiac' => $quiz['zodiac'] ?? null,

                        // step2
                        'working_hours' => (($quiz['working_hours'] ?? null) === 'RATHER_NOT_TELL') ? null : ($quiz['working_hours'] ?? null),
                        'sleep_schedule' => $quiz['sleep_schedule'] ?? null,
                        'guest_policy' => $quiz['guest_policy'] ?? null,

                        // step3
                        'move_in_date' => $quiz['move_in_date'] ?? null,
                        'contract_length_months' => $quiz['contract_length_months'] ?? null,
                        'room_preference' => $quiz['room_preference'] ?? null,
                        'food_allergies' => $quiz['food_allergies'] ?? null,
                        'pet_allergies' => $quiz['pet_allergies'] ?? null,
                        'noise_tolerance' => $quiz['noise_tolerance'] ?? null,

                        // step4 personal info
                        'phone_number' => $quiz['phone_number'] ?? null,
                        'contact_email' => $quiz['contact_email'] ?? null,
                        'instagram' => $quiz['instagram'] ?? null,
                        'twitter' => $quiz['twitter'] ?? null,
                        'snapchat' => $quiz['snapchat'] ?? null,
                    ]
                );

                // ✅ Insert uploaded profile photo into gallery
                if (!empty($quiz['profile_photo_url'])) {
                    DB::table('gallery')->insert([
                        'user_id' => $me,
                        'photo_url' => $quiz['profile_photo_url'],
                        'uploaded_at' => now(),
                    ]);
                }
            });
            CompatibilityStore::recomputeForUser($me);

            // Clear wizard session once committed
            session()->forget('quiz_wizard');

            return redirect()->route('discover');
        }

        public function reset()
    {
        session()->forget('quiz_wizard');
        return redirect()->route('quiz.step1');
    }
}