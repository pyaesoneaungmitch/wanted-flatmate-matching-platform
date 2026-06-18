<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Wanted')</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
 <style>
  .app-shell { min-height: 100vh; }

  .sidebar {
    width: 260px;
    position: sticky;
    top: 0;
    height: 100vh;
    border-right: 1px solid rgba(0,0,0,.1);
    background: #fff;
    transition: width .2s ease;
    overflow-x: hidden;
  }

  .sidebar.collapsed { width: 84px; }

  .sidebar .label { transition: opacity .15s ease; }
  .sidebar.collapsed .label { opacity: 0; pointer-events: none; }

  .sidebar .nav-link { white-space: nowrap; }
  .sidebar .nav-link.active {
    background: #F2CC0F;   
    border-radius: .5rem;
    font-weight: 600;
  }

  .collapse-btn {
    border: 1px solid rgba(0,0,0,.1);
    background: #fff;
  }
:root{
    /* Reference-style palette */
    --bg: #fcdd54;               /* page background (warm off-white) */
    --panel: #FFFFFF;            /* cards/panels */
    --sidebar: #F3F1EA;          /* left sidebar background */
    --border: rgba(17,17,17,.10);
    --text: #111111;
    --muted: rgba(17,17,17,.60);
    --shadow: 0 10px 30px rgba(17,17,17,.08);

    /* Accent */
    --accent: #F2CC0F;           /* your yellow */
    --accent-soft: #fbdf55;
  }

  body{ background: var(--bg) !important; color: var(--text); }

  /* Generic panels */
  .wanted-panel{
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: var(--shadow);
  }

  /* Buttons */
  .btn-wanted{
    background: var(--accent) !important;
    border-color: var(--accent) !important;
    color: var(--text) !important;
    font-weight: 800;
    border-radius: 12px;
  }
  .btn-wanted:hover{ filter: brightness(.96); }

  .btn-wanted-outline{
    background: var(--panel) !important;
    border: 1px solid var(--border) !important;
    color: var(--text) !important;
    font-weight: 800;
    border-radius: 12px;
  }
  .btn-wanted-outline:hover{ background: var(--accent-soft) !important; }

  /* Inputs focus = yellow ring */
  .form-control:focus, .form-select:focus{
    border-color: var(--accent) !important;
    box-shadow: 0 0 0 .2rem rgba(242,204,15,.25) !important;
  }

  /* Chips */
  .chip{
    border: 1px solid var(--border);
    background: #fff;
    border-radius: 999px;
    padding: 6px 10px;
    font-weight: 700;
    font-size: 13px;
  }

  /* Subtle score badge like reference */
  .wanted-badge{
    background: var(--accent-soft);
    border: 1px solid rgba(242,204,15,.55);
    color: var(--text);
    border-radius: 12px;
    padding: 6px 12px;
    font-weight: 900;
  }
  <style>
  .wanted-sidebar{
    background: var(--sidebar);
    border-right: 1px solid var(--border);
    min-height: 100vh;
  }

  .wanted-sidebar .nav-link{
    color: var(--text);
    font-weight: 700;
    border-radius: 12px;
    padding: 10px 12px;
    margin: 4px 10px;
  }

  .wanted-sidebar .nav-link:hover{
    background: rgba(17,17,17,.05);
  }

  .wanted-sidebar .nav-link.active{
    background: #F2CC0F; ;
    border: 1px solid rgba(242,204,15,.45);
  }

  /* Optional: logo pill */
  .wanted-logo-pill{
    display:flex; align-items:center; gap:10px;
    padding: 14px 14px;
    font-weight: 900;
  }
  .wanted-logo-dot{
    width: 28px; height: 28px; border-radius: 10px;
    background: var(--accent);
    display:inline-flex; align-items:center; justify-content:center;
    font-weight: 900;
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
<style>
  /* Only affect the logo in the sidebar brand area */
  #sidebar .sidebar-brand{
    display:flex;
    justify-content:center;
    align-items:center;
    padding: 14px 10px 10px;
  }

  #sidebar .brand-link{
    display:flex;
    justify-content:center;
    align-items:center;
    width: 100%;
  }

  /* BULLETPROOF: cannot fill page */
  #sidebar img.brand-logo{
    display:block !important;
    height: 78px !important;     /* normal size */
    width: auto !important;
    max-width: 100% !important;
    max-height: 78px !important;
    object-fit: contain !important;
  }

  /* Collapsed (shrink by ~70%) */
  #sidebar.is-collapsed img.brand-logo{
    height: 24px !important;
    max-height: 24px !important;
  }

  #sidebar.is-collapsed .sidebar-brand{
    padding: 10px 6px;
  }
