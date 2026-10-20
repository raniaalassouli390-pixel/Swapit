<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Create Account';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.auth-wrap{min-height:calc(100vh-60px);display:flex;align-items:center;justify-content:center;padding:40px 20px}
.auth-card{background:#fff;border-radius:20px;box-shadow:var(--shadow-lg);width:100%;max-width:480px;padding:44px 40px}
.auth-logo{font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--green);margin-bottom:28px}
.auth-title{font-size:26px;font-weight:800;margin-bottom:6px}
.auth-sub{font-size:14px;color:var(--gray);margin-bottom:32px}
.form-group{margin-bottom:16px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-label{display:block;font-size:13px;font-weight:600;color:var(--dark);margin-bottom:6px}
.form-input{width:100%;padding:11px 14px;border:1.5px solid var(--border);border-radius:10px;font-size:14px;color:var(--dark);background:#fff;transition:.15s;outline:none}
.form-input:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(44,110,73,.1)}
.btn-auth{width:100%;padding:13px;background:var(--green);color:#fff;border:none;border-radius:10px;font-family:var(--font-display);font-size:15px;font-weight:700;cursor:pointer;transition:.15s;margin-top:8px}
.btn-auth:hover{background:var(--green2)}
.btn-auth:disabled{opacity:.6;cursor:not-allowed}
.auth-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--red);border-radius:10px;padding:11px 14px;font-size:13.5px;margin-bottom:16px;display:none}
.auth-footer{text-align:center;font-size:13.5px;color:var(--gray);margin-top:24px}
.auth-footer a{color:var(--green);font-weight:600}
.pw-wrap{position:relative}
.pw-toggle{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px;color:var(--gray);padding:2px}
.pw-hint{font-size:11.5px;color:var(--gray);margin-top:5px}
</style>
<div class="auth-wrap">
  <div class="auth-card fade-up">
    <div class="auth-logo">🔄 SwapIt</div>
    <h1 class="auth-title">Create your account</h1>
    <p class="auth-sub">Join Jordan's favourite marketplace</p>
    <div class="auth-error" id="regError"></div>
    <form id="regForm" onsubmit="doRegister(event)">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">First name</label>
          <input type="text" id="first_name" class="form-input" placeholder="Ahmad" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Last name</label>
          <input type="text" id="last_name" class="form-input" placeholder="Hassan"/>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Username</label>
        <input type="text" id="username" class="form-input" placeholder="ahmad_h" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Email address</label>
        <input type="email" id="email" class="form-input" placeholder="you@example.com" required autocomplete="email"/>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="pw-wrap">
          <input type="password" id="password" class="form-input" placeholder="Min 8 chars, upper, lower, number, symbol" required/>
          <button type="button" class="pw-toggle" onclick="togglePw()">👁</button>
        </div>
        <p class="pw-hint">At least 8 characters with uppercase, lowercase, number &amp; symbol.</p>
      </div>
      <button type="submit" class="btn-auth" id="regBtn">Create Account</button>
    </form>
    <div class="auth-footer">
      Already have an account? <a href="<?= BASE_PATH ?>/src/config/pages/login.php">Log in</a>
    </div>
  </div>
</div>
<script>
(function(){if(localStorage.getItem('swapit_token')){window.location.href='<?= BASE_PATH ?>/src/config/pages/home.php';}})();
function togglePw(){var i=document.getElementById('password');i.type=i.type==='password'?'text':'password';}
async function doRegister(e){
  e.preventDefault();
  var btn=document.getElementById('regBtn'),err=document.getElementById('regError');
  err.style.display='none';btn.disabled=true;btn.textContent='Creating account…';
  try{
    var res=await fetch('<?= BASE_PATH ?>/src/config/api/auth.php?action=register',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({
      first_name:document.getElementById('first_name').value.trim(),
      last_name:document.getElementById('last_name').value.trim(),
      username:document.getElementById('username').value.trim(),
      email:document.getElementById('email').value.trim(),
      password:document.getElementById('password').value
    })});
    var d=await res.json();
    if(d.ok){
      localStorage.setItem('swapit_pending_email',document.getElementById('email').value.trim());
      if(d.dev_verify_code)localStorage.setItem('swapit_dev_code',d.dev_verify_code);
      else localStorage.removeItem('swapit_dev_code');
      window.location.href='<?= BASE_PATH ?>/src/config/pages/verify.php';
    }else{
      err.textContent=d.error||'Registration failed. Please check your details.';err.style.display='block';
      btn.disabled=false;btn.textContent='Create Account';
    }
  }catch(ex){err.textContent='Network error. Please try again.';err.style.display='block';btn.disabled=false;btn.textContent='Create Account';}
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
