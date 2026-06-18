@extends('layouts.app')
@section('title', 'Inbox')

@section('content')
<style>
  .inbox-shell{
    display:grid;
    grid-template-columns: 86px 260px 1fr;
    gap: 0;
    height: calc(100vh - 40px);
    background:#fff;
    border: 1px solid rgba(0,0,0,.1);
    border-radius: 12px;
    overflow:hidden;
  }

  /* avatar strip (grey blocks) */
  .avatar-strip{
    background:#bdbdbd;
    overflow-y:auto;
  }
  .avatar-tile{
    height: 110px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-bottom: 1px solid rgba(0,0,0,.12);
  }
  .avatar-circle{
    width: 56px; height:56px; border-radius:999px;
    background:#fff; display:flex; align-items:center; justify-content:center;
    border: 2px solid rgba(0,0,0,.2);
    font-size: 24px;
  }
  .avatar-tile.active{ background: rgba(255,255,255,.35); }

  /* thread list */
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

  /* chat area */
  .chat{
    display:flex;
    flex-direction:column;
    height:100%;
  }
  .chat-top{
    padding: 14px 16px;
    border-bottom: 1px solid rgba(0,0,0,.12);
    display:flex;
    align-items:center;
    gap: 12px;
  }
  .chat-name{ font-weight:800; font-size: 22px; }
  .chat-actions{ margin-left:auto; display:flex; gap: 10px; }
  .chat-body{
    flex:1;
    padding: 18px;
    overflow-y:auto;
    background: #fff;
  }
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
  .share-btn{
    border-radius: 12px;
    padding: 12px 14px;
    background:#2f2f2f;
    color:#fff;
    border:none;
    width: 170px;
    font-weight: 600;
  }
  .send-btn{
    width: 56px;
    height: 56px;
    border-radius: 12px;
    border: 1px solid rgba(0,0,0,.2);
    background:#fff;
    font-size: 22px;
  }
  .inbox-shell{
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 16px;
  box-shadow: var(--shadow);
  }
  .thread-list{
    border-right: 1px solid var(--border);
  }
  .thread-item.active{
    background: var(--accent-soft);
  }
  .chat-top{
    border-bottom: 1px solid var(--border);
  }
  .chat-bottom{
    border-top: 1px solid var(--border);
  }
  .avatar-strip{
  background: var(--sidebar);
  border-right: 1px solid var(--border);
  overflow-y:auto;
}
.avatar-tile{
  height: 110px;
  display:flex;
  align-items:center;
  justify-content:center;
  border-bottom: 1px solid var(--border);
  text-decoration:none;
}
.avatar-circle{
  width: 56px; height:56px; border-radius:999px;
  background: #fff;
  display:flex; align-items:center; justify-content:center;
  border: 1px solid var(--border);
  font-size: 24px;
}
.avatar-tile.active{
  background: var(--accent-soft);
}
.msg-row.me .bubble{
  background: var(--accent);
  color: var(--text);
  border-color: rgba(242,204,15,.55);
  font-weight: 800;
}
.bubble{
  background:#fff;
  border: 1px solid var(--border);
}
.send-btn{
  border: 1px solid var(--border);
  background: #fff;
}
.send-btn:hover{ background: var(--accent-soft); }
.share-btn{
  border-radius: 12px;
  padding: 12px 14px;
  background: var(--text);
  color: #fff;
  border: 1px solid rgba(17,17,17,.25);
  width: 170px;
  font-weight: 900;
}
.share-btn:hover{
  background: #111;
  border-color: rgba(242,204,15,.55);
  color: var(--accent);
}
.btn-danger-soft{
  background: rgba(220,53,69,.16) !important;
  border: 1px solid rgba(220,53,69,.35) !important;
  color: #b02a37 !important;
  font-weight: 900;
  border-radius: 12px;
}
.btn-danger-soft:hover{
  background: #fff !important;
  border-color: rgba(220,53,69,.50) !important;
  color: #b02a37 !important;
}
</style>

@php
  $me = auth()->user()->user_id;
  $activeId = $activeThread->inbox_id ?? null;
@endphp