</style>
</style>
</head>
<body class="bg-light" bgcolor="#fcdd54">
@php
  $myListingCount = \Illuminate\Support\Facades\DB::table('listings')
    ->where('user_id', auth()->user()->user_id)
    ->whereNull('deleted_at')
    ->count();
@endphp
<div class="d-flex app-shell">
  <div class="wanted-sidebar">
  <aside class="sidebar d-flex flex-column p-3">
    <?php
  $displayName = null;
  if (auth()->check()) {
    $displayName = \Illuminate\Support\Facades\DB::table('public_profile')
      ->where('user_id', auth()->user()->user_id)
      ->value('display_name');
  }
?>
    {{-- Logo --}}
    <div class="sidebar-brand" id="sidebar-brand">
  <a href="{{ route('discover') }}" class="brand-link" aria-label="Home">
    <img src="{{ url('/storage/assets/logos/sidebarlogosmol.png')}}" style="
       display:block;
       height:150px;
       width:auto;">
  </a>
</div>
    <button id="sidebarToggle" type="button"
  class="btn btn-sm collapse-btn w-100 d-flex align-items-center justify-content-center gap-2 mb-3">
  <span class="fw-semibold">☰</span>
  <span class="label">Collapse</span>
</button>

    <hr class="mt-0">

    {{-- Nav --}}
    
    <nav class="nav nav-pills flex-column gap-1">
      <a class="nav-link {{ request()->routeIs('discover') ? 'active' : '' }}" href="{{ route('discover') }}">
  <span class="label">Discover</span>
</a>
<a class="nav-link {{ request()->routeIs('matches') ? 'active' : '' }}" href="{{ route('matches') }}">
  <span class="label">Matches</span>
</a>
<a class="nav-link {{ request()->is('inbox*') ? 'active' : '' }}"
   href="{{ route('inbox') }}">Inbox</a>
<a class="nav-link {{ request()->routeIs('listings') ? 'active' : '' }}" href="{{ route('listings') }}">
  <span class="label">Listings</span>
</a>
@if($myListingCount > 0)
  <a class="nav-link {{ request()->is('my-listings*') ? 'active' : '' }}"
   href="{{ route('my.listings') }}">
  Your Listings
</a>
@endif
      </nav>

    <div class="mt-auto">
      <hr>

      {{-- Bottom actions --}}
      <div class="mb-2 small text-muted label">
  Signed in as <span class="fw-semibold text-dark">{{ $displayName ?? auth()->user()->email }}</span>
</div>
      <a class="btn btn-outline-secondary w-100 mb-2 {{ request()->routeIs('profile') ? 'active' : '' }}"
   href="{{ route('profile') }}">
  <span class="label">Profile</span>
</a>

      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-outline-danger w-100" type="submit">
  <span class="label">Logout</span>
</button>
      </form>
    </div>
  </aside>
</div>
  {{-- Main content --}}
  <main class="flex-grow-1 p-4">
    @yield('content')
  </main>
  
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (function () {
    const sidebar = document.querySelector('.sidebar');
    const btn = document.getElementById('sidebarToggle');
    if (!sidebar || !btn) return;

    // restore state
    const saved = localStorage.getItem('wanted_sidebar_collapsed');
    if (saved === '1') sidebar.classList.add('collapsed');

    btn.addEventListener('click', () => {
      sidebar.classList.toggle('collapsed');
      localStorage.setItem('wanted_sidebar_collapsed', sidebar.classList.contains('collapsed') ? '1' : '0');
    });
  })();
</script>
<!-- Like confirmation modal -->
<div class="modal fade" id="likeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-body text-center py-4">
        <div id="likePop" style="font-size:54px; line-height:1;">💚</div>
        <div class="mt-2 fw-bold fs-4">Liked!</div>
        <div class="text-muted">Your like has been recorded.</div>
      </div>
    </div>
  </div>
</div>

