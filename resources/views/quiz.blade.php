{{-- resources/views/quiz.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quiz — Wanted</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5" style="max-width: 980px;">
  <div class="mb-4">
    <h2 class="mb-1">Roommate Matching Quiz</h2>
    <p class="text-muted mb-0">Complete this to unlock Discover.</p>
  </div>

  @if ($errors->any())
    <div class="alert alert-danger">
      <div class="fw-semibold mb-2">Please fix the following:</div>
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('quiz.submit') }}">
    @csrf

    {{-- Public profile (from quiz) --}}
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white">
        <div class="fw-semibold">Public Profile</div>
        <div class="text-muted small">This is visible on your card during discovery.</div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Display name</label>
            <input type="text" class="form-control" name="display_name" maxlength="60" value="{{ old('display_name') }}" required>
            <div class="form-text">2–60 characters.</div>
          </div>

          <div class="col-md-2">
            <label class="form-label">Age</label>
            <input type="number" class="form-control" name="age" min="18" max="99" value="{{ old('age') }}" required>
            </div>

          <div class="col-md-12">
            <label class="form-label">Bio</label>
            <textarea class="form-control" name="bio" maxlength="300" rows="3" placeholder="A short intro...">{{ old('bio') }}</textarea>
            <div class="form-text">Up to 300 characters.</div>
          </div>
        </div>
      </div>
    </div>

    {{-- Basics --}}
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white">
        <div class="fw-semibold">Matching Basics</div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Looking for</label>
            <select class="form-select" name="looking_for_type" required>
              @php $lft = old('looking_for_type'); @endphp
              <option value="" {{ $lft==='' ? 'selected' : '' }} disabled>Select one…</option>
              <option value="ROOMMATE" {{ $lft==='ROOMMATE' ? 'selected' : '' }}>Roommate</option>
              <option value="ROOM_AND_ROOMMATE" {{ $lft==='ROOM_AND_ROOMMATE' ? 'selected' : '' }}>Room + roommate</option>
              <option value="JOIN_GROUP" {{ $lft==='JOIN_GROUP' ? 'selected' : '' }}>Join a group</option>
            </select>
            <div class="form-text">This affects matching intent (10 points).</div>
          </div>

          <div class="col-md-6">
            <label class="form-label">City</label>
            <input type="text" class="form-control" name="city" maxlength="60" value="{{ old('city') }}" required>
            <div class="form-text">Used for filtering/matching (13 points).</div>
          </div>

          <div class="col-md-3">
            <label class="form-label">Budget min (PCM)</label>
            <input type="number" class="form-control" name="budget_min" min="0" max="10000" value="{{ old('budget_min', 400) }}" required>
          </div>

          <div class="col-md-3">
            <label class="form-label">Budget max (PCM)</label>
            <input type="number" class="form-control" name="budget_max" min="0" max="10000" value="{{ old('budget_max', 800) }}" required>
            <div class="form-text">Budget compatibility (13 points).</div>
          </div>

          <div class="col-md-6 d-flex align-items-end">
            <div class="alert alert-info w-100 mb-0">
              <div class="fw-semibold">Tip</div>
              <div class="small">Pick honestly — it improves the quality of your recommendations.</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Private profile (from quiz; personal info later) --}}
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white">
        <div class="fw-semibold">Lifestyle (Private)</div>
        <div class="text-muted small">Hidden by default. Sharing is controlled later by consent.</div>
      </div>
      <div class="card-body">
        <div class="row g-3">

          <div class="col-md-6">
            <label class="form-label">Occupation</label>
            <input type="text" class="form-control" name="occupation" maxlength="80" value="{{ old('occupation') }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Zodiac</label>
            <input type="text" class="form-control" name="zodiac" maxlength="80" value="{{ old('zodiac') }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">Working hours</label>
            @php $wh = old('working_hours'); @endphp
            <select class="form-select" name="working_hours">
              <option value="" {{ $wh==='' ? 'selected' : '' }}>Prefer not to say</option>
              <option value="MORNING" {{ $wh==='MORNING' ? 'selected' : '' }}>Morning</option>
              <option value="DAY" {{ $wh==='DAY' ? 'selected' : '' }}>Day</option>
              <option value="NIGHT" {{ $wh==='NIGHT' ? 'selected' : '' }}>Night</option>
              <option value="MIXED" {{ $wh==='MIXED' ? 'selected' : '' }}>Mixed</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Sleep schedule</label>
            @php $ss = old('sleep_schedule'); @endphp
            <select class="form-select" name="sleep_schedule">
              <option value="" {{ $ss==='' ? 'selected' : '' }}>Prefer not to say</option>
              <option value="EARLY_BIRD" {{ $ss==='EARLY_BIRD' ? 'selected' : '' }}>Early bird</option>
              <option value="NIGHT_OWL" {{ $ss==='NIGHT_OWL' ? 'selected' : '' }}>Night owl</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Move-in date</label>
            <input type="date" class="form-control" name="move_in_date" value="{{ old('move_in_date') }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">Contract length (months)</label>
            <input type="number" class="form-control" name="contract_length_months" min="1" max="60" value="{{ old('contract_length_months') }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">Room preference</label>
            @php $rp = old('room_preference'); @endphp
            <select class="form-select" name="room_preference">
              <option value="" {{ $rp==='' ? 'selected' : '' }}>Prefer not to say</option>
              <option value="ENSUITE" {{ $rp==='ENSUITE' ? 'selected' : '' }}>Ensuite</option>
              <option value="SHARED_BATH" {{ $rp==='SHARED_BATH' ? 'selected' : '' }}>Shared bath</option>
              <option value="NO_PREF" {{ $rp==='NO_PREF' ? 'selected' : '' }}>No preference</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Guest policy</label>
            @php $gp = old('guest_policy'); @endphp
            <select class="form-select" name="guest_policy">
              <option value="" {{ $gp==='' ? 'selected' : '' }}>Prefer not to say</option>
              <option value="OK" {{ $gp==='OK' ? 'selected' : '' }}>OK</option>
              <option value="LIMITED" {{ $gp==='LIMITED' ? 'selected' : '' }}>Limited</option>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label">Food allergies</label>
            <input type="text" class="form-control" name="food_allergies" maxlength="120" value="{{ old('food_allergies') }}">
          </div>

          <div class="col-md-6">
            <label class="form-label">Pet allergies</label>
            <input type="text" class="form-control" name="pet_allergies" maxlength="120" value="{{ old('pet_allergies') }}">
          </div>

          <div class="col-md-4">
            <label class="form-label">Noise tolerance (0–10)</label>
            <input type="number" class="form-control" name="noise_tolerance" min="0" max="10" value="{{ old('noise_tolerance') }}">
            <div class="form-text">0 = very sensitive, 10 = very tolerant.</div>
          </div>

          <div class="col-md-8 d-flex align-items-end">
            <div class="alert alert-secondary w-100 mb-0">
              <div class="small">
                Personal contact info (phone/email/socials) will be collected later in a separate form.
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    {{-- Hobbies --}}
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="fw-semibold">Hobbies</div>
        <div class="text-muted small">4 points each (max 40)</div>
      </div>
      <div class="card-body">
        <div class="row g-2">
          @php
            $hobbies = [
              ['hobby_gym', 'Gym / Fitness'],
              ['hobby_gaming', 'Gaming'],
              ['hobby_cooking', 'Cooking'],
              ['hobby_creative', 'Creative arts'],
              ['hobby_hiking', 'Hiking / Outdoors'],
              ['hobby_music', 'Music'],
              ['hobby_movies', 'Movies / Series'],
              ['hobby_foodie', 'Foodie'],
              ['hobby_partying', 'Partying / Night out'],
              ['hobby_reading', 'Reading'],
            ];
          @endphp

          @foreach ($hobbies as [$key, $label])
            <div class="col-md-6 col-lg-4">
              <div class="form-check border rounded p-2 bg-white">
                <input class="form-check-input" type="checkbox" name="{{ $key }}" id="{{ $key }}"
                  {{ old($key) ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $key }}">{{ $label }}</label>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Preferences --}}
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div class="fw-semibold">Preferences</div>
        <div class="text-muted small">3 points each (max 24)</div>
      </div>
      <div class="card-body">
        <div class="row g-2">
          @php
            $prefs = [
              ['pref_smoking', 'Okay with smoking (or you smoke)'],
              ['pref_drinking', 'Okay with drinking'],
              ['pref_introverted', 'More introverted'],
              ['pref_clean_high', 'High cleanliness expectations'],
              ['pref_noise_sensitive', 'Noise sensitive'],
              ['pref_guests_ok', 'Okay with guests'],
              ['pref_student', 'Student lifestyle'],
              ['pref_night_owl', 'Night owl'],
            ];
          @endphp

          @foreach ($prefs as [$key, $label])
            <div class="col-md-6 col-lg-4">
              <div class="form-check border rounded p-2 bg-white">
                <input class="form-check-input" type="checkbox" name="{{ $key }}" id="{{ $key }}"
                  {{ old($key, $key === 'pref_student' ? 1 : 0) ? 'checked' : '' }}>
                <label class="form-check-label" for="{{ $key }}">{{ $label }}</label>
              </div>
            </div>
          @endforeach
        </div>

        <hr class="my-4">

        <div class="d-flex gap-2">
          <button class="btn btn-primary" type="submit">Submit &amp; Unlock Discover</button>
          <a class="btn btn-outline-secondary" href="{{ url('/') }}">Cancel</a>
        </div>

        <div class="text-muted small mt-3">
          By submitting, you agree this information is used to compute your compatibility score (0–100).
        </div>
      </div>
    </div>

  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>