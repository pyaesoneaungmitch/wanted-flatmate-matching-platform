@extends('layouts.app')
@section('title', 'Matches')

@section('content')
<style>
  .page-shell {
    display: grid;
    grid-template-columns: 1fr 320px;
    gap: 20px;
    align-items: start;
  }
  .topbar {
    background: #fff;
    border: 1px solid rgba(0,0,0,.1);
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    gap: 12px;
    align-items: center;
    margin-bottom: 14px;
  }
  .card-list {
    max-height: calc(100vh - 180px);
    overflow-y: auto;
    padding-right: 8px;
  }
  .filter-panel {
    position: sticky;
    top: 16px;
    background: #fff;
    border: 1px solid rgba(0,0,0,.1);
    border-radius: 12px;
    padding: 16px;
  }
  .match-card {
    background: #fff;
    border: 2px solid rgba(0,0,0,.15);
    border-radius: 24px;
    padding: 18px;
    margin-bottom: 18px;
  }
  .card-inner {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 18px;
    align-items: start;
  }
  .photo-box {
    width: 100%;
    height: 260px;
    border-radius: 26px;
    background: #f1f3f5;
    border: 1px solid rgba(0,0,0,.12);
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(0,0,0,.45);
    font-weight: 600;
    overflow: hidden;
  }
  .score-box {
    display: inline-block;
    padding: 6px 14px;
    border: 2px solid rgba(0,0,0,.25);
    border-radius: 18px;
    font-weight:bold;
    font-size: 18px;
    background: #eed44f;
  }
  .action-row {
    display: flex;
    gap: 18px;
    justify-content: center;
    margin-top: 14px;
  }
  .circle-action {
    width: 74px; height: 74px; border-radius: 999px;
    border: 2px solid rgba(0,0,0,.25);
    background: #fff;
    display:flex; align-items:center; justify-content:center;
    font-size: 34px;
  }
  .btn-pill {
    border-radius: 10px;
    border: 1px solid #F2CC0F;
    background-color:#F2CC0F;
    color:#000000;
    font-size: 16px;
  }

  @media (max-width: 1200px) {
    .page-shell { grid-template-columns: 1fr; }
    .filter-panel { position: static; }
  }

  .topbar{
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 16px;
  box-shadow: var(--shadow);
}
.filter-panel{
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 16px;
  box-shadow: var(--shadow);
}
.btn-primary{
  border-radius: 10px;
    border: 1px solid #F2CC0F;
    background-color:#F2CC0F;
    color:#000000;
    font-size: 16px;
    font-weight:bold;
}
</style>

