@extends('layouts.blank')
@section('title','Quiz — Step 4')

@section('content')
<style>
  .wrap{
    min-height: 100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding: 24px 14px;
    background: var(--bg);
  }
  .box{
    width: min(980px, 100%);
    background: var(--panel);
    border: 1px solid var(--border);
    border-radius: 18px;
    box-shadow: var(--shadow);
    padding: 22px;
  }
  .header{ display:flex; justify-content:center; margin-bottom: 16px; }
  .title-pill{
    background: rgba(242,204,15,.20);
    border: 1px solid rgba(242,204,15,.55);
    border-radius: 14px;
    padding: 10px 16px;
    font-weight: 900;
  }
  .qtitle{
    text-align:center;
    font-weight: 900;
    font-size: 24px;
    margin: 6px 0 18px;
  }

  .cardish{
    background:#fff;
    border: 1px solid var(--border);
    border-radius: 18px;
    padding: 16px;
  }

  .section-label{
    font-weight: 900;
    font-size: 16px;
    margin: 6px 0 12px;
  }

  /* Photo uploader */
  .photo-row{
    display:flex;
    align-items:center;
    gap: 16px;
  }
  .photo-preview{
    width: 96px;
    height: 96px;
    border-radius: 18px;
    background: rgba(17,17,17,.04);
    border: 1px solid var(--border);
    overflow:hidden;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight: 900;
    color: var(--muted);
  }
  .photo-preview img{ width:100%; height:100%; object-fit:cover; }

  .tip{
    background: rgba(242,204,15,.16);
    border: 1px solid rgba(242,204,15,.55);
    border-radius: 16px;
    padding: 12px 14px;
    display:flex;
    gap: 12px;
    align-items:flex-start;
  }
  .tip-icon{
    width: 34px; height:34px;
    border-radius: 12px;
    background: rgba(242,204,15,.28);
    border: 1px solid rgba(242,204,15,.55);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size: 18px;
    font-weight: 900;
    flex: 0 0 auto;
  }
  .tip-title{ font-weight: 900; margin-bottom: 2px; }
  .tip-text{ color: var(--muted); font-weight: 700; font-size: 13px; line-height: 1.35; }

  .icon-input{ position: relative; }
  .icon-input .icon{
    position:absolute;
    left: 12px; top: 50%;
    transform: translateY(-50%);
    font-size: 18px;
    opacity:.8;
  }
  .icon-input input{ padding-left: 42px !important; }

  .actions{
    display:flex;
    justify-content:center;
    margin-top: 18px;
  }
</style>