<style>
  @keyframes pop {
    0% { transform: scale(.7); opacity: .6; }
    70% { transform: scale(1.15); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
  }
  .pop-anim { animation: pop .28s ease-out; }
</style>

<script>
  // Global helper you can call from any page
  window.showLikedPopup = function () {
    const el = document.getElementById('likePop');
    if (el) {
      el.classList.remove('pop-anim');
      void el.offsetWidth; // reflow to restart animation
      el.classList.add('pop-anim');
    }
    const modalEl = document.getElementById('likeModal');
    if (!modalEl || !window.bootstrap) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
    setTimeout(() => modal.hide(), 900);
  }
</script>
<!-- Pass confirmation modal -->
<div class="modal fade" id="passModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-body text-center py-4">
        <div id="passPop" style="font-size:54px; line-height:1;">👋</div>
        <div class="mt-2 fw-bold fs-4">Passed</div>
        <div class="text-muted">No worries — we won’t show them again (for now).</div>
      </div>
    </div>
  </div>
</div>

<script>
  window.showPassedPopup = function () {
    const el = document.getElementById('passPop');
    if (el) {
      el.classList.remove('pop-anim');
      void el.offsetWidth;
      el.classList.add('pop-anim');
    }
    const modalEl = document.getElementById('passModal');
    if (!modalEl || !window.bootstrap) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
    setTimeout(() => modal.hide(), 700);
  }
</script>
<!-- Confirm share/stop modal -->
<div class="modal fade" id="confirmShareModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-body p-4">
        <div class="fw-bold fs-5 mb-2" id="confirmShareTitle">Confirm</div>
        <div class="text-muted" id="confirmShareBody">...</div>

        <div class="d-flex justify-content-end gap-2 mt-4">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="confirmShareYes">Confirm</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- After-action popup -->
<div class="modal fade" id="shareStatusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-body text-center py-4">
        <div id="sharePopIcon" style="font-size:54px; line-height:1;">🔒</div>
        <div class="mt-2 fw-bold fs-4" id="sharePopTitle">Done</div>
        <div class="text-muted" id="sharePopText"></div>
      </div>
    </div>
  </div>
</div>

<style>
  @keyframes pop {
    0% { transform: scale(.7); opacity: .6; }
    70% { transform: scale(1.15); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
  }
  .pop-anim { animation: pop .28s ease-out; }
</style>

<script>
  // Confirmation helper: attach this to a form + mode ('share' or 'stop')
  window.confirmShareToggle = function(formId, mode) {
    const form = document.getElementById(formId);
    if (!form || !window.bootstrap) return;

    const titleEl = document.getElementById('confirmShareTitle');
    const bodyEl  = document.getElementById('confirmShareBody');
    const yesBtn  = document.getElementById('confirmShareYes');

    if (mode === 'share') {
      titleEl.textContent = "Share private profile?";
      bodyEl.textContent  = "Hey — you're about to share your private data with this user. Are you sure?";
      yesBtn.textContent  = "Yes, share";
      yesBtn.className    = "btn btn-primary";
    } else {
      titleEl.textContent = "Stop sharing?";
      bodyEl.textContent  = "This user will no longer be able to view your private profile. Continue?";
      yesBtn.textContent  = "Yes, stop sharing";
      yesBtn.className    = "btn btn-danger";
    }

    const modalEl = document.getElementById('confirmShareModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    // Remove old listener, add new one
    yesBtn.onclick = () => form.submit();

    modal.show();
  };

  // After-action popup (triggered by flash session)
  @if(session('share_status'))
    (function(){
      const status = "{{ session('share_status') }}"; // 'shared' or 'stopped'
      const icon = document.getElementById('sharePopIcon');
      const title = document.getElementById('sharePopTitle');
      const text = document.getElementById('sharePopText');

      if (status === 'shared') {
        icon.textContent = "🔓";
        title.textContent = "Private Profile Shared";
        text.textContent = "They can now view your private profile.";
      } else {
        icon.textContent = "🔒";
        title.textContent = "Sharing Stopped";
        text.textContent = "They can no longer view your private profile.";
      }

      icon.classList.remove('pop-anim');
      void icon.offsetWidth;
      icon.classList.add('pop-anim');

      const modalEl = document.getElementById('shareStatusModal');
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
      setTimeout(() => modal.hide(), 1000);
    })();
  @endif
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const sidebar = document.getElementById('sidebar');
  const btn = document.getElementById('sidebarCollapseBtn'); // use your real id

  btn?.addEventListener('click', () => {
    sidebar.classList.toggle('is-collapsed');
  });
</script>
<script>
  (function(){
    const sidebar = document.getElementById('sidebar');
    const logo = document.getElementById('sidebarLogo');
    const btn = document.getElementById('sidebarCollapseBtn'); // <-- use your real collapse button id

    if (!sidebar || !logo) return;

    function applyLogoSize(){
      const collapsed = sidebar.classList.contains('is-collapsed');
      logo.style.height = collapsed ? '24px' : '78px';
      logo.style.maxHeight = collapsed ? '24px' : '78px';
      logo.style.maxWidth  = collapsed ? '48px' : '140px';
    }

    // initial
    applyLogoSize();

    // toggle
    btn?.addEventListener('click', () => {
      sidebar.classList.toggle('is-collapsed');
      applyLogoSize();
    });

    // if sidebar is toggled elsewhere
    const obs = new MutationObserver(applyLogoSize);
    obs.observe(sidebar, { attributes:true, attributeFilter:['class'] });
  })();
</script>
</body>
</html>