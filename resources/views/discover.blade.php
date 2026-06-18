@extends('layouts.app')
@section('title', 'Discover')

@section('content')
<style>
  /* Discover page layout */
  .discover-shell {
    display: grid;
    grid-template-columns: 1fr 320px; /* center + filter sidebar */
    gap: 20px;
    align-items: start;
  }

  /* Make the card list scrollable (middle section) */
  .card-list {
    max-height: calc(100vh - 140px);
    overflow-y: auto;
    padding-right: 8px;
  }

  /* Filter sidebar */
  .filter-panel {
    position: sticky;
    top: 16px;
    background: #fff;
    border: 1px solid rgba(0,0,0,.1);
    border-radius: 12px;
    padding: 16px;
  }

  /* Card styling */
  .profile-card {
    background: #fff;
    border: 2px solid rgba(0,0,0,.15);
    border-radius: 24px;
    padding: 18px;
    margin-bottom: 18px;
    box-shadow: 0 2px 10px rgba(0,0,0,.04);
  }

  .profile-card-inner {
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
  }

  .action-row {
    display: flex;
    gap: 18px;
    justify-content: center;
    margin-top: 14px;
  }

  .circle-action {
    width: 74px;
    height: 74px;
    border-radius: 999px;
    border: 2px solid rgba(0,0,0,.25);
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 34px;
    line-height: 1;
    transition: transform .1s ease;
  }
  .circle-action:hover { transform: scale(1.03); }
  .circle-action.pass { color: #111; }
  .circle-action.like { color: #111; }

  .score-box {
    display: inline-block;
    padding: 6px 14px;
    border: 2px solid rgba(0,0,0,.25);
    border-radius: 8px;
    font-weight: 800;
    font-size: 28px;
    letter-spacing: .5px;
    background: #fff;
  }

  .tags-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 10px 0 12px;
  }

  .tag-pill {
    border: 1px solid rgba(0,0,0,.25);
    border-radius: 8px;
    padding: 4px 10px;
    font-size: 14px;
    background: #F2CC0F;
  }

  .label-strong { font-weight: 800; }

  .show-more {
    border-radius: 10px;
    border: 1px solid rgba(0,0,0,.25);
    background:rgba(242,204,15,.9);
  }

  /* Top bar */
  .topbar {
    background: #fff;
    border: 1px solid rgba(0,0,0,.1);
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    gap: 12px;
    align-items: center;
    justify-content: flex-start;
    margin-bottom: 14px;
  }

  @media (max-width: 1200px) {
    .discover-shell { grid-template-columns: 1fr; }
    .filter-panel { position: static; }
  }
  .shell{ display:grid; grid-template-columns: 1fr 320px; gap:20px; align-items:start; }

.topbar{
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 14px 16px;
  box-shadow: var(--shadow);
  display:flex;
  gap:12px;
  align-items:center;
  margin-bottom:14px;
}

.card-list{ max-height: calc(100vh - 180px); overflow-y:auto; padding-right:8px; }

.filter-panel{
  position: sticky;
  top: 16px;
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 16px;
  box-shadow: var(--shadow);
}
.user-card{
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 22px;
  padding: 18px;
  margin-bottom: 18px;
  box-shadow: var(--shadow);
}

.photo-box{
  width:100%;
  height:260px;
  border-radius:26px;
  background:#f1f3f5;
  border: 1px solid var(--border);
  overflow:hidden;
  display:flex;
  align-items:center;
  justify-content:center;
  color: var(--muted);
  font-weight:700;
}

.score-pill{
  display:inline-block;
  background: var(--accent-soft);
  border: 1px solid rgba(242,204,15,.55);
  color: var(--text);
  border-radius: 12px;
  padding: 6px 12px;
  font-weight: 900;
  letter-spacing:.2px;
}
.circle-btn{
  width:64px;height:64px;border-radius:999px;
  display:flex;align-items:center;justify-content:center;
  border: 1px solid var(--border);
  background: var(--panel);
  box-shadow: 0 8px 18px rgba(17,17,17,.10);
  font-size: 26px;
  transition: transform .12s ease, filter .12s ease;
}
.circle-btn:hover{ transform: translateY(-1px); }

.circle-btn.like{
  background: var(--accent);
  border-color: rgba(242,204,15,.9);
  color: var(--text);
  font-weight:900;
}
.circle-btn.pass{
  background: var(--panel);
  color: var(--text);
}
.tag-pill{
  display:inline-flex;
  align-items:center;
  gap:8px;
  padding: 6px 12px;
  border-radius: 999px;
  border: 1px solid var(--border);
  background: #F2CC0F;
  color: var(--text);
  font-weight: 700;
  font-size: 13px;
  line-height: 1;
}

.tag-pill.accent{
  background: var(--accent-soft);
  border-color: rgba(242,204,15,.55);
}
</style>

<div class="discover-shell">
  {{-- CENTER: Sorting + Scrollable Cards --}}
  <div>
    <div class="topbar">
      <div class="fw-semibold">Sorted By :</div>
      <form method="GET" action="{{ route('discover') }}" class="d-flex align-items-center gap-2">
        <input type="hidden" name="mode" value="{{ $mode ?? 'fresh' }}">
        <input type="hidden" name="city" value="{{ request('city') }}">
        <input type="hidden" name="age_min" value="{{ request('age_min') }}">
        <input type="hidden" name="age_max" value="{{ request('age_max') }}">
        <input type="hidden" name="pref" value="{{ request('pref','any') }}">

        <select class="form-select" style="max-width: 260px;" name="sort" onchange="this.form.submit()">
          <option value="score" @selected(request('sort','score')==='score')>Compatibility Score</option>
          <option value="budget" @selected(request('sort')==='budget')>Budget</option>
          <option value="age" @selected(request('sort')==='age')>Age</option>
        </select>
      </form>
      <a class="btn btn-wanted-outline" href="{{ route('discover', ['mode' => $mode ?? 'fresh', 'score' => 'ai']) }}">
        Try AI mode (beta)✨
      </a>

      @if(($scoreMode ?? 'rule') === 'ai')
        <a class="btn btn-wanted-outline" href="{{ route('discover', ['mode' => $mode ?? 'fresh']) }}">
          Exit AI mode
        </a>
      @endif

      <div class="ms-auto small text-muted">
        Showing {{ count($cards ?? []) }} result(s)
      </div>
    </div>

    <div class="card-list">
      @foreach (($cards ?? []) as $card)
        <div class="profile-card" data-user-id="{{ $card['user_id'] ?? 0 }}">
          <div class="profile-card-inner">
            {{-- Left: Photo + actions --}}
            <div>
              <div class="photo-box">
                @if(!empty($card['photo_url']))
                  <img src="{{ url($card['photo_url']) }}" alt="Photo"
     style="width:100%;height:100%;object-fit:cover;border-radius:26px;">
                @else
                  Photo
                @endif
              </div>

              <div class="action-row">
                <button type="button"
                        class="circle-action pass js-pass circle-btn pass"
                        data-user-id="{{ $card['user_id'] }}"
                        title="Pass">✕</button>

                <button type="button"
                        class="circle-action like js-like circle-btn like"
                        data-user-id="{{ $card['user_id'] }}"
                        title="Like">✓</button>
              </div>
            </div>

            {{-- Right: Details --}}
            <div>
              
              <div class="mb-2">
                @if(($scoreMode ?? 'rule') === 'ai')
                  <span class="score-pill">AI Score: {{ (int)($card['ai_score_100'] ?? $card['score_100']) }}%</span>
                  @php $p = $card['ai_p'] ?? null; @endphp
                  <div class="small text-muted mt-1">p(match): {{ $p !== null ? number_format($p, 2) : '—' }}</div>
                @else
                  <span class="score-pill">Score: {{ (int)($card['score_100'] ?? 0) }}%</span>
                @endif
              </div>

              <div class="h2 mb-2" style="font-weight: 800;">
                {{ $card['display_name'] ?? 'Name' }},
                <span style="font-weight: 800;">
                  {{ $card['age'] ?? '—' }}
                </span>
              </div>

              {{-- Tags (hardcoded for now) --}}
              <div class="tags-row">
                <span class="tag-pill">Student</span>
                <span class="tag-pill">Gym</span>
                <span class="tag-pill">Quiet</span>
                <span class="tag-pill">…</span>
              </div>

              <div class="mb-2">
                <div class="fst-italic text-muted">"Bio"</div>
                <div class="text-muted">
                  {{ $card['bio'] ?? '' }}
                </div>
              </div>

              <div class="mt-3">
                <div class="label-strong">City</div>
                <div class="text-muted">{{ $card['city'] ?? '—' }}</div>
              </div>

              <div class="mt-2">
                <div class="label-strong">Budget -</div>
                <div class="text-muted">
                  £{{ $card['budget_min'] ?? '—' }} – £{{ $card['budget_max'] ?? '—' }}/mo
                </div>
              </div>

              <div class="mt-3">
                <a class="btn btn-wanted-outline w-100 show-more"
                href="{{ route('profiles.show', ['user_id' => $card['user_id']]) }}">
                Show More ……
              </a>
              </div>
            </div>
          </div>
        </div>
      @endforeach

     @if (count($cards) === 0)
  <div class="bg-white border rounded-4 p-5 text-center" style="border-color: rgba(0,0,0,.12) !important;">
        @if(($mode ?? 'fresh') === 'fresh' && ($hasPassed ?? false))
          <div style="font-size:64px; line-height:1;">🧭</div>
          <h3 class="fw-bold mt-3">You’ve seen everyone for now</h3>
          <p class="text-muted mb-4">
            Want to give the people you passed a second chance?
          </p>
          <a class="btn btn-wanted btn-lg" href="{{ route('discover') }}?mode=second_chance">
            Show passed users
          </a>
        @else
          <div style="font-size:64px; line-height:1;">🎉</div>
          <h3 class="fw-bold mt-3">No more users to show</h3>
          <p class="text-muted mb-0">
            You’re all caught up. Check back later for new people.
          </p>
        @endif
      </div>
    @endif
    </div>
  </div>

{{-- RIGHT: Filter sidebar --}}
<aside class="filter-panel">
  <div class="h4 mb-3" style="font-weight: 800;">Filter</div>

  <form method="GET" action="{{ route('discover') }}">
    {{-- Keep mode + sort when applying filters --}}
    <input type="hidden" name="mode" value="{{ $mode ?? 'fresh' }}">
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
      <label class="form-label fw-semibold">Preferences</label>

      @php $pref = request('pref','any'); @endphp

      <div class="form-check">
        <input class="form-check-input" type="radio" name="pref" id="prefAny" value="any" @checked($pref==='any')>
        <label class="form-check-label" for="prefAny">Any</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="pref" id="prefStudent" value="student" @checked($pref==='student')>
        <label class="form-check-label" for="prefStudent">Student</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="pref" id="prefGym" value="gym" @checked($pref==='gym')>
        <label class="form-check-label" for="prefGym">Gym</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="pref" id="prefQuiet" value="quiet" @checked($pref==='quiet')>
        <label class="form-check-label" for="prefQuiet">Quiet</label>
      </div>
    </div>

    <div class="d-grid gap-2 mt-4">
      <button type="submit" class="btn btn-wanted">Apply &amp; Search</button>

      {{-- Reset: keep mode, clear filters --}}
      <a class="btn btn-wanted-outline" href="{{ route('discover') }}?mode={{ $mode ?? 'fresh' }}">Reset</a>
    </div>
  </form>
</aside>
</div>

<script>
  // UI-only stubs for now (no backend wiring yet)
  document.getElementById('resetFilters')?.addEventListener('click', () => {
    document.getElementById('filterCity').value = '';
    document.getElementById('ageMin').value = 18;
    document.getElementById('ageMax').value = 25;
    document.getElementById('prefAny').checked = true;
  });

  document.getElementById('applyFilters')?.addEventListener('click', () => {
    alert('Filters will be wired to the backend next.');
  });
</script>
<script>
  async function sendSwipe(toUserId, like, cardEl) {
    const res = await fetch("{{ route('swipe.store') }}", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}",
        "Accept": "application/json"
      },
     body: JSON.stringify({
        to_user_id: parseInt(toUserId, 10),
        like: !!like,
        mode: "{{ $mode ?? 'fresh' }}"
      })
    });

    if (!res.ok) {
        const txt = await res.text();
        console.log('Swipe error status', res.status, txt);
        alert("Swipe failed: " + res.status);
        return;
      }

    // Show animated confirmation only on LIKE
    if (like && window.showLikedPopup) window.showLikedPopup();
    if (!like && window.showPassedPopup) window.showPassedPopup();


    // Remove card from UI (simple)
    if (cardEl) cardEl.remove();
  }

  // Event delegation so it works even if cards rerender
  document.addEventListener('click', (e) => {
    const likeBtn = e.target.closest('.js-like');
    const passBtn = e.target.closest('.js-pass');
    if (!likeBtn && !passBtn) return;

    const btn = likeBtn || passBtn;
    const userId = btn.getAttribute('data-user-id');
    const cardEl = btn.closest('.profile-card');
    sendSwipe(userId, !!likeBtn, cardEl);
  });
</script>
@endsection