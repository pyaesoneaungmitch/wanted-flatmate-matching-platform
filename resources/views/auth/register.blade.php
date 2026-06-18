<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register — Wanted</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{ background: var(--bg, #F7F6F2); }
    .wrap{ min-height:100vh; display:flex; align-items:center; justify-content:center; padding:28px 18px; }
    .panel{
      width: 980px;
      background:#fff;
      border: 1px solid rgba(17,17,17,.10);
      border-radius: 18px;
      overflow:hidden;
      box-shadow: 0 14px 40px rgba(17,17,17,.10);
      display:grid;
      grid-template-columns: 46% 54%;
    }

    /* LEFT YELLOW */
    .left{
      background:#F2CC0F;
      padding: 26px 22px;
      position:relative;
    }
    .logo-box{
      padding: 3px;
      width: fit-content;
      color:#fff;
      font-weight:900;
    }
    .hero{
      margin-top: 46px;
      font-weight: 900;
      font-size: 54px;
      line-height: 1.02;
      color:#111;
    }
    .sub{
      margin-top: 18px;
      color: rgba(17,17,17,.75);
      font-weight: 700;
      font-size: 14px;
      max-width: 360px;
    }

    /* RIGHT FORM */
    .right{ padding: 30px 28px; }
    .title{ font-weight: 900; font-size: 34px; margin-bottom: 18px; }

    .form-label{ font-weight: 900; font-size: 14px; }
    .form-control{
      border-radius: 12px;
      border: 1px solid rgba(17,17,17,.18);
      padding: 14px 14px;
      font-size: 16px;
    }
    .form-control:focus{
      border-color:#F2CC0F;
      box-shadow: 0 0 0 .2rem rgba(242,204,15,.25);
    }

    .pw-wrap{ position:relative; }
    .pw-toggle{
      position:absolute; right:10px; top:50%;
      transform: translateY(-50%);
      border:none; background:transparent;
      font-size:18px; padding:6px 8px; opacity:.75;
    }
    .pw-toggle:hover{ opacity:1; }

    .btn-main{
      background:#F2CC0F; border:1px solid #F2CC0F;
      color:#111; font-weight:900;
      border-radius: 12px;
      padding: 14px;
      font-size: 17px;
    }
    .btn-main:hover{  background-color:rgba(242,204,15,.55) }

    .btn-alt{
      background: rgb(0, 0, 0);
      color:#F2CC0F;
      font-weight:900;
      border-radius:12px;
      padding: 14px;
      font-size: 20px;
    }
    .btn-alt:hover{  background-color:rgba(242,204,15,.55) }

    .section-title{ font-weight: 900; font-size: 30px; margin: 24px 0 10px; }
    @media(max-width: 992px){
      .panel{ grid-template-columns: 1fr; width: 520px; }
      .hero{ font-size: 42px; }
    }
  </style>
</head>
<body>
<div class="wrap">
  <div class="panel">

    <div class="left">
      <center>
      <div class="logo-box">
        <img src="{{ url('/storage/assets/logos/LogoSai_YBG.png') }}" alt="Wanted Logo" style="height:150px; width:250px; display:block;">
      </div></center>

      <div class="hero">Find your Roommates. <br>
Swipe smarter. <br> Live easier.</div>
      <div class="sub">
        Find compatible roommates, explore listings, and chat safely.
        Your private info stays locked until you choose to share.
      </div>
    </div>

    <div class="right">
      <div class="title">Create Account</div>

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

      <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Name*</label>
            <input class="form-control" name="username" value="{{ old('username') }}" placeholder="Enter name" required>
            
          </div>

          <div class="col-md-6">
            <label class="form-label">Email*</label>
            <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Enter email" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Password*</label>
            <div class="pw-wrap">
              <input type="password" class="form-control" name="password" id="pw1" placeholder="Enter Password" required>
              <button class="pw-toggle" type="button" onclick="togglePw('pw1')">👁</button>
            </div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Confirm Password*</label>
            <div class="pw-wrap">
              <input type="password" class="form-control" name="password_confirmation" id="pw2" placeholder="Retype Password" required>
              <button class="pw-toggle" type="button" onclick="togglePw('pw2')">👁</button>
            </div>
          </div>
        </div>

    
        
           <!-- reCAPTCHA v2 widget -->
          <div class="mt-3 g-recaptcha" data-sitekey="6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI"></div>
        
        <div class="form-check my-3">
          <input class="form-check-input" type="checkbox" id="terms">
          <label class="form-check-label fw-bold" for="terms">
            I agree to the <span class="text-decoration-underline">Terms and Conditions</span>
          </label>
        </div>

        <button class="btn btn-main w-100" type="submit" id="createBtn" disabled>Create Account</button>
<hr>
        <div class="section-title">Already Registered?</div>
        <a class="btn btn-alt w-100" href="{{ route('login') }}">Log In</a>
      </form>
    </div>

  </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
  function togglePw(id){
    const el = document.getElementById(id);
    el.type = (el.type === 'password') ? 'text' : 'password';
  }
  const terms = document.getElementById('terms');
  const btn = document.getElementById('createBtn');
  terms.addEventListener('change', ()=> btn.disabled = !terms.checked);
</script>
</body>
</html>