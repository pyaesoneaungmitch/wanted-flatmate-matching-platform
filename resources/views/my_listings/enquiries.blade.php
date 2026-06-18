@extends('layouts.app')
@section('title','Enquiries')

@section('content')
<style>
  .shell{
    display:grid;
    grid-template-columns: 260px 1fr;
    gap: 0;
    height: calc(100vh - 40px);
    background:#fff;
    border: 1px solid rgba(0,0,0,.1);
    border-radius: 12px;
    overflow:hidden;
  }
  .thread-list{
    border-right: 1px solid rgba(0,0,0,.12);
    overflow-y:auto;
  }
  .thread-item{
    padding: 14px 14px;
    border-bottom: 1px solid rgba(0,0,0,.08);
    text-decoration:none;
    display:block;
    color:inherit;
  }
  .thread-item.active{ background: rgba(13,110,253,.08); }

  .chat{ display:flex; flex-direction:column; height:100%; }
  .chat-top{
    padding: 14px 16px;
    border-bottom: 1px solid rgba(0,0,0,.12);
    display:flex;
    align-items:center;
    gap: 12px;
  }
  .chat-name{ font-weight:800; font-size: 20px; }
  .chat-actions{ margin-left:auto; display:flex; gap:10px; align-items:center; }
  .chat-body{ flex:1; padding:18px; overflow-y:auto; }
  .msg-row{ display:flex; margin-bottom: 12px; }
  .msg-row.me{ justify-content:flex-end; }
  .bubble{
    max-width: 60%;
    border: 1px solid rgba(0,0,0,.2);
    border-radius: 10px;
    padding: 10px 12px;
    font-size: 14px;
    background:#f8f9fa;
  }
  .msg-row.me .bubble{ background:#bdbdbd; color:#fff; }
  .chat-bottom{
    border-top: 1px solid rgba(0,0,0,.12);
    padding: 12px;
    display:flex;
    gap: 12px;
    align-items:center;
  }
  .send-btn{
    width: 56px; height: 56px;
    border-radius: 12px;
    border: 1px solid rgba(0,0,0,.2);
    background:#fff;
    font-size: 22px;
  }
</style>

@php
  $me = auth()->user()->user_id;
  $activeId = $activeThread->inbox_id ?? null;
@endphp

<div class="mb-3 d-flex align-items-center justify-content-between">
  <div>
    <h2 class="fw-bold mb-0">Enquiries</h2>
    <div class="text-muted">{{ $listing->property_name }}</div>
  </div>
  <a class="btn btn-outline-secondary" href="{{ route('my.listings') }}">Back to dashboard</a>
</div>

<div class="shell">

  {{-- left: enquiries list --}}
  <div class="thread-list">
    @foreach($threads as $t)
      @php
        $name = \Illuminate\Support\Facades\DB::table('public_profile')
          ->where('user_id',$t->other_user_id)
          ->value('display_name') ?? ('User '.$t->other_user_id);
      @endphp

      <a class="thread-item {{ $activeId === $t->inbox_id ? 'active' : '' }}"
         href="{{ route('my.listings.enquiries.show', ['listing_id'=>$listing->listing_id, 'inbox_id'=>$t->inbox_id]) }}">
        <div class="fw-semibold">{{ $name }}</div>
        <div class="small text-muted">Enquiry thread</div>
      </a>
    @endforeach

    @if($threads->count() === 0)
      <div class="p-3 text-muted">No enquiries yet.</div>
    @endif
  </div>

  {{-- right: chat --}}
  <div class="chat">
    <div class="chat-top">
      <div class="fw-bold">🏠</div>

      <div>
        <div class="chat-name">{{ $activeUser->display_name ?? 'Select an enquiry' }}</div>
        <div class="text-muted small">{{ $listing->property_name }}</div>
      </div>

      @if($activeThread)
        <div class="chat-actions">
          <a class="btn btn-secondary"
             href="{{ route('profiles.show', ['user_id' => $activeUser->user_id ?? 0]) }}?from=inbox">
            View Profile
          </a>
        </div>
      @endif
    </div>

    <div class="chat-body">
      @if(!$activeThread)
        <div class="text-muted">Pick a thread on the left to view messages.</div>
      @else
        @foreach($messages as $m)
          <div class="msg-row {{ (int)$m->sender_user_id === (int)$me ? 'me' : '' }}">
            <div class="bubble">{{ $m->content }}</div>
          </div>
        @endforeach
      @endif
    </div>

    @if($activeThread)
      <div class="chat-bottom">
        <form class="flex-grow-1 d-flex gap-2"
              method="POST"
              action="{{ route('my.listings.enquiries.send', ['listing_id'=>$listing->listing_id, 'inbox_id'=>$activeThread->inbox_id]) }}">
          @csrf
          <input class="form-control form-control-lg" name="content" placeholder="Reply…" maxlength="1000" required>
          <button class="send-btn" type="submit">✈</button>
        </form>
      </div>
    @endif
  </div>

</div>
@endsection