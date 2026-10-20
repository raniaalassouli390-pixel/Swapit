<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Reset Password';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.auth-wrap{min-height:calc(100vh-60px);display:flex;align-items:center;justify-content:center;padding:40px 20px}
.auth-card{background:#fff;border-radius:20px;box-shadow:var(--shadow-lg);width:100%;max-width:440px;padding:44px 40px;text-align:center}
.auth-icon{font-size:52px;margin-bottom:20px}
.auth-title{font-size:24px;font-weight:800;margin-bottom:8px;text-align:left}
.auth-sub{font-size:14px;color:var(--gray);margin-bottom:28px;text-align:left}
.form-group{margin-bottom:18px;text-align:left}
.form-label{display:block;font-size:13px;font-weight:600;color:var(--dark);margin-bottom:6px}
.form-input{width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;color:var(--dark);background:#fff;transition:.15s;outline:none}
.form-input:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(44,110,73,.1)}
.btn-auth{width:100%;padding:13px;background:var(--green);color:#fff;border:none;border-radius:10px;font-family:var(--font-display);font-size:15px;font-weight:700;cursor:pointer;transition:.15s}
.btn-auth:hover{background:var(--green2)}
.btn-auth:disabled{opacity:.6;cursor:not-allowed}
.auth-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--red);border-radius:10px;padding:11px 14px;font-size:13.5px;margin-bottom:16px;display:none;text-align:left}
.auth-success{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:#15803d;border-radius:10px;padding:14px 16px;font-size:14px;margin-bottom:16px;display:none;text-align:left}
.back-link{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--gray);margin-top:20px}
.back-link:hover{color:var(--dark)}
</style>
<div class="auth-wrap">
  <div class="auth-card fade-up">
    <div class="auth-icon">🔑</div>
    <h1 class="auth-title">Forgot your password?</h1>
    <p class="auth-sub">Enter the email linked to your SwapIt account and we'll send you a reset link.</p>
    <div class="auth-error" id="fpError"></div>
    <div class="auth-success" id="fpSuccess"></div>
    <form id="fpForm" onsubmit="doForgot(event)">
      <div class="form-group">
        <label class="form-label">Email address</label>
        <input type="email" id="fpEmail" class="form-input" placeholder="you@example.com" required autocomplete="email"/>
      </div>
      <button type="submit" class="btn-auth" id="fpBtn">Send Reset Link</button>
    </form>
    <a href="<?= BASE_PATH ?>/src/config/pages/login.php" class="back-link">← Back to Login</a>
  </div>
</div>
<script>
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
async function doForgot(e){
  e.preventDefault();
  var btn=document.getElementById('fpBtn'),err=document.getElementById('fpError'),ok=document.getElementById('fpSuccess');
  err.style.display='none';ok.style.display='none';
  btn.disabled=true;btn.textContent='Sending…';
  try{
    var res=await fetch('<?= BASE_PATH ?>/src/config/api/auth.php?action=forgot_password',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email:document.getElementById('fpEmail').value.trim()})});
    var d=await res.json();
    if(d.ok){
      var msg='If this email is registered, a reset link has been sent.';
      if(d.dev_reset_link){
        msg+=' Local dev link: ';
        ok.innerHTML=msg+'<a href="'+esc(d.dev_reset_link)+'" style="color:#15803d;font-weight:700;word-break:break-all">'+esc(d.dev_reset_link)+'</a>';
      }else{
        ok.textContent=msg;
      }
      ok.style.display='block';
      document.getElementById('fpForm').style.display='none';
    }else{
      err.textContent=d.error||'Could not send reset link.';err.style.display='block';
      btn.disabled=false;btn.textContent='Send Reset Link';
    }
  }catch(ex){err.textContent='Network error. Please try again.';err.style.display='block';btn.disabled=false;btn.textContent='Send Reset Link';}
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
