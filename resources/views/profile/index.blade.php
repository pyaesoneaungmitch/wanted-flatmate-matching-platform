@extends('layouts.app')
@section('title', 'Profile')

@section('content')
<style>
  .p-grid{ display:grid; grid-template-columns: 1fr 420px; gap: 18px; align-items:start; }
  .cardish{ background:#fff; border:2px solid rgba(0,0,0,.12); border-radius: 24px; padding: 16px; }
  .photo-main{ width: 240px; height: 180px; border-radius: 22px; background:#f1f3f5; border:1px solid rgba(0,0,0,.12); display:flex; align-items:center; justify-content:center; font-weight:700; color:rgba(0,0,0,.45); overflow:hidden; }
  .gallery-grid{ display:grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
  .g-tile{ height: 300px; border-radius: 18px; background:#f1f3f5; border:1px solid rgba(0,0,0,.12); display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden; }
  .pen{ cursor:pointer; opacity:.75; }
  .pen:hover{ opacity:1; }
  .hidden{ display:none !important; }

  .private-wrap{ background:#bdbdbd; border-radius: 18px; padding: 14px; margin-top: 18px; }
  .private-title{ font-weight:900; color:#fff; font-size: 22px; display:flex; align-items:center; gap:10px; justify-content:space-between; }
  .private-grid{ display:grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 10px; }
  .private-box{ background:#fff; border-radius: 10px; padding: 10px; border: 1px solid rgba(0,0,0,.18); }
  .private-row{ display:grid; grid-template-columns: 160px 1fr; gap: 10px; align-items:center; margin-bottom: 8px; }
  .p-label{ font-weight: 800; font-size: 13px; }
</style>

{{-- Confirm modal (page-local, simple) --}}
<div class="modal fade" id="confirmSaveModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-body p-4">
        <div class="fw-bold fs-5 mb-2" id="confirmTitle">Confirm changes</div>
        <div class="text-muted" id="confirmBody">Are you sure you want to save these changes?</div>
        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="confirmYes">Yes, save</button>
        </div>
      </div>
    </div>
  </div>
</div>

@php
  $meId = auth()->user()->user_id;
@endphp

<div class="mb-3">
  <h2 class="fw-bold">Profile (Public)</h2>
</div>

<div class="p-grid">
  {{-- LEFT: main public card + gallery + prefs/hobbies blocks --}}
  <div>
    {{-- Top public card --}}
    <div class="cardish mb-3">
      <div class="d-flex gap-3">
        <div class="photo-main">
          @php $firstPhoto = $photos->first(); @endphp
          @if($firstPhoto)
            <img src="{{ url($firstPhoto->photo_url) }}" style="width:100%;height:100%;object-fit:cover;" alt="Photo">
          @else
            Photo
          @endif
        </div>

        <div class="flex-grow-1">
          {{-- Name/Age edit --}}
          <div class="d-flex align-items-center gap-2">
            <h2 class="fw-bold mb-1">Name, Age</h2>
            <span class="pen" id="penPublic">✎</span>
          </div>

          <form id="formPublic" method="POST" action="{{ route('profile.public.update') }}">
            @csrf

            <div id="publicView">
              <div class="fw-bold">{{ $public->display_name ?? '—' }}, {{ $public->age ?? '—' }}</div>
              <div class="text-muted">City</div>
              <div class="fw-semibold">{{ $public->city ?? '—' }}</div>
              <div class="text-muted mt-1">Budget - £{{ $public->budget_min ?? '—' }} – £{{ $public->budget_max ?? '—' }}/mo</div>
            </div>

            <div id="publicEdit" class="hidden">
              <div class="row g-2">
                <div class="col-md-6">
                  <label class="form-label">Display name</label>
                  <input class="form-control" name="display_name" value="{{ $public->display_name ?? '' }}" required>
                </div>
                <div class="col-md-3">
                  <label class="form-label">Age</label>
                  <input class="form-control" type="number" name="age" min="16" max="99" value="{{ $public->age ?? '' }}">
                </div>
                <div class="col-md-6">
                  <label class="form-label">City</label>
                  <input class="form-control" name="city" value="{{ $public->city ?? '' }}">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Budget min</label>
                  <input class="form-control" type="number" name="budget_min" min="0" max="10000" value="{{ $public->budget_min ?? '' }}">
                </div>
                <div class="col-md-3">
                  <label class="form-label">Budget max</label>
                  <input class="form-control" type="number" name="budget_max" min="0" max="10000" value="{{ $public->budget_max ?? '' }}">
                </div>
              </div>

              <div class="d-flex gap-2 mt-3">
                <button type="button" class="btn btn-primary" onclick="confirmSubmit('formPublic')">Save</button>
                <button type="button" class="btn btn-outline-secondary" onclick="cancelEdit('public')">Cancel</button>
              </div>
            </div>
          </form>

          <div class="d-flex gap-2 mt-3">
            <a class="btn btn-secondary" href="{{ route('profiles.show', ['user_id' => $meId]) }}?from=profile">👁 Preview</a>
            <a class="btn btn-secondary" href="{{ route('quiz.show') }}">❓ Retake Quiz</a>
          </div>
        </div>
      </div>
    </div>

    {{-- Gallery grid + upload --}}
    <div class="cardish mb-3">
      <div class="gallery-grid">
        @foreach($photos as $ph)
          <div class="g-tile">
            <img src="{{ url($ph->photo_url) }}" style="width:100%;height:100%;object-fit:cover;" alt="Photo">
            <form method="POST" action="{{ route('profile.photos.delete', ['photo_id' => $ph->photo_id]) }}"
                  style="position:absolute; top:8px; right:8px;">
              @csrf
              <button class="btn btn-sm btn-light" type="submit" title="Delete">🗑</button>
            </form>
          </div>
        @endforeach

        @for($i = $photos->count(); $i < 6; $i++)
          <div class="g-tile">
            <form method="POST" action="{{ route('profile.photos.upload') }}" enctype="multipart/form-data">
              @csrf
              <input type="file" name="photo" accept="image/*" required class="form-control form-control-sm"
                     onchange="this.form.submit()">
              <div class="mt-2 fw-bold" style="font-size:28px;">+</div>
            </form>
          </div>
        @endfor
      </div>
      <div class="small text-muted mt-2">Upload up to 6 photos. (Auto-uploads when selected.)</div>
    </div>

    {{-- Preferences/Hobbies placeholders (wireframe only for now) --}}
    <div class="d-flex gap-3">
      <div class="cardish flex-grow-1">
        <div class="fw-bold mb-2">Preferences</div>
        <div class="d-flex flex-wrap gap-2">
          <span class="btn btn-outline-secondary btn-sm">Icon + Text</span>
          <span class="btn btn-outline-secondary btn-sm">Icon + Text</span>
          <span class="btn btn-outline-secondary btn-sm">Icon + Text</span>
        </div>
        <button class="btn btn-outline-secondary btn-sm mt-3" disabled>Add..</button>
      </div>

      <div class="cardish flex-grow-1">
        <div class="fw-bold mb-2">Hobbies</div>
        <div class="d-flex flex-wrap gap-2">
          <span class="btn btn-outline-secondary btn-sm">Icon + Text</span>
          <span class="btn btn-outline-secondary btn-sm">Icon + Text</span>
          <span class="btn btn-outline-secondary btn-sm">Icon + Text</span>
        </div>
        <button class="btn btn-outline-secondary btn-sm mt-3" disabled>Edit..</button>
      </div>
    </div>
  </div>

  {{-- RIGHT: About Me + Tags + Looking For (wireframe blocks) --}}
  <div>
    <div class="cardish mb-3">
      <div class="d-flex align-items-center justify-content-between">
        <div class="fw-bold">About Me</div>
        <span class="pen" id="penAbout">✎</span>
      </div>
      <div class="d-flex gap-2 align-items-center mt-2">
        <select class="form-select" id="bioTone" style="max-width:180px;">
          <option value="fun">Fun</option>
          <option value="calm">Calm</option>
          <option value="premium">Premium</option>
        </select>

        <button type="button" class="btn btn-wanted" id="genBioBtn">
          Generate bio (AI)
        </button>

        <div class="small text-muted" id="bioGenStatus"></div>
      </div>
      <div id="bioPreviewWrap" class="d-none mt-3">
        <div class="fw-bold mb-2">AI suggestion</div>
        <button type="button" id="bioPreviewBtn"
                class="btn btn-wanted-outline w-100 text-start"
                style="white-space:normal;">
          <!-- filled by JS -->
        </button>
        <div class="small text-muted mt-2">Click the card to use it as your bio.</div>
      </div>

      <div class="mt-2 d-none" id="bioSuggestions"></div>

      <form id="formAbout" method="POST" action="{{ route('profile.about.update') }}">
        @csrf
        <div id="aboutView" class="text-muted mt-2" style="min-height: 120px;">
          {{ $public->bio ?? '' }}
        </div>

        <div id="aboutEdit" class="hidden mt-2">
          <textarea class="form-control"  id="bioFieldVisible" name="bio" rows="6" maxlength="300">{{ $public->bio ?? '' }}</textarea>
          <div class="d-flex gap-2 mt-3">
            <button type="button" class="btn btn-primary" onclick="confirmSubmit('formAbout')">Save</button>
            <button type="button" class="btn btn-outline-secondary" onclick="cancelEdit('about')">Cancel</button>
          </div>
        </div>
      </form>
    </div>

    <div class="cardish mb-3">
      <div class="fw-bold">My Tags</div>
      <div class="d-flex flex-wrap gap-2 mt-2">
        <span class="btn btn-outline-secondary btn-sm">tag</span>
        <span class="btn btn-outline-secondary btn-sm">tag</span>
        <span class="btn btn-outline-secondary btn-sm">tag</span>
      </div>
      <button class="btn btn-outline-secondary btn-sm mt-2" disabled>Edit</button>
    </div>

    <div class="cardish">
      <div class="fw-bold">Looking For</div>
      <div class="d-flex flex-wrap gap-2 mt-2">
        <span class="btn btn-outline-secondary btn-sm">Icon + Text</span>
        <span class="btn btn-outline-secondary btn-sm">Icon + Text</span>
        <span class="btn btn-outline-secondary btn-sm">Add..</span>
      </div>
    </div>
  </div>
</div>

{{-- Private profile section --}}
<div class="private-wrap">
  <div class="private-title">
    <span>Private Profile</span>
    <span class="pen" id="penPrivate">✎</span>
  </div>

  <form id="formPrivate" method="POST" action="{{ route('profile.private.update') }}">
    @csrf

    <div id="privateView">
      <div class="private-grid">
        <div class="private-box">
          <div class="private-row"><div class="p-label">Work</div><div class="p-value">{{ $private->occupation ?? '—' }}</div></div>
          <div class="private-row"><div class="p-label">Zodiac</div><div class="p-value">{{ $private->zodiac ?? '—' }}</div></div>
          <div class="private-row"><div class="p-label">Working hours</div><div class="p-value">{{ $private->working_hours ?? '—' }}</div></div>
          <div class="private-row"><div class="p-label">Sleep schedule</div><div class="p-value">{{ $private->sleep_schedule ?? '—' }}</div></div>
        </div>
        <div class="private-box">
          <div class="private-row"><div class="p-label">Move in date</div><div class="p-value">{{ $private->move_in_date ?? '—' }}</div></div>
          <div class="private-row"><div class="p-label">Contract (months)</div><div class="p-value">{{ $private->contract_length_months ?? '—' }}</div></div>
          <div class="private-row"><div class="p-label">Room preference</div><div class="p-value">{{ $private->room_preference ?? '—' }}</div></div>
          <div class="private-row"><div class="p-label">Guest policy</div><div class="p-value">{{ $private->guest_policy ?? '—' }}</div></div>
        </div>
      </div>
    </div>

    <div id="privateEdit" class="hidden mt-3">
      <div class="row g-2">
        <div class="col-md-6">
          <label class="form-label">Occupation</label>
          <input class="form-control" name="occupation" value="{{ $private->occupation ?? '' }}">
        </div>
        <div class="col-md-6">
          <label class="form-label">Zodiac</label>
          <input class="form-control" name="zodiac" value="{{ $private->zodiac ?? '' }}">
        </div>

        <div class="col-md-4">
          <label class="form-label">Working hours</label>
          <select class="form-select" name="working_hours">
            <option value="">—</option>
            @foreach(['MORNING','DAY','NIGHT','MIXED'] as $v)
              <option value="{{ $v }}" @selected(($private->working_hours ?? '') === $v)>{{ $v }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Sleep schedule</label>
          <select class="form-select" name="sleep_schedule">
            <option value="">—</option>
            @foreach(['EARLY_BIRD','NIGHT_OWL'] as $v)
              <option value="{{ $v }}" @selected(($private->sleep_schedule ?? '') === $v)>{{ $v }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Move-in date</label>
          <input type="date" class="form-control" name="move_in_date" value="{{ $private->move_in_date ?? '' }}">
        </div>

        <div class="col-md-4">
          <label class="form-label">Contract length (months)</label>
          <input type="number" class="form-control" name="contract_length_months" min="1" max="60" value="{{ $private->contract_length_months ?? '' }}">
        </div>

        <div class="col-md-4">
          <label class="form-label">Room preference</label>
          <select class="form-select" name="room_preference">
            <option value="">—</option>
            @foreach(['ENSUITE','SHARED_BATH','NO_PREF'] as $v)
              <option value="{{ $v }}" @selected(($private->room_preference ?? '') === $v)>{{ $v }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label">Guest policy</label>
          <select class="form-select" name="guest_policy">
            <option value="">—</option>
            @foreach(['OK','LIMITED'] as $v)
              <option value="{{ $v }}" @selected(($private->guest_policy ?? '') === $v)>{{ $v }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Food allergies</label>
          <input class="form-control" name="food_allergies" maxlength="120" value="{{ $private->food_allergies ?? '' }}">
        </div>
        <div class="col-md-6">
          <label class="form-label">Pet allergies</label>
          <input class="form-control" name="pet_allergies" maxlength="120" value="{{ $private->pet_allergies ?? '' }}">
        </div>

        <div class="col-md-4">
          <label class="form-label">Noise tolerance (0–10)</label>
          <input type="number" class="form-control" name="noise_tolerance" min="0" max="10" value="{{ $private->noise_tolerance ?? '' }}">
        </div>
      </div>

      <div class="d-flex gap-2 mt-3">
        <button type="button" class="btn btn-primary" onclick="confirmSubmit('formPrivate')">Save</button>
        <button type="button" class="btn btn-outline-secondary" onclick="cancelEdit('private')">Cancel</button>
      </div>
    </div>
  </form>
</div>

<script>
  // Edit toggles
  document.getElementById('penPublic')?.addEventListener('click', () => startEdit('public'));
  document.getElementById('penAbout')?.addEventListener('click', () => startEdit('about'));
  document.getElementById('penPrivate')?.addEventListener('click', () => startEdit('private'));

  function startEdit(section){
    document.getElementById(section+'View')?.classList.add('hidden');
    document.getElementById(section+'Edit')?.classList.remove('hidden');
  }
  function cancelEdit(section){
    document.getElementById(section+'Edit')?.classList.add('hidden');
    document.getElementById(section+'View')?.classList.remove('hidden');
  }

  // Confirm-before-submit
  function confirmSubmit(formId){
    const modalEl = document.getElementById('confirmSaveModal');
    if (!modalEl || !window.bootstrap) {
      document.getElementById(formId).submit();
      return;
    }
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const yesBtn = document.getElementById('confirmYes');
    yesBtn.onclick = () => document.getElementById(formId).submit();
    modal.show();
  }
</script>
<script>
  async function postJson(url, payload){
    const res = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    const raw = await res.text();
    let data = null;
    try { data = JSON.parse(raw); } catch(e) {}

    return { res, data, raw };
  }

  const btn = document.getElementById('genBioBtn');
  const toneSel = document.getElementById('bioTone');
  const statusEl = document.getElementById('bioGenStatus');

  const bioTextarea = document.getElementById('bioFieldVisible'); // <-- your visible textarea id
  const previewWrap = document.getElementById('bioPreviewWrap');
  const previewBtn  = document.getElementById('bioPreviewBtn');

  function setLoading(isLoading){
    if (!btn) return;
    btn.disabled = isLoading;
    btn.textContent = isLoading ? 'Generating…' : 'Generate bio (AI)';
  }

  function showPreview(bio){
    previewBtn.textContent = bio;
    previewBtn.dataset.bio = bio;
    previewWrap.classList.remove('d-none');
  }

  btn?.addEventListener('click', async () => {
    statusEl.textContent = 'Generating a bio…';
    setLoading(true);
    previewWrap.classList.add('d-none');

    const { res, data, raw } = await postJson("{{ route('ai.bio') }}", {
      tone: toneSel?.value || 'fun'
    });

    setLoading(false);

    if (!res.ok || !data || data.ok !== true) {
      console.warn('AI bio failed', {status: res.status, data, raw});
      statusEl.textContent = data?.error ? data.error : `Failed (${res.status})`;
      return;
    }

    const bio = (data.bio || '').toString().trim();
    if (!bio) {
      statusEl.textContent = 'AI returned an empty bio. Try again.';
      return;
    }

    statusEl.textContent = 'Here’s a suggestion — tap it to use it.';
    showPreview(bio);
  });

  // Click preview card to apply
  previewBtn?.addEventListener('click', () => {
    const bio = previewBtn.dataset.bio || '';
    if (!bioTextarea) return;

    bioTextarea.value = bio;
    bioTextarea.dispatchEvent(new Event('input', { bubbles: true }));
    bioTextarea.dispatchEvent(new Event('change', { bubbles: true }));

    statusEl.textContent = 'Bio applied. You can still edit it.';
    bioTextarea.focus();
    bioTextarea.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // OPTIONAL: if you have an edit panel that needs opening, call it here.
    // e.g. openBioEditor();
  });
</script>
@endsection