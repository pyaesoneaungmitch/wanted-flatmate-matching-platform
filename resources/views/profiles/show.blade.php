@extends('layouts.app')
@section('title', 'Profile Details')

@section('content')
<style>
  .detail-wrap { max-width: 980px; margin: 0 auto; }
  .back-btn { border: 1px solid rgba(0,0,0,.2); border-radius: 10px; background:#fff; }
  .outer-card {
    background:#fff; border:2px solid rgba(0,0,0,.18); border-radius: 36px;
    padding: 18px; position: relative;
  }
  .score-top {
    position:absolute; top:-18px; left:50%; transform: translateX(-50%);
    background:#fff; border:2px solid rgba(0,0,0,.25); border-radius: 10px;
    padding: 6px 18px; font-weight: 900; font-size: 34px; line-height:1;
    min-width: 240px; text-align:center;
  }
  .photo-shell {
    background:#f1f3f5; border:1px solid rgba(0,0,0,.15);
    border-radius: 28px; height: 330px; overflow:hidden;
  }
  .chip { border:1px solid rgba(0,0,0,.25); border-radius: 8px; padding: 6px 10px; background:#fff; font-size: 14px; }
  .section-title { font-weight: 800; margin-bottom: 10px; }
  .locked {
    margin-top: 16px;
    background: #bdbdbd; border-radius: 26px;
    height: 180px; display:flex; align-items:center; justify-content:center;
    color: rgba(255,255,255,.95);
    border: 2px dashed rgba(0,0,0,.25);
  }
  .locked-inner { text-align:center; }
  .swipe-row { display:flex; justify-content:center; gap: 38px; margin-top: 18px; }
  .circle-action {
    width: 86px; height: 86px; border-radius: 999px;
    border:2px solid rgba(0,0,0,.25); background:#fff;
    display:flex; align-items:center; justify-content:center;
    font-size: 40px; line-height:1;
  }
  .divider-col { border-left: 2px solid rgba(0,0,0,.25); }

  /* Carousel dots look closer to wireframe */
  .carousel-indicators [data-bs-target]{
    width: 12px; height: 12px; border-radius: 999px;
    border: 1px solid rgba(0,0,0,.35);
    background-color: #cfcfcf;
    opacity: 1;
  }
  .carousel-indicators .active{ background-color:#fff; }
  .page-wrap{ max-width: 1180px; margin: 0 auto; padding: 18px 14px; }
  .panel{
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: 18px;
    box-shadow: var(--shadow);
  }
  .section-title{ font-weight: 900; font-size: 18px; margin: 0 0 10px; }
  .muted{ color: var(--muted); }
  .score-pill{
  display:inline-block;
  background: var(--accent-soft);
  border: 1px solid rgba(242,204,15,.55);
  color: var(--text);
  border-radius: 12px;
  padding: 6px 12px;
  font-weight: 900;
}
  .btn-like{
    background: var(--accent);
    border: 1px solid var(--accent);
    color: var(--text);
    font-weight: 900;
    border-radius: 12px;
  }
  .btn-like:hover{ filter: brightness(.96); }

  .btn-pass{
    background: #fff;
    border: 1px solid var(--border);
    color: var(--text);
    font-weight: 900;
    border-radius: 12px;
  }
  .locked{
  background: rgba(17,17,17,.04);
  border: 1px dashed rgba(17,17,17,.18);
  border-radius: 16px;
  padding: 5px;
  color:rgba(0, 0, 0, 0.55);
}
  .locked .lock-icon{
    width: 44px; height: 44px; border-radius: 14px;
    background: var(--accent-soft);
    border: 1px solid rgba(242,204,15,.55);
    display:flex; align-items:center; justify-content:center;
    font-size: 20px; font-weight: 900;
    
  }
.btn-pass:hover{ background: var(--accent-soft); }
.private-wrap{
      margin-top: 16px;
      background:#bdbdbd;
      border-radius: 18px;
      padding: 14px;
    }
    .private-title{
      font-weight: 900;
      color:#fff;
      font-size: 18px;
      margin-bottom: 10px;
      display:flex;
      align-items:center;
      gap:10px;
    }
    .private-grid{
      display:grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }
    .private-box{
      background:#fff;
      border-radius: 10px;
      padding: 10px;
      border: 1px solid rgba(0,0,0,.18);
    }
    .private-row{
      display:grid;
      grid-template-columns: 140px 1fr;
      align-items:center;
      gap: 10px;
      margin-bottom: 8px;
    }
    .private-row:last-child{ margin-bottom: 0; }
    .p-label{ font-weight: 800; font-size: 13px; color:#333; }
    .p-value{
      background:#f1f3f5;
      border: 1px solid rgba(0,0,0,.18);
      border-radius: 8px;
      padding: 6px 10px;
      font-size: 13px;
      color:#111;
    }
    .pillrow{ display:flex; gap: 10px; flex-wrap: wrap; }
    .pill{
      background:#f1f3f5;
      border: 1px solid rgba(0,0,0,.18);
      border-radius: 10px;
      padding: 6px 10px;
      font-size: 13px;
    }
    body{ background: var(--bg) !important; }
  </style>

<div class="detail-wrap">
  <div class="mb-3">
    <a href="{{ route('discover') }}" class="btn back-btn">BACK</a>
  </div>

  <div class="outer-card">
    <div class="score-pill">Score: {{ $score }}%</div>

    <div class="row g-4 pt-3">
      {{-- Left: Photo carousel --}}
      <div class="col-md-5">
        <div id="photoCarousel" class="carousel slide photo-shell" data-bs-ride="false">
          <div class="carousel-indicators">
            @php $count = max(1, count($photos)); @endphp
            @for ($i=0; $i<$count; $i++)
              <button type="button" data-bs-target="#photoCarousel" data-bs-slide-to="{{ $i }}"
                class="{{ $i===0 ? 'active' : '' }}" aria-current="{{ $i===0 ? 'true' : 'false' }}"></button>
            @endfor
          </div>

          <div class="carousel-inner h-100">
            @if(count($photos) === 0)
              <div class="carousel-item active h-100">
                <div class="h-100 d-flex align-items-center justify-content-center text-muted fw-semibold">
                  Photo
                </div>
              </div>
            @else
              @foreach($photos as $idx => $url)
                <div class="carousel-item {{ $idx===0 ? 'active' : '' }} h-100">
                  <img src="{{ url($url) }}" class="d-block w-100 h-100" style="object-fit:cover;" alt="Photo">
                </div>
              @endforeach
            @endif
          </div>
        </div>
      </div>

      {{-- Right: summary --}}
      <div class="col-md-7">
        <div class="h1 fw-bold mb-2">
          {{ $public->display_name }}, {{ $public->age ?? '—' }}
        </div>

        {{-- Tags (placeholder for now; later map from quiz booleans) --}}
        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="chip">Student</span>
          <span class="chip">Gym</span>
          <span class="chip">Quiet</span>
          <span class="chip">tag</span>
          <span class="chip">tag</span>
          <span class="chip">tag</span>
        </div>

        <div class="text-muted fst-italic">"Bio"</div>
        <div class="text-muted mb-3">{{ $public->bio ?? '' }}</div>

        <div class="fw-bold">City</div>
        <div class="mb-2">{{ $public->city ?? '—' }}</div>

        <div class="fw-bold">Budget - £{{ $public->budget_min ?? '—' }} – £{{ $public->budget_max ?? '—' }}/mo</div>
      </div>
    </div>

    {{-- Middle sections --}}
    <div class="row g-0 mt-4">
      <div class="col-md-4 pe-3">
        <div class="section-title">Hobbies</div>
        <div class="d-flex flex-wrap gap-2">
          <span class="chip">Icon + Text</span>
          <span class="chip">Icon + Text</span>
          <span class="chip">Icon + Text</span>
          <span class="chip">Icon + Text</span>
          <span class="chip">Icon + Text</span>
        </div>
      </div>

      <div class="col-md-4 px-3 divider-col">
        <div class="section-title">Preferences</div>
        <div class="d-flex flex-wrap gap-2">
          <span class="chip">Icon + Text</span>
          <span class="chip">Icon + Text</span>
          <span class="chip">Icon + Text</span>
          <span class="chip">Icon + Text</span>
        </div>
      </div>

      <div class="col-md-4 ps-3 divider-col">
        <div class="section-title">Looking For..</div>

        @php
          $lookingForLabel = null;
          if (!empty($quiz?->looking_for_type)) {
            $lookingForLabel = match($quiz->looking_for_type) {
              'ROOMMATE' => 'Roommate',
              'ROOM_AND_ROOMMATE' => 'Room + Roommate',
              'JOIN_GROUP' => 'Joining a group',
              default => $quiz->looking_for_type,
            };
          }
        @endphp

        <div class="d-flex flex-wrap gap-2">
          <span class="chip">{{ $lookingForLabel ?? '—' }}</span>
        </div>
      </div>
    </div>

    {{-- Private profile lock section --}}
    @if(!$privateShare)
      <div class="locked">
        <div class="locked-icon">
          <div style="font-size:64px; line-height:1;">🔒</div>
          <div class="mt-2 fw-semibold">
            Private Profile is Locked and<br>
            yet to be shared by the owner
          </div>
        </div>
      </div>
    @else
    

  <div class="private-wrap">
    <div class="private-title">Private Profile ✓</div>

    <div class="private-grid">
      {{-- LEFT COLUMN --}}
      <div class="private-box">
        <div class="private-row">
          <div class="p-label">Work</div>
          <div class="p-value">{{ $private->occupation ?? '—' }}</div>
        </div>
        <div class="private-row">
          <div class="p-label">Zodiac</div>
          <div class="p-value">{{ $private->zodiac ?? '—' }}</div>
        </div>

        <div class="private-row">
          <div class="p-label">Working hours</div>
          <div class="p-value">{{ $private->working_hours ?? '—' }}</div>
        </div>

        <div class="private-row">
          <div class="p-label">Sleeping habits</div>
          <div class="p-value">{{ $private->sleep_schedule ?? '—' }}</div>
        </div>
      </div>

      {{-- RIGHT COLUMN --}}
      <div class="private-box">
        <div class="private-row">
          <div class="p-label">Move in date</div>
          <div class="p-value">{{ $private->move_in_date ?? '—' }}</div>
        </div>

        <div class="private-row">
          <div class="p-label">Preferred Contract Length</div>
          <div class="p-value">
            {{ $private->contract_length_months ? ($private->contract_length_months . ' mo') : '—' }}
          </div>
        </div>

        <div class="private-row">
          <div class="p-label">Room Preference</div>
          <div class="p-value">{{ $private->room_preference ?? '—' }}</div>
        </div>

        <div class="private-row">
          <div class="p-label">Guest Policy</div>
          <div class="p-value">{{ $private->guest_policy ?? '—' }}</div>
        </div>
      </div>

      {{-- FULL-WIDTH ROWS (bottom) --}}
      <div class="private-box" style="grid-column: 1 / -1;">
        <div class="private-row">
          <div class="p-label">Allergies</div>
          <div class="p-value">
            {{ trim(($private->food_allergies ?? '') . ' ' . ($private->pet_allergies ?? '')) ?: '—' }}
          </div>
        </div>

        <div class="private-row">
          <div class="p-label">Noise Tolerance</div>
          <div class="p-value">{{ $private->noise_tolerance ?? '—' }}</div>
        </div>
      </div>

      <div class="private-box" style="grid-column: 1 / -1;">
        <div class="private-row">
          <div class="p-label">Phone Number</div>
          <div class="p-value">{{ $private->phone_number ?? '—' }}</div>
        </div>

        <div class="private-row">
          <div class="p-label">Email</div>
          <div class="p-value">{{ $private->contact_email ?? '—' }}</div>
        </div>

        <div class="private-row">
          <div class="p-label">Socials</div>
          <div class="pillrow">
            <span class="pill">IG: {{ $private->instagram ?? '—' }}</span>
            <span class="pill">X: {{ $private->twitter ?? '—' }}</span>
            <span class="pill">Snap: {{ $private->snapchat ?? '—' }}</span>
          </div>
        </div>
      </div>

    </div>
  </div>
@endif

    {{-- Swipe actions --}}
     @if(!in_array(($from ?? null), ['matches','inbox','profile']))
      <div class="swipe-row">
        <button type="button" class="btn btn-like" id="btnPass">✕</button>
        <button type="button" class="btn btn-pass" id="btnLike">✓</button>
      </div>
      @endif
  </div>
</div>

<script>
  async function swipe(like) {
    const res = await fetch("{{ route('swipe.store') }}", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": "{{ csrf_token() }}",
        "Accept": "application/json"
      },
      body: JSON.stringify({ to_user_id: {{ $otherUserId }}, like })
    });

    if (!res.ok) {
  alert("Swipe failed.");
  return;
}

    // Show popup
    if (like && window.showLikedPopup) window.showLikedPopup();
    if (!like && window.showPassedPopup) window.showPassedPopup();

    // Small delay so user actually sees it
    setTimeout(() => {
      window.location.href = "{{ route('discover') }}";
    }, like ? 900 : 700);;
      }

  document.getElementById('btnPass')?.addEventListener('click', () => swipe(false));
  document.getElementById('btnLike')?.addEventListener('click', () => swipe(true));
</script>
@endsection