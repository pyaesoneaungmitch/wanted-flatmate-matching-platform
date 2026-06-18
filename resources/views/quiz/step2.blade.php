@extends('layouts.blank')
@section('title','Quiz — Step 2')

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
  .header{
    display:flex;
    justify-content:center;
    margin-bottom: 16px;
  }
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
    font-size: 26px;
    margin: 10px 0 18px;
  }

  .choices{
    display:grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
    margin-bottom: 18px;
  }
  .choice{
    border: 2px solid rgba(17,17,17,.14);
    border-radius: 18px;
    background: #fff;
    height: 260px;
    display:flex;
    align-items:center;
    justify-content:center;
    flex-direction:column;
    cursor:pointer;
    transition: transform .12s ease, background .12s ease, border-color .12s ease;
    user-select:none;
  }
  .choice:hover{ transform: translateY(-2px); background: rgba(242,204,15,.10); }
  .choice.selected{
    background: rgba(242,204,15,.18);
    border-color: rgba(242,204,15,.85);
  }
  .bigicon{ font-size: 54px; margin-bottom: 10px; }
  .ans{ font-weight: 900; font-size: 18px; }

  .slider-wrap{
    background:#fff;
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 18px;
  }
  .slider-value{
    display:inline-block;
    background: rgba(242,204,15,.18);
    border: 1px solid rgba(242,204,15,.55);
    border-radius: 12px;
    padding: 6px 12px;
    font-weight: 900;
  }

  .actions{ display:flex; justify-content:center; margin-top: 18px; }
  @media(max-width: 820px){
    .choices{ grid-template-columns: 1fr; }
    .choice{ height: 180px; }
  }
</style>

<div class="wrap">
  <div class="box">
    <div class="header">
      <div class="title-pill">Roommate Matching Quiz</div>
    </div>

    <div class="qtitle">{{ $question['title'] }}</div>

    <form method="POST" action="{{ route('quiz.step2.save') }}" id="step2Form">
      @csrf
      <input type="hidden" name="q_index" value="{{ $qIndex }}">
      <input type="hidden" name="answer" id="answerInput" value="{{ $saved ?? '' }}">

      @if($question['type'] === 'choice2')
        <div class="choices">
          @foreach($question['options'] as $opt)
            <div class="choice" data-val="{{ $opt['value'] }}">
              <div class="bigicon">{{ $opt['icon'] ?? '⭐' }}</div>
              <div class="ans">{{ $opt['label'] }}</div>
            </div>
          @endforeach
        </div>
      @elseif($question['type'] === 'slider')
        @php
          $min = $question['min'] ?? 1;
          $max = $question['max'] ?? 10;
          $val = $saved ?? 5;
        @endphp
        <div class="slider-wrap">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="fw-bold text-muted">1 (Low)</div>
            <div class="slider-value">Selected: <span id="sliderVal">{{ $val }}</span></div>
            <div class="fw-bold text-muted">10 (High)</div>
          </div>
          <input type="range" class="form-range" min="{{ $min }}" max="{{ $max }}" value="{{ $val }}" id="slider">
        </div>
      @elseif($question['type'] === 'select')
        <div class="slider-wrap">
          <label class="form-label fw-bold">Choose one</label>
          <select class="form-select form-select-lg" id="selectAnswer">
            <option value="" disabled {{ empty($saved) ? 'selected' : '' }}>Select…</option>
            @foreach($question['options'] as $opt)
              <option value="{{ $opt['value'] }}" {{ ($saved ?? '') == $opt['value'] ? 'selected' : '' }}>
                {{ $opt['label'] }}
              </option>
            @endforeach
          </select>
        </div>
      @endif

      <div class="actions">
        <button type="submit" class="btn btn-wanted btn-lg px-5">Next</button>
      </div>
    </form>
  </div>
</div>

<script>
  const type = @json($question['type']);
  const saved = @json($saved);

  const answerInput = document.getElementById('answerInput');

  if (type === 'choice2') {
    const choices = document.querySelectorAll('.choice');
    function setSelected(val){
      answerInput.value = val;
      choices.forEach(c => c.classList.toggle('selected', c.dataset.val == val));
    }
    if (saved !== null && saved !== '') setSelected(saved);

    choices.forEach(c => c.addEventListener('click', () => setSelected(c.dataset.val)));
  }

  if (type === 'slider') {
    const slider = document.getElementById('slider');
    const sliderVal = document.getElementById('sliderVal');
    answerInput.value = slider.value;
    slider.addEventListener('input', () => {
      sliderVal.textContent = slider.value;
      answerInput.value = slider.value;
    });
  }

  if (type === 'select') {
    const sel = document.getElementById('selectAnswer');
    answerInput.value = sel.value || '';
    sel.addEventListener('change', () => answerInput.value = sel.value);
  }

  document.getElementById('step2Form').addEventListener('submit', (e) => {
    if (!answerInput.value) {
      e.preventDefault();
      alert('Please pick an option to continue.');
    }
  });
</script>
@endsection