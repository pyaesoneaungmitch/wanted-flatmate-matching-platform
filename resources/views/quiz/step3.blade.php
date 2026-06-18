@extends('layouts.blank')
@section('title','Quiz — Step 3')

@section('content')
<style>
  .wrap{
    min-height: 100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding: 24px 14px;
    background: var(--bg);
  }
  .box{
    width: min(980px, 100%);
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: 18px;
    box-shadow: var(--shadow);
    padding: 22px;
  }
  .header{ display:flex; justify-content:center; margin-bottom: 16px; }
  .title-pill{
    background: rgba(242,204,15,.20);
    border: 1px solid rgba(242,204,15,.55);
    border-radius: 14px;
    padding: 10px 16px;
    font-weight: 900;
  }
  .qtitle{
    text-align:center;
    font-weight: 900;
    font-size: 24px;
    margin: 6px 0 18px;
  }

  .cardish{
    background:#fff;
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 16px;
  }

  /* Tip bubble */
  .tip{
    margin-top: 14px;
    background: rgba(242,204,15,.16);
    border: 1px solid rgba(242,204,15,.55);
    border-radius: 16px;
    padding: 12px 14px;
    display:flex;
    gap: 12px;
    align-items:flex-start;
  }
  .tip-icon{
    width: 34px; height:34px;
    border-radius: 12px;
    background: rgba(242,204,15,.28);
    border: 1px solid rgba(242,204,15,.55);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size: 18px;
    font-weight: 900;
    flex: 0 0 auto;
  }
  .tip-title{ font-weight: 900; margin-bottom: 2px; }
  .tip-text{ color: var(--muted); font-weight: 700; font-size: 13px; line-height: 1.35; }

  .room-choice{
    border: 2px solid rgba(17,17,17,.14);
    border-radius: 16px;
    background:#fff;
    padding: 14px;
    height: 92px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap: 10px;
    cursor:pointer;
    font-weight: 900;
    transition: transform .12s ease, background .12s ease, border-color .12s ease;
    user-select:none;
  }
  .room-choice:hover{ transform: translateY(-2px); background: rgba(242,204,15,.10); }
  .room-choice.selected{
    background: rgba(242,204,15,.18);
    border-color: rgba(242,204,15,.85);
  }
  .room-icon{ font-size: 22px; }

  .actions{ display:flex; justify-content:center; margin-top: 18px; }

  .allergies-title{
    font-weight: 900;
    font-size: 26px;
    text-align:center;
    margin-bottom: 10px;
  }

  @media(max-width: 820px){
    .room-choice{ height: 84px; }
  }
</style>

<div class="wrap">
  <div class="box">
    <div class="header">
      <div class="title-pill">Room Related Questions</div>
    </div>

    <div class="qtitle">Tell us about your ideal room setup</div>

    @if ($errors->any())
      <div class="alert alert-danger">
        <div class="fw-bold mb-1">Please fix:</div>
        <ul class="mb-0">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('quiz.step3.save') }}" id="step3Form">
      @csrf

      <div class="row g-3">
        <div class="col-md-7">
          <div class="cardish h-100">
            <label class="form-label fw-bold">Looking for in (City)</label>
            <input class="form-control form-control-lg" name="city" maxlength="60"
                   value="{{ old('city', $v['city'] ?? '') }}" required>

            <div class="mt-3">
              <label class="form-label fw-bold">Move in date</label>
              <input type="date" class="form-control form-control-lg" name="move_in_date"
                     value="{{ old('move_in_date', $v['move_in_date'] ?? '') }}">
            </div>

            <div class="mt-3">
              <label class="form-label fw-bold">Budget (PCM)</label>
              <div class="d-flex gap-2">
                <input type="number" class="form-control form-control-lg" name="budget_min" min="0" max="10000"
                       value="{{ old('budget_min', $v['budget_min'] ?? 400) }}" required>
                <input type="number" class="form-control form-control-lg" name="budget_max" min="0" max="10000"
                       value="{{ old('budget_max', $v['budget_max'] ?? 800) }}" required>
              </div>
              <div class="small text-muted mt-1">Min — Max</div>

              <div class="tip">
                <div class="tip-icon">💡</div>
                <div>
                  <div class="tip-title">Quick tip</div>
                  <div class="tip-text">
                    Be honest here — it helps avoid awkward “this is out of my budget” chats later.
                    If you’re flexible, set a slightly wider range.
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="col-md-5">
          <div class="cardish h-100">
            <label class="form-label fw-bold">Contract length (months)</label>
            <input type="number" class="form-control form-control-lg" name="contract_length_months" min="1" max="60"
                   value="{{ old('contract_length_months', $v['contract_length_months'] ?? '') }}"
                   placeholder="e.g., 12">

            <div class="mt-3">
              <div class="fw-bold mb-2">Room Preference</div>

              <input type="hidden" name="room_preference" id="roomPref"
                     value="{{ old('room_preference', $v['room_preference'] ?? '') }}" required>

              <div class="d-grid gap-2">
                <div class="room-choice" data-val="ENSUITE">
                  <span class="room-icon">🚿</span> Ensuite
                </div>
                <div class="room-choice" data-val="SHARED_BATH">
                  <span class="room-icon">🛁</span> Shared Bathroom
                </div>
                <div class="room-choice" data-val="NO_PREF">
                  <span class="room-icon">🤷</span> No Preference
                </div>
              </div>
              <div class="small text-muted mt-2">Tap one option to select.</div>
            </div>
          </div>
        </div>

        <div class="col-12">
          <div class="cardish">
            <div class="allergies-title">Any Allergies?</div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">🍽 Food allergies</label>
                <input class="form-control form-control-lg" name="food_allergies" maxlength="120"
                       value="{{ old('food_allergies', $v['food_allergies'] ?? '') }}"
                       placeholder="Optional">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">🐾 Pet allergies</label>
                <input class="form-control form-control-lg" name="pet_allergies" maxlength="120"
                       value="{{ old('pet_allergies', $v['pet_allergies'] ?? '') }}"
                       placeholder="Optional">
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="actions">
        <button type="submit" class="btn btn-wanted btn-lg px-5">Next</button>
      </div>
    </form>
  </div>
</div>

<script>
  const choices = document.querySelectorAll('.room-choice');
  const hidden = document.getElementById('roomPref');
  const saved = hidden.value;

  function setChoice(val){
    hidden.value = val;
    choices.forEach(c => c.classList.toggle('selected', c.dataset.val === val));
  }

  if (saved) setChoice(saved);

  choices.forEach(c => c.addEventListener('click', () => setChoice(c.dataset.val)));

  document.getElementById('step3Form').addEventListener('submit', (e) => {
    if (!hidden.value) {
      e.preventDefault();
      alert('Please select a room preference.');
    }
  });
</script>
@endsection