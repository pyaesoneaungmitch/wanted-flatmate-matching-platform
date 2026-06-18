<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — Wanted</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{ background: var(--bg, #F7F6F2); }
    .auth-wrap{ min-height:100vh; display:flex; align-items:center; justify-content:center; padding:32px 16px; }
    .auth-card{
      width: 520px;
      height: 820px;
      background: #fff;
      border: 1px solid rgba(17,17,17,.10);
      border-radius: 18px;
      overflow:hidden;
      box-shadow: 0 14px 40px rgba(17,17,17,.10);
      font-size: 16.5px;
    }
    .auth-top{
      background: #111;
      padding: 2px;
      display:flex;
      justify-content:center;
      align-items:center;
    }
    .logo-pill{
      background: #000000(255, 255, 255, 0.45);
      border: 1.5px solid rgb(0, 0, 0);
      color:#fff;
      font-weight: 900;
      font-size:20px;
      padding: 10px 16px;
      border-radius: 12px;
      letter-spacing:.3px;
    }
    .hero{
      background: #F2CC0F;
      padding: 22px 18px;
    }
    .hero h1{
      margin:0;
      font-weight: 900;
      font-size: 50px;
      line-height: 1.05;
      color:#111;
    }
    .hero p{ margin:10px 0 0; color: rgba(17,17,17,.75); font-weight:600; font-size:20px }

    .content{ padding: 18px; }
    .form-label{ font-weight: 800; font-size: 16px; letter-spacing:.2px; }
    .form-control{
      border-radius: 12px;
      border: 1px solid rgba(17,17,17,.15);
      padding: 12px 12px;
    }
    .form-control:focus{
      border-color:#F2CC0F;
      box-shadow: 0 0 0 .2rem rgba(242,204,15,.25);
    }
    .pw-wrap{ position:relative; }
    .pw-toggle{
      position:absolute;
      right:10px; top:50%;
      transform: translateY(-50%);
      border:none;
      background: transparent;
      font-size: 18px;
      padding: 6px 8px;
      opacity:.7;
    }
    .pw-toggle:hover{ opacity:1; }

    .btn-wanted{
      background:#F2CC0F;
      border: 1px solid #F2CC0F;
      color:#111;
      font-weight: 900;
      border-radius: 12px;
      padding: 12px;
    }
    .btn-wanted:hover{ background:#FFE35a; }

    .divider{
      border-top: 1px solid rgba(17,17,17,.12);
      margin: 18px 0 14px;
    }
    .subhead{ font-weight: 900; font-size: 20px; margin-bottom: 10px; }
    .btn-outline{
      background:#fff;
      border: 1px solid rgba(17,17,17,.18);
      color:#111;
      font-weight: 900;
      border-radius: 12px;
      padding: 12px;
    }
    .btn-outline:hover{ background: rgba(242,204,15,.14); }
    .small-link{ color: rgba(17,17,17,.65); text-decoration:none; font-weight:700; }
    .small-link:hover{ color:#111; text-decoration:underline; }

    .mini-badges{ display:flex; gap:8px; flex-wrap:wrap; margin-top: 10px; }
    .mini-badge{
      color:#F2CC0F;
      font-weight: 900;
      font-size:20px;
      border-radius: 999px;
      padding: 6px 20px;
      font-weight: 800;
      background: rgb(0, 0, 0)
    }
  </style>
</head>
<body>

<div class="auth-wrap">
  <div class="auth-card">

    <div class="auth-top">
      <div style=>
        <img src="{{ url('/storage/assets/logos/LogoSai_Sideway.png') }}" alt="Wanted Logo" style="height:150px; width:auto; display:block;">
      </div>
    </div>

    <div class="hero">
      <h1>Welcome back 👋</h1>
      <p>Pick up where you left off — matches, chats, and listings.</p>
      <div class="mini-badges">
        <span class="mini-badge"> Swipe </span>
        <span class="mini-badge"> Match </span>
        <span class="mini-badge"> List </span>
      </div>
    </div>

    <div class="content">

      @if ($errors->any())
        <div class="alert alert-danger">
          <div class="fw-bold mb-1">Please fix:</div>
          <ul class="mb-0">
            @foreach ($errors->all() as $e)
              <li>{{ $e }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="mb-2">
          <label class="form-label d-flex justify-content-between align-items-center">
            <span>Password</span>
            <a class="small-link" href="#" onclick="alert('Forgot password can be added later.');return false;">Forgot Password?</a>
          </label>

          <div class="pw-wrap">
            <input type="password" class="form-control" name="password" id="pw" required>
            <button class="pw-toggle" type="button" onclick="togglePw()" aria-label="Show password">👁</button>
          </div>
        </div>

        <div class="form-check my-3">
          <input class="form-check-input" type="checkbox" id="terms">
          <label class="form-check-label" for="terms" style="font-weight:700;color:rgba(17,17,17,.7);">
            I agree to the Terms and Conditions
          </label>
        </div>

        <button class="btn btn-wanted w-100" type="submit" id="loginBtn" disabled>Log In</button>
      </form>

      <div class="divider"></div>

      <div class="subhead">New here?</div>
      <a class="btn btn-outline w-100" href="{{ route('register') }}">Register Now</a>

    </div>
  </div>
</div>

<script>
  function togglePw(){
    const pw = document.getElementById('pw');
    pw.type = (pw.type === 'password') ? 'text' : 'password';
  }

  // Enable login only if terms checked (matches your wireframe intent)
  const terms = document.getElementById('terms');
  const btn = document.getElementById('loginBtn');
  terms.addEventListener('change', () => {
    btn.disabled = !terms.checked;
  });
</script>

</body>
</html>