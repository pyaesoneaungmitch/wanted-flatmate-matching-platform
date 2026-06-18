@extends('layouts.app')
@section('title','Edit Listing')

@section('content')
<style>
  .wrap{max-width:980px;margin:0 auto;}
  .cardish{background:#fff;border:1px solid rgba(0,0,0,.12);border-radius:16px;padding:18px;}
  .gallery-grid{ display:grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
  .g-tile{
    height: 150px; border-radius: 18px; background:#f1f3f5;
    border:1px solid rgba(0,0,0,.12);
    display:flex; align-items:center; justify-content:center;
    position:relative; overflow:hidden;
  }
  .g-plus{ font-size: 40px; font-weight: 800; color: rgba(0,0,0,.45); }
</style>

<div class="wrap">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h2 class="fw-bold mb-0">Edit Listing</h2>
      <div class="text-muted">Update your listing details and manage photos.</div>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('my.listings') }}">Back</a>
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

  <div class="cardish mb-3">
    <form method="POST" action="{{ route('my.listings.update', ['listing_id' => $listing->listing_id]) }}">
      @csrf

      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label fw-semibold">Property name</label>
          <input class="form-control" name="property_name" maxlength="80" required
                 value="{{ old('property_name', $listing->property_name) }}">
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Max occupants</label>
          <input type="number" class="form-control" name="max_occupants" min="1" max="255" required
                 value="{{ old('max_occupants', $listing->max_occupants) }}">
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Rent (PCM)</label>
          <input type="number" class="form-control" name="rent_pcm" min="0" max="10000" required
                 value="{{ old('rent_pcm', $listing->rent_pcm) }}">
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">City</label>
          <input class="form-control" name="city" maxlength="60" required
                 value="{{ old('city', $listing->city) }}">
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Property type</label>
          @php $pt = old('property_type', $listing->property_type); @endphp
          <select class="form-select" name="property_type" required>
            <option value="ROOM" @selected($pt==='ROOM')>Room</option>
            <option value="STUDIO" @selected($pt==='STUDIO')>Studio</option>
            <option value="FLAT" @selected($pt==='FLAT')>Flat</option>
            <option value="HOUSE" @selected($pt==='HOUSE')>House</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Bathrooms shared?</label>
          @php $bs = (string)old('bathrooms_shared', $listing->bathrooms_shared); @endphp
          <select class="form-select" name="bathrooms_shared" required>
            <option value="1" @selected($bs==='1')>Yes</option>
            <option value="0" @selected($bs==='0')>No</option>
          </select>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-semibold">Available from</label>
          <input type="date" class="form-control" name="available_from"
                 value="{{ old('available_from', $listing->available_from ?? '') }}">
        </div>

        <div class="col-12">
          <label class="form-label fw-semibold">Description</label>
          <textarea class="form-control" name="description" rows="4" maxlength="800"
                    placeholder="Add a short description (room details, bills included, vibe, etc.)">{{ old('description', $listing->description ?? '') }}</textarea>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Contact email (optional)</label>
          <input type="email" class="form-control" name="contact_email" maxlength="255"
                 value="{{ old('contact_email', $listing->contact_email ?? '') }}">
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold">Contact phone (optional)</label>
          <input class="form-control" name="contact_phone" maxlength="30"
                 value="{{ old('contact_phone', $listing->contact_phone ?? '') }}">
          <div class="form-text">Provide at least one contact method (email or phone).</div>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button class="btn btn-primary" type="submit">Save Changes</button>
        <a class="btn btn-outline-secondary" href="{{ route('my.listings') }}">Cancel</a>
      </div>
    </form>
  </div>

  <div class="cardish">
    <div class="fw-bold mb-2">Property photos</div>
    <div class="text-muted mb-3">Upload up to 6 photos.</div>

    <div class="gallery-grid">
      @foreach($propertyPhotos as $ph)
        <div class="g-tile">
          <img src="{{ url($ph->photo_url) }}" style="width:100%;height:100%;object-fit:cover;" alt="Photo">

          <form method="POST"
                action="{{ route('listings.photos.delete', ['photo_id' => $ph->photo_id]) }}"
                style="position:absolute; top:8px; right:8px;">
            @csrf
            <button class="btn btn-sm btn-light" type="submit" title="Delete">🗑</button>
          </form>
        </div>
      @endforeach

      @for($i = $propertyPhotos->count(); $i < 6; $i++)
        <div class="g-tile">
          <form method="POST"
                action="{{ route('listings.photos.upload', ['listing_id' => $listing->listing_id]) }}"
                enctype="multipart/form-data" style="text-align:center;">
            @csrf
            <input type="file" name="photo" accept="image/*" required class="form-control form-control-sm">
            <button class="btn btn-outline-secondary btn-sm mt-2" type="submit">Upload</button>
            <div class="g-plus mt-2">+</div>
          </form>
        </div>
      @endfor
    </div>
  </div>
</div>
@endsection