@extends('layouts.app')
@section('title','Listings')

@section('content')
<style>
  .shell{display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;}
  .topbar{background:#fff;border:1px solid rgba(0,0,0,.1);border-radius:12px;padding:14px 16px;display:flex;gap:12px;align-items:center;margin-bottom:14px;}
  .card-list{max-height:calc(100vh - 180px);overflow-y:auto;padding-right:8px;}
  .filter-panel{position:sticky;top:16px;background:#fff;border:1px solid rgba(0,0,0,.1);border-radius:12px;padding:16px;}
  .listing-card{background:#fff;border:2px solid rgba(0,0,0,.15);border-radius:24px;padding:18px;margin-bottom:18px;}
  .inner{display:grid;grid-template-columns:280px 1fr;gap:18px;align-items:start;}
  .photo{width:100%;height:260px;border-radius:26px;background:#f1f3f5;border:1px solid rgba(0,0,0,.12);display:flex;align-items:center;justify-content:center;overflow:hidden;color:rgba(0,0,0,.45);font-weight:600;}
  .btn-pill{border-radius:10px;border:1px solid rgba(0,0,0,.25);}
  .score-box{display:inline-block;padding:6px 14px;border:2px solid rgba(0,0,0,.25);border-radius:8px;font-weight:800;font-size:22px;background:#fff;}
  @media(max-width:1200px){.shell{grid-template-columns:1fr}.filter-panel{position:static}}
  .your-listing{
  background: rgba(13,110,253,.06);
  border-color: rgba(13,110,253,.25) !important;
  .carousel-indicators [data-bs-target]{
  width: 10px; height: 10px; border-radius: 999px;
  border: 1px solid rgba(0,0,0,.35);
  background-color: #cfcfcf;
  opacity: 1;
}
.carousel-indicators .active{ background-color:#fff; }
}
</style>

<div class="shell">
  <div>
    <div class="topbar">
      <h2 class="fw-bold mb-0">Property Listings</h2>

        <button type="button" class="btn btn-outline-secondary ms-2" data-bs-toggle="modal" data-bs-target="#listTermsModal">
            List your Own (+)
        </button>

      <div class="ms-auto d-flex align-items-center gap-2">
        <div class="fw-semibold">Sorted By :</div>
        <form method="GET" action="{{ route('listings') }}">
          <input type="hidden" name="city" value="{{ $city }}">
          <input type="hidden" name="min" value="{{ $min }}">
          <input type="hidden" name="max" value="{{ $max }}">
          <select class="form-select" name="sort" onchange="this.form.submit()" style="max-width:200px;">
            <option value="price" @selected($sort==='price')>Price</option>
            <option value="city" @selected($sort==='city')>City</option>
            <option value="updated" @selected($sort==='updated')>Recently updated</option>
          </select>
        </form>
      </div>
    </div>
    @if(session('own_listing_notice'))
    <div class="bg-white border rounded-4 p-4 text-center mb-3" style="border-color: rgba(0,0,0,.12) !important;">
        <div style="font-size:56px; line-height:1;">😄</div>
        <div class="fw-bold mt-2">That’s your own listing!</div>
        <div class="text-muted">No need to enquire — you’re the owner.</div>
    </div>
    @endif

    @if(session('listing_deleted_notice'))
    <div class="alert alert-danger text-center">
        <div style="font-size:40px;line-height:1;">🚫</div>
        <div class="fw-bold mt-2">That listing was removed.</div>
        <div class="text-muted">You can’t enquire because it no longer exists.</div>
    </div>
    @endif

    <div class="card-list">
      @forelse($listings as $l)
      @php $isMine = ((int)$l->user_id === (int)auth()->user()->user_id); @endphp
        <div class="listing-card {{ $isMine ? 'your-listing' : '' }}" data-listing-id="{{ $l->listing_id }}">
            @if($isMine)
            <div class="mb-2 fw-bold">Your Listing⭐</div>
        @endif
          <div class="inner">
            <div>
              @php
                $photos = $photosByListing[$l->listing_id] ?? collect();
                $carouselId = 'listingCarousel'.$l->listing_id;
                @endphp

                <div class="photo p-0" style="background:transparent;border:none;">
                <div id="{{ $carouselId }}" class="carousel slide" data-bs-ride="false" style="width:100%;height:260px;border-radius:26px;overflow:hidden;border:1px solid rgba(0,0,0,.12);background:#f1f3f5;">
                    <div class="carousel-indicators">
                    @php $count = max(1, $photos->count()); @endphp
                    @for($i=0; $i<$count; $i++)
                        <button type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide-to="{{ $i }}"
                        class="{{ $i===0 ? 'active' : '' }}" aria-current="{{ $i===0 ? 'true' : 'false' }}"></button>
                    @endfor
                    </div>

                    <div class="carousel-inner h-100">
                    @if($photos->count() === 0)
                        <div class="carousel-item active h-100">
                        <div class="h-100 d-flex align-items-center justify-content-center text-muted fw-semibold">
                            Photo
                        </div>
                        </div>
                    @else
                        @foreach($photos as $idx => $ph)
                        <div class="carousel-item {{ $idx===0 ? 'active' : '' }} h-100">
                            <img src="{{ url($ph->photo_url) }}" class="d-block w-100 h-100" style="object-fit:cover;" alt="Photo">
                        </div>
                        @endforeach
                    @endif
                    </div>

                    @if($photos->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#{{ $carouselId }}" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                    @endif
                </div>
                </div>

              <form method="POST" action="{{ route('listings.enquire',['listing_id'=>$l->listing_id]) }}" class="mt-3">
                @csrf
                <button class="btn btn-secondary w-100 btn-pill" type="submit">Enquire</button>
              </form>
            </div>

            <div>
              <div class="h2 fw-bold mb-1">{{ $l->property_name }}</div>
              <div class="d-flex align-items-center gap-2 text-muted mb-2">
                <span>👤</span> <span class="fw-semibold">{{ $l->owner_name }}</span>
              </div>

              <div class="text-muted mb-1"><span class="fw-bold text-dark">Location:</span> {{ $l->city }}</div>
              <div class="text-muted mb-2"><span class="fw-bold text-dark">Rent:</span> £{{ $l->rent_pcm }}/mo</div>

              <div class="text-muted" style="max-width:520px;">
                {{ \Illuminate\Support\Str::limit($l->description ?? '', 120) }}
              </div>
              <div class="mt-3 d-none js-more">
            <div class="text-muted"><span class="fw-bold text-dark">Type:</span> {{ $l->property_type }}</div>
            <div class="text-muted"><span class="fw-bold text-dark">Max occupants:</span> {{ $l->max_occupants }}</div>
            <div class="text-muted"><span class="fw-bold text-dark">Bathrooms shared:</span> {{ $l->bathrooms_shared ? 'Yes' : 'No' }}</div>
            <div class="text-muted"><span class="fw-bold text-dark">Available from:</span> {{ $l->available_from ?? '—' }}</div>

            <hr class="my-2">

            <div class="fw-bold">Full description</div>
            <div class="text-muted">{{ $l->description ?? '—' }}</div>

            <hr class="my-2">

            <div class="text-muted"><span class="fw-bold text-dark">Contact:</span>
                {{ $l->contact_email ?? '' }}
                @if($l->contact_email && $l->contact_phone) · @endif
                {{ $l->contact_phone ?? '' }}
                @if(!$l->contact_email && !$l->contact_phone) — @endif
            </div>
            </div>

              <div class="mt-3">
                <button type="button" class="btn btn-outline-secondary w-100 btn-pill js-toggle-more">
                Show More ……
                </button>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="bg-white border rounded-4 p-5 text-center" style="border-color: rgba(0,0,0,.12) !important;">
          <div style="font-size:64px;line-height:1;">🏠</div>
          <h3 class="fw-bold mt-3">No listings yet</h3>
          <p class="text-muted mb-0">Try adjusting filters, or create the first listing.</p>
        </div>
      @endforelse
    </div>
  </div>

  <aside class="filter-panel">
    <div class="h4 mb-3 fw-bold">Filter</div>

    <form method="GET" action="{{ route('listings') }}" class="d-grid gap-3">
      <input type="hidden" name="sort" value="{{ $sort }}">

      <div>
        <label class="form-label fw-semibold">Current City</label>
        <select class="form-select" name="city">
          <option value="">Any</option>
          @foreach($cities as $c)
            <option value="{{ $c }}" @selected($city===$c)>{{ $c }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label class="form-label fw-semibold">Price Range</label>
        <div class="d-flex align-items-center gap-2">
          <input type="number" class="form-control" name="min" value="{{ $min ?? 200 }}">
          <span class="fw-semibold">—</span>
          <input type="number" class="form-control" name="max" value="{{ $max ?? 800 }}">
        </div>
      </div>

      <div>
        <label class="form-label fw-semibold">Tags</label>
        <div class="text-muted small">(We’ll wire tags later.)</div>
      </div>

      <button class="btn btn-primary" type="submit">Apply &amp; Search</button>
      <a class="btn btn-outline-secondary" href="{{ route('listings') }}">reset</a>
    </form>
  </aside>
</div>

<!-- List your own: Terms modal -->
<div class="modal fade" id="listTermsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-body p-4">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="fw-bold fs-4 mb-1">List your own property</div>
            <div class="text-muted">
              You can post a small listing as a student/tenant — no business account needed.
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <hr class="my-3">

        <div class="mb-3">
          <div class="fw-semibold mb-2">Before you continue:</div>
          <ul class="list-unstyled d-grid gap-2 mb-0">
            <li class="d-flex gap-2">
              <span style="font-size:18px;">✅</span>
              <span>Only post properties you’re genuinely offering (no spam, no fake rooms).</span>
            </li>
            <li class="d-flex gap-2">
              <span style="font-size:18px;">🔒</span>
              <span>Don’t share sensitive details publicly (exact address, bank info, etc.).</span>
            </li>
            <li class="d-flex gap-2">
              <span style="font-size:18px;">📷</span>
              <span>Use real photos. You can upload multiple property photos after creating the listing.</span>
            </li>
            <li class="d-flex gap-2">
              <span style="font-size:18px;">📩</span>
              <span>Enquiries happen through in-app messaging. Provide at least one contact method (email or phone).</span>
            </li>
          </ul>
        </div>

        <div class="form-check mt-3">
          <input class="form-check-input" type="checkbox" id="agreeTerms">
          <label class="form-check-label" for="agreeTerms">
            I agree to the terms and conditions.
          </label>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <a id="confirmListBtn" class="btn btn-primary disabled" aria-disabled="true" href="{{ route('listings.create') }}">
            Confirm
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  (function(){
    const agree = document.getElementById('agreeTerms');
    const btn = document.getElementById('confirmListBtn');
    if(!agree || !btn) return;

    agree.addEventListener('change', () => {
      if (agree.checked) {
        btn.classList.remove('disabled');
        btn.setAttribute('aria-disabled', 'false');
      } else {
        btn.classList.add('disabled');
        btn.setAttribute('aria-disabled', 'true');
      }
    });

    // Prevent clicking when disabled
    btn.addEventListener('click', (e) => {
      if (btn.classList.contains('disabled')) e.preventDefault();
    });
  })();
</script>
<script>
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-toggle-more');
    if (!btn) return;

    const card = btn.closest('.listing-card');
    const more = card?.querySelector('.js-more');
    if (!more) return;

    more.classList.toggle('d-none');
    btn.textContent = more.classList.contains('d-none') ? 'Show More ……' : 'Show Less';
  });
</script>
<script>
  (function(){
    const params = new URLSearchParams(window.location.search);
    const openId = params.get('open');
    if (!openId) return;

    const card = document.querySelector(`.listing-card[data-listing-id="${openId}"]`);
    if (!card) return;

    const more = card.querySelector('.js-more');
    const btn = card.querySelector('.js-toggle-more');
    if (more) more.classList.remove('d-none');
    if (btn) btn.textContent = 'Show Less';

    // Scroll into view nicely
    card.scrollIntoView({ behavior: 'smooth', block: 'start' });
  })();
</script>
@endsection