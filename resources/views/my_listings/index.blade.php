@extends('layouts.app')
@section('title','Your Listings')

@section('content')
<style>
  .dash-wrap{max-width:1100px;margin:0 auto;}
  .list-card{
    background:#fff;border:1px solid rgba(0,0,0,.12);
    border-radius:18px;padding:14px;margin-bottom:14px;
    display:flex;gap:14px;align-items:center;
    transition: transform .12s ease, box-shadow .12s ease;
  }
  .list-card:hover{ transform: translateY(-2px); box-shadow: 0 10px 22px rgba(0,0,0,.08); }
  .thumb{
    width:120px;height:88px;border-radius:14px;overflow:hidden;
    background:#f1f3f5;border:1px solid rgba(0,0,0,.12);
    display:flex;align-items:center;justify-content:center;
    color:rgba(0,0,0,.45);font-weight:700;
  }
  .actions{margin-left:auto;display:flex;gap:10px;align-items:center;}
  .iconbtn{
    width:44px;height:44px;border-radius:12px;
    border:1px solid rgba(0,0,0,.18);background:#fff;
    display:flex;align-items:center;justify-content:center;
    font-size:18px; position:relative;
  }
  .badge-dot{
    position:absolute; top:-6px; right:-6px;
    background:#0d6efd;color:#fff;
    border-radius:999px;
    padding:2px 7px;
    font-size:12px;
    border:2px solid #fff;
  }
</style>

<div class="dash-wrap">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h2 class="fw-bold mb-0">Your Listings</h2>
      <div class="text-muted">Manage your properties, enquiries, and updates.</div>
    </div>
    </div>

  @if(session('deleted'))
    <div class="alert alert-danger">
      <strong>Deleted.</strong> Your property is no longer visible to others.
    </div>
  @endif

  @if(session('saved'))
    <div class="alert alert-success">Changes saved.</div>
  @endif

  @forelse($cards as $c)
    <div class="list-card">
      <div class="thumb">
        @if($c['photo_url'])
          <img src="{{ url($c['photo_url']) }}" style="width:100%;height:100%;object-fit:cover;" alt="Photo">
        @else
          Photo
        @endif
      </div>

      <div>
        <div class="fw-bold fs-5">{{ $c['property_name'] }}</div>
        <div class="text-muted small">Last updated: {{ $c['updated_at'] }}</div>
        <div class="text-muted small">Your Listing</div>
      </div>

      <div class="actions">
        <a class="iconbtn" href="{{ route('my.listings.enquiries', ['listing_id'=>$c['listing_id']]) }}" title="Enquiries">
          💬
          @if($c['enquiry_count'] > 0)
            <span class="badge-dot">{{ $c['enquiry_count'] }}</span>
          @endif
        </a>

        <a class="iconbtn" href="{{ route('my.listings.edit', ['listing_id'=>$c['listing_id']]) }}" title="Edit">
          ✎
        </a>

        <form method="POST"
            action="{{ route('my.listings.delete', ['listing_id' => $c['listing_id']]) }}"
            onsubmit="return confirm('Delete this listing? It will disappear from Listings and enquiry chats become read-only.');"
            style="margin:0;">
        @csrf
        <button class="iconbtn" type="submit" title="Delete" style="border-color: rgba(220,53,69,.4);">
            🗑
        </button>
        </form>
      </div>
    </div>
  @empty
    <div class="bg-white border rounded-4 p-5 text-center" style="border-color: rgba(0,0,0,.12) !important;">
      <div style="font-size:64px;line-height:1;">🏠</div>
      <h3 class="fw-bold mt-3">No listings yet</h3>
      <p class="text-muted mb-3">Create your first listing to start receiving enquiries.</p>
      <a class="btn btn-primary" href="{{ route('listings.create') }}">Create Listing</a>
    </div>
  @endforelse
</div>



<script>
  function confirmDelete(listingId, name){
    const modalEl = document.getElementById('deleteModal');
    const textEl = document.getElementById('deleteText');
    const form = document.getElementById('deleteForm');

    const msg = `You're about to delete "${name}". It will disappear from listings and enquiries will show as removed. Continue?`;
    textEl.textContent = msg;

    // set the POST target
    form.action = `{{ url('/my-listings') }}/${listingId}/delete`;

    // If Bootstrap modal is available, use it. Otherwise fallback to native confirm()
    const hasBootstrapModal = (window.bootstrap && window.bootstrap.Modal && typeof window.bootstrap.Modal.getOrCreateInstance === 'function');

    if (hasBootstrapModal) {
      const modal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
      return;
    }

    // Fallback: native confirm dialog
    if (window.confirm(msg)) {
      form.submit();
    }
  }
</script>
@endsection