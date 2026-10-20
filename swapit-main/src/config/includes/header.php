<?php

require_once __DIR__ . '/../config.php';
$pageTitle  = $pageTitle  ?? 'SwapIt';
$extraCss   = $extraCss   ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title><?= htmlspecialchars($pageTitle) ?> — SwapIt</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="<?= BASE_PATH ?>/public/css/main.css"/>
<?php foreach ($extraCss as $css): ?>
<link rel="stylesheet" href="<?= htmlspecialchars($css) ?>"/>
<?php endforeach; ?>
<style>
.site-nav{background:#fff;border-bottom:1px solid var(--border);height:60px;display:flex;align-items:center;justify-content:space-between;padding:0 28px;position:sticky;top:0;z-index:100;box-shadow:0 1px 6px rgba(0,0,0,.04)}
.nav-logo{font-family:var(--font-display);font-weight:800;font-size:20px;color:var(--dark);display:flex;align-items:center;gap:8px;text-decoration:none}
.nav-logo-icon{width:30px;height:30px;background:var(--green);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0}
.nav-links{display:flex;gap:22px;align-items:center}
.nav-links a{font-size:14px;color:var(--gray);font-weight:500;transition:.15s}
.nav-links a:hover{color:var(--dark)}
.nav-actions{display:flex;align-items:center;gap:10px;position:relative}
.nav-btn-primary{padding:8px 16px;background:var(--green);color:#fff;border-radius:9px;font-size:13px;font-weight:700;font-family:var(--font-display);transition:.15s}
.nav-btn-primary:hover{background:var(--green2)}
.nav-btn-secondary{padding:7px 14px;background:none;border:1.5px solid var(--border);border-radius:9px;font-size:13px;color:var(--dark);font-weight:500}
.nav-icon-btn{width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:9px;font-size:18px;position:relative;text-decoration:none}
.nav-icon-btn:hover{background:var(--cream)}
.notif-badge{position:absolute;top:3px;right:3px;min-width:16px;height:16px;background:var(--red);color:#fff;border-radius:99px;font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 3px}
.nav-avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green2));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;cursor:pointer;flex-shrink:0}
.nav-dropdown{position:absolute;top:calc(100% + 10px);right:0;background:#fff;border:1px solid var(--border);border-radius:14px;padding:6px;min-width:185px;box-shadow:0 8px 28px rgba(0,0,0,.12);z-index:200}
.nav-dropdown a{display:block;padding:9px 14px;font-size:13.5px;color:var(--dark);border-radius:8px;transition:.15s}
.nav-dropdown a:hover{background:var(--cream)}
.nav-dropdown .nav-logout{color:var(--red);}
.nav-dropdown-divider{height:1px;background:var(--border);margin:5px 0}
.nav-hamburger{display:none;background:none;border:none;font-size:20px;padding:4px}
@media(max-width:768px){
  .nav-links{display:none;position:absolute;top:60px;left:0;right:0;background:#fff;border-bottom:1px solid var(--border);flex-direction:column;padding:12px 20px;gap:10px;z-index:99}
  .nav-links.open{display:flex}
  .nav-hamburger{display:block}
}
</style>
</head>
<body>
<nav class="site-nav" id="siteNav">
  <a href="<?= BASE_PATH ?>/src/config/pages/home.php" class="nav-logo">
    <div class="nav-logo-icon">🔄</div> SwapIt
  </a>
  <div class="nav-links" id="navLinks">
    <a href="<?= BASE_PATH ?>/src/config/pages/browse.php">Browse</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/search.php">Search</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/auction.php">Auctions</a>
  </div>
  <div class="nav-actions" id="navActions">
    <a href="<?= BASE_PATH ?>/src/config/pages/create-listing.php" class="nav-btn-primary">+ Sell / Swap</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/notifications.php" class="nav-icon-btn" id="navNotifBtn" style="display:none">🔔<span class="notif-badge" id="navNotifCount" style="display:none">0</span></a>
    <a href="<?= BASE_PATH ?>/src/config/pages/messages.php" class="nav-icon-btn" id="navMsgBtn" style="display:none">💬</a>
    <div class="nav-avatar" id="navAvatar" style="display:none" onclick="toggleNavMenu()">
      <span id="navAvatarInitial">U</span>
    </div>
    <div class="nav-dropdown" id="navDropdown" style="display:none">
      <a href="<?= BASE_PATH ?>/src/config/pages/profile.php">My Profile</a>
      <a href="<?= BASE_PATH ?>/src/config/pages/my-listings.php">My Listings</a>
      <a href="<?= BASE_PATH ?>/src/config/pages/my-bids.php">My Bids</a>
      <a href="<?= BASE_PATH ?>/src/config/pages/swap-offers.php">Swap Offers</a>
      <a href="<?= BASE_PATH ?>/src/config/pages/wishlist.php">Wishlist</a>
      <a href="<?= BASE_PATH ?>/src/config/pages/edit-profile.php">Settings</a>
      <div class="nav-dropdown-divider" id="navAdminDivider" style="display:none"></div>
      <a href="<?= BASE_PATH ?>/src/config/pages/admin/dashboard.php" id="navAdminLink" style="display:none;color:#5B21B6;font-weight:700">⚙️ Admin Panel</a>
      <div class="nav-dropdown-divider"></div>
      <a href="#" onclick="swapitLogout();return false;" class="nav-logout">Log Out</a>
    </div>
    <a href="<?= BASE_PATH ?>/src/config/pages/login.php" class="nav-btn-secondary" id="navLoginBtn">Log In</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/register.php" class="nav-btn-primary" id="navRegisterBtn">Sign Up</a>
  </div>
  <button class="nav-hamburger" onclick="toggleNav()">☰</button>
</nav>
<script>
(function(){
  var token = localStorage.getItem('swapit_token');
  var user  = null;
  try { user = JSON.parse(localStorage.getItem('swapit_user')); } catch(e){}
  if (token && user) {
    var loginBtn    = document.getElementById('navLoginBtn');
    var registerBtn = document.getElementById('navRegisterBtn');
    if (loginBtn)    loginBtn.style.display    = 'none';
    if (registerBtn) registerBtn.style.display = 'none';
    document.getElementById('navNotifBtn').style.display = '';
    document.getElementById('navMsgBtn').style.display   = '';
    document.getElementById('navAvatar').style.display   = '';
    document.getElementById('navAvatarInitial').textContent = ((user.name||user.email||'U')[0]).toUpperCase();
    if (user.role === 'admin') {
      document.getElementById('navAdminLink').style.display    = '';
      document.getElementById('navAdminDivider').style.display = '';
    }
    fetch('<?= BASE_PATH ?>/src/config/api/notifications.php',{headers:{'Authorization':'Bearer '+token}})
      .then(function(r){return r.json();}).then(function(d){
        var n = (d.notifications||[]).filter(function(x){return !x.is_read;}).length;
        if(n>0){
          var el = document.getElementById('navNotifCount');
          el.textContent = n>9?'9+':n;
          el.style.display='';
        }
      }).catch(function(){});
  }
})();
function toggleNavMenu(){
  var dd=document.getElementById('navDropdown');
  dd.style.display=dd.style.display==='none'?'block':'none';
}
function toggleNav(){
  document.getElementById('navLinks').classList.toggle('open');
}
function swapitLogout(){
  var token=localStorage.getItem('swapit_token');
  if(token){fetch('<?= BASE_PATH ?>/src/config/api/auth.php?action=logout',{method:'POST',headers:{'Authorization':'Bearer '+token}}).catch(function(){});}
  localStorage.removeItem('swapit_token');
  localStorage.removeItem('swapit_user');
  window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';
}
function requireLogin(){
  if(!localStorage.getItem('swapit_token')){
    window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php?next='+encodeURIComponent(window.location.pathname);
    return false;
  }
  return true;
}
function authHeaders(){
  return {'Authorization':'Bearer '+localStorage.getItem('swapit_token'),'Content-Type':'application/json'};
}
document.addEventListener('click',function(e){
  var dd=document.getElementById('navDropdown');
  var av=document.getElementById('navAvatar');
  if(dd&&!dd.contains(e.target)&&av&&!av.contains(e.target)){dd.style.display='none';}
});
</script>
