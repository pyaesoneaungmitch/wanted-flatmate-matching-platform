<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title','Wanted')</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  {{-- keep your theme vars so colors still match --}}
  <style>
    :root{
      --bg: #F7F6F2;
      --panel: #FFFFFF;
      --border: rgba(17,17,17,.10);
      --text: #111111;
      --muted: rgba(17,17,17,.60);
      --shadow: 0 14px 40px rgba(17,17,17,.10);
      --accent: #F2CC0F;
      --accent-soft: rgba(242,204,15,.18);
    }
    body{ background: var(--bg); color: var(--text); }

    .btn-wanted{
      background: var(--accent) !important;
      border-color: var(--accent) !important;
      color: var(--text) !important;
      font-weight: 900;
      border-radius: 12px;
    }
    .btn-wanted-outline{
      background: var(--panel) !important;
      border: 1px solid var(--border) !important;
      color: var(--text) !important;
      font-weight: 900;
      border-radius: 12px;
    }
    .form-control:focus, .form-select:focus{
      border-color: var(--accent) !important;
      box-shadow: 0 0 0 .2rem rgba(242,204,15,.25) !important;
    }
  </style>

  @stack('head')
</head>
<body>
  @yield('content')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
  <div class="text-center py-4" style="color: rgba(17,17,17,.65); font-weight: 700;">
  <div class="d-flex justify-content-center align-items-center gap-2">
    <img src="{{ url('\storage\assets\logos\sidebarlogosmol.png') }}" alt="Wanted" style="height:50px;width:auto;display:block;">
    <span>Developed by Pyae Sone Aung @ 2026</span>
  </div>
</div>
</body>
</html>