<div class="wrap">
  <div class="box">
    <div class="header">
      <div class="title-pill">Personal Information</div>
    </div>

    <div class="qtitle">Last step — set up your profile</div>

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

    <form method="POST" action="{{ route('quiz.step4.save') }}" enctype="multipart/form-data">
      @csrf

      <div class="row g-3">

        {{-- Profile photo (top) --}}
        <div class="col-12">
          <div class="cardish">
            <div class="section-label">Profile photo</div>

            <div class="photo-row">
              <div class="photo-preview" id="photoPreview">
                <span>Photo</span>
              </div>

              <div class="flex-grow-1">
                <input class="form-control form-control-lg" type="file" name="profile_photo" id="profilePhoto"
                       accept="image/*">
                <div class="small text-muted mt-2">
                  Optional — a clear photo helps people feel confident replying.
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Public profile --}}
        <div class="col-12">
          <div class="cardish">
            <div class="section-label">Public Profile</div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Display Name*</label>
                <input class="form-control form-control-lg" name="display_name" maxlength="60"
                       value="{{ old('display_name', $v['display_name'] ?? '') }}" required>
              </div>

              <div class="col-md-3">
                <label class="form-label fw-bold">Age</label>
                <input type="number" class="form-control form-control-lg" name="age" min="16" max="99"
                       value="{{ old('age', $v['age'] ?? '') }}" placeholder="Optional">
              </div>

              <div class="col-12">
                <label class="form-label fw-bold">Bio</label>
                <textarea class="form-control form-control-lg" name="bio" rows="3" maxlength="300"
                          placeholder="A short intro (optional)">{{ old('bio', $v['bio'] ?? '') }}</textarea>
              </div>
            </div>
          </div>
        </div>

        {{-- Occupation + Working hours together --}}
        <div class="col-12">
          <div class="cardish">
            <div class="section-label">Work & Routine</div>

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Occupation</label>
                <input class="form-control form-control-lg" name="occupation" maxlength="80"
                       value="{{ old('occupation', $v['occupation'] ?? '') }}" placeholder="Optional">
              </div>

              <div class="col-md-6">
                <label class="form-label fw-bold">Working hours</label>
                @php $wh = old('working_hours', $v['working_hours'] ?? 'RATHER_NOT_TELL'); @endphp
                <select class="form-select form-select-lg" name="working_hours">
                  <option value="MORNING" @selected($wh==='MORNING')>Morning</option>
                  <option value="DAY" @selected($wh==='DAY')>Day</option>
                  <option value="NIGHT" @selected($wh==='NIGHT')>Night</option>
                  <option value="MIXED" @selected($wh==='MIXED')>Mixed</option>
                  <option value="RATHER_NOT_TELL" @selected($wh==='RATHER_NOT_TELL')>Rather not tell</option>
                </select>
              </div>
            </div>

            {{-- You're almost done tip --}}
            <div class="tip mt-3">
              <div class="tip-icon">✨</div>
              <div>
                <div class="tip-title">You’re almost done</div>
                <div class="tip-text">
                  Quick tip: keep your bio simple — say your vibe, what you’re looking for,
                  and one or two non-negotiables (e.g., quiet nights, tidy kitchen).
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Socials + contact --}}
        <div class="col-12">
          <div class="cardish">
            <div class="section-label">Contact & Socials (Optional)</div>

            <div class="row g-3">
              <div class="col-md-6 icon-input">
                <span class="icon">📞</span>
                <input class="form-control form-control-lg" name="phone_number" maxlength="30"
                       value="{{ old('phone_number', $v['phone_number'] ?? '') }}"
                       placeholder="Phone number (optional)">
              </div>

              <div class="col-md-6 icon-input">
                <span class="icon">✉️</span>
                <input type="email" class="form-control form-control-lg" name="contact_email" maxlength="255"
                       value="{{ old('contact_email', $v['contact_email'] ?? '') }}"
                       placeholder="Contact email (optional)">
              </div>

              <div class="col-md-4 icon-input">
                <span class="icon">📷</span>
                <input class="form-control form-control-lg" name="instagram" maxlength="255"
                       value="{{ old('instagram', $v['instagram'] ?? '') }}"
                       placeholder="Instagram (optional)">
              </div>

              <div class="col-md-4 icon-input">
                <span class="icon">🐦</span>
                <input class="form-control form-control-lg" name="twitter" maxlength="255"
                       value="{{ old('twitter', $v['twitter'] ?? '') }}"
                       placeholder="Twitter (optional)">
              </div>

              <div class="col-md-4 icon-input">
                <span class="icon">👻</span>
                <input class="form-control form-control-lg" name="snapchat" maxlength="255"
                       value="{{ old('snapchat', $v['snapchat'] ?? '') }}"
                       placeholder="Snapchat (optional)">
              </div>
            </div>

            <div class="small text-muted mt-2">
              These stay private until you choose to share after matching.
            </div>
          </div>
        </div>

      </div>

      <div class="actions">
        <button type="submit" class="btn btn-wanted btn-lg px-5">Finish &amp; Unlock Discover</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Instant preview for profile photo
  const input = document.getElementById('profilePhoto');
  const preview = document.getElementById('photoPreview');

  input?.addEventListener('change', () => {
    const file = input.files && input.files[0];
    if (!file) return;

    const url = URL.createObjectURL(file);
    preview.innerHTML = '';
    const img = document.createElement('img');
    img.src = url;
    preview.appendChild(img);
  });
</script>
@endsection