<div class="inbox-shell">

  {{-- tabs column --}}
    <div class="avatar-strip d-flex flex-column">
      @php $tab = request('tab', 'people'); @endphp

      <a class="avatar-tile {{ $tab==='people' ? 'active' : '' }}"
        href="{{ route('inbox') }}?tab=people">
        <div class="avatar-circle">👤</div>
      </a>

      <a class="avatar-tile {{ $tab==='listings' ? 'active' : '' }}"
        href="{{ route('inbox') }}?tab=listings">
        <div class="avatar-circle">🏠</div>
      </a>
    </div>
    @php
      $tab = request('tab', 'people');
      $threadsFiltered = $threads->filter(fn($t) => $tab === 'people' ? $t->type === 'MATCH' : $t->type === 'LISTING');
    @endphp
  {{-- thread list (names) --}}
  <div class="thread-list">
    @foreach($threadsFiltered as $t)
      @php
        $otherName = \Illuminate\Support\Facades\DB::table('public_profile')
          ->where('user_id',$t->other_user_id)
          ->value('display_name') ?? ('User '.$t->other_user_id);

        $title = ($t->type === 'LISTING')
          ? (($t->listing_title ?? 'Listing Enquiry') . ' · ' . $otherName)
          : $otherName;
      @endphp

    <a class="thread-item {{ $activeId === $t->inbox_id ? 'active' : '' }}"
      href="{{ route('inbox.show', ['inbox_id' => $t->inbox_id]) }}?tab={{ $tab }}">
      <div class="fw-semibold">{{ $title }}</div>
      <div class="small text-muted">{{ $t->type === 'LISTING' ? 'Listing enquiry' : 'Match chat' }}</div>
    </a>
      @endforeach
  </div>

  {{-- chat area --}}
  <div class="chat">
    <div class="chat-top">
      <div class="avatar-circle">👤</div>

      <div>
        <div class="chat-name">{{ $activeUser->display_name ?? 'Select a chat' }}</div>
      </div>

     @if($activeThread)
      <div class="chat-actions">
        @php
          $otherId = ($activeThread->user1_id == $me) ? $activeThread->user2_id : $activeThread->user1_id;
        @endphp

        @if($activeThread->type === 'MATCH')
          <a class="btn btn-wanted"
            href="{{ route('profiles.show', ['user_id' => $otherId]) }}?from=inbox">
            View Profile
          </a>
        @else
          <a class="btn btn-wanted"
            href="{{ route('listings') }}?open={{ $activeThread->listing_id }}">
            View Property
          </a>
        @endif

        @if($activeThread->type === 'MATCH')
          <form method="POST"
                action="{{ route('inbox.unmatch', ['inbox_id' => $activeThread->inbox_id]) }}"
                onsubmit="return confirm('Unmatch this user? This will remove the match and delete the chat history.');">
            @csrf
            <button class="btn btn-danger-soft" type="submit">Unmatch</button>
          </form>
        @endif
      </div>
    @endif
    </div>

    <div class="chat-body">
      @if(!$activeThread)
        <div class="text-muted">No conversations yet.</div>
      @else
      @if(($activeThread->type ?? '') === 'LISTING' && (!empty($listingDeleted) || session('listing_deleted')))
        <div class="alert alert-danger">
          <div style="font-size:32px;line-height:1;">🚫</div>
          <div class="fw-bold mt-2">This property no longer exists.</div>
          <div>The owner has removed this listing. This enquiry is now read-only.</div>
        </div>
      @endif
        @foreach($messages as $m)
          <div class="msg-row {{ (int)$m->sender_user_id === (int)$me ? 'me' : '' }}">
            <div class="bubble">
              {{ $m->content }}
            </div>
          </div>
        @endforeach
      @endif
    </div>

  @if($activeThread)
      @php
        $isReadOnlyListing = (($activeThread->type ?? '') === 'LISTING') && (!empty($listingDeleted));
        $tabQ = request('tab', 'people');

        $mode = ($iShared ?? false) ? 'stop' : 'share';
        $label = ($iShared ?? false) ? 'Stop Sharing' : 'Share';
      @endphp

      @if($isReadOnlyListing)
        <div class="p-3 text-muted small border-top">
          Messaging is disabled because the listing was deleted.
        </div>
      @else
        <div class="chat-bottom">

          @if($activeThread->type === 'MATCH')
            <form id="shareToggleForm" method="POST"
                  action="{{ route('inbox.sharePrivate', ['inbox_id' => $activeThread->inbox_id]) }}">
              @csrf
              <button class="share-btn" type="button"
                      onclick="confirmShareToggle('shareToggleForm', '{{ $mode }}')">
                {{ $label }}<br>Private Profile
              </button>
            </form>
          @endif

          <form class="flex-grow-1 d-flex gap-2"
                method="POST"
                action="{{ route('inbox.send', ['inbox_id' => $activeThread->inbox_id]) }}?tab={{ $tabQ }}">
            @csrf
            <input class="form-control form-control-lg" name="content"
                  placeholder="Type a Message...." maxlength="1000" required>
            <button class="send-btn" type="submit">✈</button>
          </form>

        </div>
      @endif
@endif
  </div>
</div>
@endsection