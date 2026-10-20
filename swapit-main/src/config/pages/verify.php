<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Verify Email';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.auth-wrap{min-height:calc(100vh-60px);display:flex;align-items:center;justify-content:center;padding:40px 20px}
.auth-card{background:#fff;border-radius:20px;box-shadow:var(--shadow-lg);width:100%;max-width:440px;padding:44px 40px;text-align:center}
.auth-icon{font-size:52px;margin-bottom:20px}
.auth-title{font-size:24px;font-weight:800;margin-bottom:8px}
.auth-sub{font-size:14px;color:var(--gray);margin-bottom:32px}
.otp-wrap{display:flex;gap:10px;justify-content:center;margin-bottom:24px}
.otp-input{width:52px;height:56px;border:2px solid var(--border);border-radius:12px;font-size:22px;font-weight:700;text-align:center;outline:none;color:var(--dark);transition:.15s}
.otp-input:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(44,110,73,.1)}
.btn-auth{width:100%;padding:13px;background:var(--green);color:#fff;border:none;border-radius:10px;font-family:var(--font-display);font-size:15px;font-weight:700;cursor:pointer;transition:.15s}
.btn-auth:hover{background:var(--green2)}
.btn-auth:disabled{opacity:.6;cursor:not-allowed}
.auth-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--red);border-radius:10px;padding:11px 14px;font-size:13.5px;margin-bottom:16px;display:none}
.resend-wrap{margin-top:20px;font-size:13.5px;color:var(--gray)}
.resend-wrap a{color:var(--green);font-weight:600;cursor:pointer}
#successState{display:none}
</style>
<div class="auth-wrap">
  <div class="auth-card fade-up">
    <div id="verifyState">
      <div class="auth-icon">📧</div>
      <h1 class="auth-title">Check your email</h1>
      <p class="auth-sub">We sent a 6-digit code to <strong id="emailDisplay">your email</strong>. Enter it below.</p>
      <div class="auth-error" id="verifyError"></div>
      <form onsubmit="doVerify(event)">
        <div class="otp-wrap" id="otpWrap">
          <input class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
          <input class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
          <input class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
          <input class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
          <input class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
          <input class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]"/>
        </div>
        <button type="submit" class="btn-auth" id="verifyBtn">Verify Email</button>
      </form>
      <div id="devCodeHint" style="display:none;margin-top:16px;padding:12px 14px;background:rgba(44,110,73,.08);border:1px solid rgba(44,110,73,.25);border-radius:10px;font-size:13px;color:var(--dark)"></div>
      <div class="resend-wrap">
        Didn't get it? <a onclick="doResend()">Resend code</a>
        <span id="resendTimer"></span>
      </div>
    </div>
    <div id="successState">
      <div class="auth-icon">✅</div>
      <h1 class="auth-title">Email verified!</h1>
      <p class="auth-sub">Your account is ready. Redirecting to login…</p>
    </div>
  </div>
</div>
<script>
var email=localStorage.getItem('swapit_pending_email')||'';
document.getElementById('emailDisplay').textContent=email||'your email';
(function(){
  var code=localStorage.getItem('swapit_dev_code');
  if(!code)return;
  var el=document.getElementById('devCodeHint');
  el.style.display='block';
  el.innerHTML='<strong>Local dev:</strong> Email is not sent from XAMPP. Your verification code is <strong style="letter-spacing:3px">'+code+'</strong>';
})();
var inputs=Array.from(document.querySelectorAll('.otp-input'));
inputs.forEach(function(inp,i){
  inp.addEventListener('input',function(){
    if(inp.value&&i<inputs.length-1)inputs[i+1].focus();
  });
  inp.addEventListener('keydown',function(e){
    if(e.key==='Backspace'&&!inp.value&&i>0)inputs[i-1].focus();
  });
});
function getCode(){return inputs.map(function(i){return i.value;}).join('');}
async function doVerify(e){
  e.preventDefault();
  var code=getCode();
  if(code.length<6){return;}
  var btn=document.getElementById('verifyBtn'),err=document.getElementById('verifyError');
  err.style.display='none';btn.disabled=true;btn.textContent='Verifying…';
  try{
    var res=await fetch('<?= BASE_PATH ?>/src/config/api/auth.php?action=verify',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email:email,code:code})});
    var d=await res.json();
    if(d.ok){
      document.getElementById('verifyState').style.display='none';
      document.getElementById('successState').style.display='block';
      localStorage.removeItem('swapit_pending_email');
      localStorage.removeItem('swapit_dev_code');
      setTimeout(function(){window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';},2000);
    }else{
      err.textContent=d.error||'Invalid code. Please try again.';err.style.display='block';
      btn.disabled=false;btn.textContent='Verify Email';
    }
  }catch(ex){err.textContent='Network error.';err.style.display='block';btn.disabled=false;btn.textContent='Verify Email';}
}
var resendCooldown=0;
async function doResend(){
  if(resendCooldown>0)return;
  try{
    var res=await fetch('<?= BASE_PATH ?>/src/config/api/auth.php?action=resend',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email:email})});
    var d=await res.json();
    if(d.dev_verify_code){
      localStorage.setItem('swapit_dev_code',d.dev_verify_code);
      var el=document.getElementById('devCodeHint');
      el.style.display='block';
      el.innerHTML='<strong>Local dev:</strong> New code: <strong style="letter-spacing:3px">'+d.dev_verify_code+'</strong>';
    }
    resendCooldown=60;
    var timerEl=document.getElementById('resendTimer');
    var t=setInterval(function(){
      resendCooldown--;
      timerEl.textContent=' ('+resendCooldown+'s)';
      if(resendCooldown<=0){clearInterval(t);timerEl.textContent='';}
    },1000);
  }catch(ex){}
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
