<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Reset Password';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.auth-wrap{min-height:calc(100vh-60px);display:flex;align-items:center;justify-content:center;padding:40px 20px}
.auth-card{background:#fff;border-radius:20px;box-shadow:var(--shadow-lg);width:100%;max-width:440px;padding:44px 40px}
.auth-icon{font-size:52px;margin-bottom:20px;text-align:center}
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
    <div class="auth-icon">🔐</div>
    <h1 class="auth-title">Set a new password</h1>
    <p class="auth-sub">Choose a strong password with at least 8 characters.</p>
    <div class="auth-error" id="rpError"></div>
    <div class="auth-success" id="rpSuccess"></div>
    <form id="rpForm" onsubmit="doReset(event)">
      <div class="form-group">
        <label class="form-label">New password</label>
        <input type="password" id="rpPassword" class="form-input" placeholder="Min. 8 characters" required minlength="8" autocomplete="new-password"/>
      </div>
      <div class="form-group">
        <label class="form-label">Confirm password</label>
        <input type="password" id="rpConfirm" class="form-input" placeholder="Repeat password" required minlength="8" autocomplete="new-password"/>
      </div>
      <button type="submit" class="btn-auth" id="rpBtn">Update Password</button>
    </form>
    <a href="<?= BASE_PATH ?>/src/config/pages/login.php" class="back-link">← Back to Login</a>
  </div>
</div>
<script>
var token=new URLSearchParams(location.search).get('token')||'';
if(!token){
  document.getElementById('rpError').textContent='Invalid or missing reset link. Request a new one from the forgot password page.';
  document.getElementById('rpError').style.display='block';
  document.getElementById('rpForm').style.display='none';
}
async function doReset(e){
  e.preventDefault();
  var btn=document.getElementById('rpBtn'),err=document.getElementById('rpError'),ok=document.getElementById('rpSuccess');
  var pw=document.getElementById('rpPassword').value;
  var cf=document.getElementById('rpConfirm').value;
  err.style.display='none';ok.style.display='none';
  if(pw!==cf){err.textContent='Passwords do not match.';err.style.display='block';return;}
  if(pw.length<8){err.textContent='Password must be at least 8 characters.';err.style.display='block';return;}
  btn.disabled=true;btn.textContent='Updating…';
  try{
    var res=await fetch('<?= BASE_PATH ?>/src/config/api/auth.php?action=reset_password',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({token:token,password:pw})});
    var d=await res.json();
    if(d.ok){
      ok.textContent=d.message||'Password updated! Redirecting to login…';
      ok.style.display='block';
      document.getElementById('rpForm').style.display='none';
      setTimeout(function(){window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';},2000);
    }else{
      err.textContent=d.error||'Could not reset password.';err.style.display='block';
      btn.disabled=false;btn.textContent='Update Password';
    }
  }catch(ex){err.textContent='Network error. Please try again.';err.style.display='block';btn.disabled=false;btn.textContent='Update Password';}
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
