<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Log In';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.auth-wrap{min-height:calc(100vh - 60px);display:flex;align-items:center;justify-content:center;padding:40px 20px}
.auth-card{background:#fff;border-radius:20px;box-shadow:var(--shadow-lg);width:100%;max-width:440px;padding:44px 40px}
.auth-logo{font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--green);margin-bottom:28px}
.auth-title{font-size:26px;font-weight:800;margin-bottom:6px}
.auth-sub{font-size:14px;color:var(--gray);margin-bottom:32px}
.form-group{margin-bottom:18px}
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
.form-link{font-size:12.5px;color:var(--green);font-weight:600}
</style>
<div class="auth-wrap">
  <div class="auth-card fade-up">
    <div class="auth-logo">🔄 SwapIt</div>
    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-sub">Log in to your account</p>
    <div class="auth-error" id="loginError"></div>
    <form id="loginForm" onsubmit="doLogin(event)">
      <div class="form-group">
        <label class="form-label">Email address</label>
        <input type="email" id="email" class="form-input" placeholder="you@example.com" required autocomplete="email"/>
      </div>
      <div class="form-group">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
          <label class="form-label" style="margin:0">Password</label>
          <a href="<?= BASE_PATH ?>/src/config/pages/forgot-password.php" class="form-link">Forgot password?</a>
        </div>
        <div class="pw-wrap">
          <input type="password" id="password" class="form-input" placeholder="••••••••" required autocomplete="current-password"/>
          <button type="button" class="pw-toggle" onclick="togglePw()">👁</button>
        </div>
      </div>
      <button type="submit" class="btn-auth" id="loginBtn">Log In</button>
    </form>
    <div class="auth-footer">
      Don't have an account? <a href="<?= BASE_PATH ?>/src/config/pages/register.php">Sign up free</a>
    </div>
  </div>
</div>
<script>
(function(){if(localStorage.getItem('swapit_token')){window.location.href='<?= BASE_PATH ?>/src/config/pages/home.php';}})();
function togglePw(){var i=document.getElementById('password');var h=i.type==='password';i.type=h?'text':'password';}
async function doLogin(e){
  e.preventDefault();
  var btn=document.getElementById('loginBtn'),err=document.getElementById('loginError');
  err.style.display='none';btn.disabled=true;btn.textContent='Logging in…';
  try{
    var res=await fetch('<?= BASE_PATH ?>/src/config/api/auth.php?action=login',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email:document.getElementById('email').value.trim(),password:document.getElementById('password').value})});
    var d=await res.json();
    if(d.ok){
      localStorage.setItem('swapit_token',d.token);
      localStorage.setItem('swapit_user',JSON.stringify({id:d.user.id,name:d.user.username,email:d.user.email,role:d.user.role,avatar:d.user.avatar||''}));
      var next=new URLSearchParams(location.search).get('next');
      window.location.href=next||'<?= BASE_PATH ?>/src/config/pages/home.php';
    }else{
      err.textContent=d.error||'Invalid email or password.';err.style.display='block';
      btn.disabled=false;btn.textContent='Log In';
    }
  }catch(ex){err.textContent='Network error. Please try again.';err.style.display='block';btn.disabled=false;btn.textContent='Log In';}
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
