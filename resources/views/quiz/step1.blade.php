@extends('layouts.blank')
@section('title','Quiz — Step 1')

@section('content')
<style>
  .qwrap{
    min-height: calc(100vh - 40px);
    display:flex;
    align-items:center;
    justify-content:center;
    padding: 24px 14px;
    background: var(--bg);
  }
  .qcard{
    width: min(980px, 100%);
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: 18px;
    box-shadow: var(--shadow);
    padding: 26px;
  }
  .qtitle{
    font-weight: 900;
    font-size: 28px;
    text-align:center;
    margin-bottom: 22px;
  }
  .choices{
    display:grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
  }
  .choice{
    border: 2px solid rgba(17,17,17,.18);
    border-radius: 16px;
    background: #fff;
    padding: 18px;
    height: 150px;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    transition: transform .12s ease, background .12s ease, border-color .12s ease;
    user-select:none;
  }
  .choice:hover{ transform: translateY(-2px); background: rgba(242,204,15,.10); }
  .choice.selected{
    border-color: rgba(242,204,15,.9);
    background: rgba(242,204,15,.18);
  }
  .icon{
    width: 44px; height:44px;
    border-radius: 14px;
    background: rgba(17,17,17,.06);
    display:flex; align-items:center; justify-content:center;
    font-size: 22px;
    margin-bottom: 10px;
  }
  .label{
    font-weight: 900;
    text-align:center;
  }
  .actions{
    display:flex;
    justify-content:center;
    margin-top: 22px;
  }
  @media(max-width: 820px){
    .choices{ grid-template-columns: 1fr; }
    .choice{ height: 120px; }
  }
</style>

<div class="qwrap">
  <div class="qcard">
    <div class="qtitle">What are you looking for?</div>

    @if ($errors->any())
      <div class="alert alert-danger">
        Please select one option to continue.
      </div>
    @endif

    <form method="POST" action="{{ route('quiz.step1.save') }}" id="step1Form">
      @csrf
      <input type="hidden" name="looking_for_type" id="lookingFor">

      <div class="choices">
        <div class="choice" data-val="ROOMMATE">
          <div class="icon">👥</div>
          <div class="label">Roommates</div>
        </div>

        <div class="choice" data-val="ROOM_AND_ROOMMATE">
          <div class="icon">🏠</div>
          <div class="label">Room + Roommate</div>
        </div>

        <div class="choice" data-val="JOIN_GROUP">
          <div class="icon">🤝</div>
          <div class="label">Join a Group</div>
        </div>
      </div>

      <div class="actions">
        <button class="btn btn-wanted btn-lg px-5" type="submit">Next</button>
      </div>
    </form>
  </div>
</div>

<script>
  const choices = document.querySelectorAll('.choice');
  const hidden = document.getElementById('lookingFor');
  const preselected = @json($selected ?? null);

  function setSelected(val){
    hidden.value = val;
    choices.forEach(c => c.classList.toggle('selected', c.dataset.val === val));
  }

  if (preselected) setSelected(preselected);

  choices.forEach(c => {
    c.addEventListener('click', () => setSelected(c.dataset.val));
  });

  document.getElementById('step1Form').addEventListener('submit', (e) => {
    if (!hidden.value) {
      e.preventDefault();
      alert('Please select one option.');
    }
  });
</script>
@endsection