<div class="page-shell">
  {{-- CENTER --}}
  <div>
    <div class="topbar">
      <div class="fw-semibold">Sorted By :</div>
      <form method="GET" action="{{ route('matches') }}" class="d-flex align-items-center gap-2">
      <input type="hidden" name="city" value="{{ request('city') }}">
      <input type="hidden" name="age_min" value="{{ request('age_min') }}">
      <input type="hidden" name="age_max" value="{{ request('age_max') }}">

      <select class="form-select" style="max-width: 260px;" name="sort" onchange="this.form.submit()">
        <option value="score" @selected(request('sort','score')==='score')>Compatibility Score</option>
        <option value="budget" @selected(request('sort')==='budget')>Budget</option>
        <option value="age" @selected(request('sort')==='age')>Age</option>
      </select>
    </form>

      <div class="ms-auto d-flex gap-2">
        <button class="btn btn-outline-secondary btn-sm" id="tabMatches">Matches</button>
        <button class="btn btn-outline-secondary btn-sm" id="tabLiked">Liked You</button>
      </div>
    </div>

    <div class="card-list" id="matchesList">
      @forelse($matchCards as $c)
        <div class="match-card" data-score="{{ $c['score_100'] }}" data-age="{{ $c['age'] ?? 0 }}" data-budget="{{ $c['budget_max'] ?? 0 }}">
          <div class="card-inner">
            <div>
              <div class="photo-box">
                @if(!empty($c['photo_url']))
                  <img src="{{ url($c['photo_url']) }}" alt="Photo" style="width:100%;height:100%;object-fit:cover;">
                @else
                  Photo
                @endif
              </div>

              </div>

            <div>
              <div class="mb-2"><span class="score-box">Score: {{ $c['score_100'] }}%</span></div>
              <div class="h2 fw-bold mb-3">{{ $c['display_name'] }}, {{ $c['age'] ?? '—' }}</div>

              <div class="d-grid gap-2" style="max-width: 360px;">
                <a class="btn btn-outline-secondary btn-pill"
                   href="{{ route('profiles.show', ['user_id' => $c['other_user_id']]) }}?from=matches">
                  Show More ……
                </a>

                {{-- Messaging later: for now placeholder (you can wire to inbox threads next) --}}
                <button type="button" class="btn btn-secondary btn-pill" disabled>
                  💬 Message
                </button>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="alert alert-secondary">No matches yet.</div>
      @endforelse
    </div>

    <div class="card-list d-none" id="likedList">
      @forelse($likedCards as $c)
        <div class="match-card" data-score="{{ $c['score_100'] }}" data-age="{{ $c['age'] ?? 0 }}" data-budget="{{ $c['budget_max'] ?? 0 }}">
          <div class="card-inner">
            <div>
              <div class="photo-box">
                @if(!empty($c['photo_url']))
                  <img src="{{ url($c['photo_url']) }}" alt="Photo" style="width:100%;height:100%;object-fit:cover;">
                @else
                  Photo
                @endif
              </div>

              <div class="action-row">
                <button type="button" class="circle-action" onclick="swipe({{ $c['other_user_id'] }}, false)">✕</button>
                <button type="button" class="circle-action" onclick="swipe({{ $c['other_user_id'] }}, true)">✓</button>
              </div>
            </div>

            <div>
              <div class="mb-2"><span class="score-box">Score: {{ $c['score_100'] }}%</span></div>
              <div class="h2 fw-bold mb-3">{{ $c['display_name'] }}, {{ $c['age'] ?? '—' }}</div>

              <div class="d-grid gap-2" style="max-width: 360px;">
                <a class="btn btn-outline-secondary btn-pill"
                  href="{{ route('profiles.show', ['user_id' => $c['other_user_id']]) }}?from=matches">
                  Show More ……
                </a>

                <div class="small text-muted">
                  They liked you — like back to create a match.
                </div>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="alert alert-secondary">No one has liked you yet.</div>
      @endforelse
    </div>
  </div>

  {{-- RIGHT FILTER --}}
  <aside class="filter-panel">
  <div class="h4 mb-3 fw-bold">Filter</div>

  <form method="GET" action="{{ route('matches') }}">
    <input type="hidden" name="sort" value="{{ request('sort','score') }}">

    <div class="mb-3">
      <label class="form-label fw-semibold">Current City</label>
      <select class="form-select" name="city">
        <option value="">Any</option>
        <option value="Leeds" @selected(request('city')==='Leeds')>Leeds</option>
        <option value="Manchester" @selected(request('city')==='Manchester')>Manchester</option>
        <option value="London" @selected(request('city')==='London')>London</option>
        <option value="Birmingham" @selected(request('city')==='Birmingham')>Birmingham</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label fw-semibold">Age Range</label>
      <div class="d-flex align-items-center gap-2">
        <input type="number" class="form-control" name="age_min" value="{{ request('age_min', 18) }}" min="16" max="99">
        <span class="fw-semibold">—</span>
        <input type="number" class="form-control" name="age_max" value="{{ request('age_max', 25) }}" min="16" max="99">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label fw-semibold">Tags</label>
      <div class="d-flex flex-wrap gap-2">
        <span class="chip">tag</span><span class="chip">tag</span><span class="chip">tag</span>
        <span class="chip">tag</span><span class="chip">tag</span><span class="chip">tag</span>
      </div>
      <div class="form-text">(We’ll wire tags later.)</div>
    </div>

    <div class="d-grid gap-2 mt-4">
      <button type="submit" class="btn btn-primary">Apply &amp; Search</button>
      <a class="btn btn-outline-secondary" href="{{ route('matches') }}">reset</a>
    </div>
  </form>
</aside>
</div>

<script>
  // Tabs
  const matchesList = document.getElementById('matchesList');
  const likedList = document.getElementById('likedList');
  document.getElementById('tabMatches')?.addEventListener('click', () => {
    matchesList.classList.remove('d-none');
    likedList.classList.add('d-none');
  });
  document.getElementById('tabLiked')?.addEventListener('click', () => {
    likedList.classList.remove('d-none');
    matchesList.classList.add('d-none');
  });

  
  // Filters are UI-only for now (we'll wire to DB next)
  document.getElementById('resetFilters')?.addEventListener('click', () => {
    document.getElementById('filterCity').value = '';
    document.getElementById('ageMin').value = 18;
    document.getElementById('ageMax').value = 25;
  });
  document.getElementById('applyFilters')?.addEventListener('click', () => {
    alert('We will wire these filters to backend queries next.');
  });
</script>
@endsection