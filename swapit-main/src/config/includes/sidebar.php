<?php

$activePage = $activePage ?? '';
$sidebarLinks = [
  ['href'=>BASE_PATH . '/src/config/pages/profile.php',    'icon'=>'👤', 'label'=>'My Profile',   'key'=>'profile'],
  ['href'=>BASE_PATH . '/src/config/pages/my-listings.php','icon'=>'📋', 'label'=>'My Listings',  'key'=>'listings'],
  ['href'=>BASE_PATH . '/src/config/pages/my-orders.php',  'icon'=>'🛍️', 'label'=>'My Orders',    'key'=>'orders'],
  ['href'=>BASE_PATH . '/src/config/pages/my-bids.php',    'icon'=>'🔨', 'label'=>'My Bids',      'key'=>'bids'],
  ['href'=>BASE_PATH . '/src/config/pages/swap-offers.php','icon'=>'🔁', 'label'=>'Swap Offers',  'key'=>'swaps'],
  ['href'=>BASE_PATH . '/src/config/pages/messages.php',   'icon'=>'💬', 'label'=>'Messages',     'key'=>'messages'],
  ['href'=>BASE_PATH . '/src/config/pages/wishlist.php',   'icon'=>'❤️', 'label'=>'Wishlist',     'key'=>'wishlist'],
  ['href'=>BASE_PATH . '/src/config/pages/reviews.php',    'icon'=>'⭐', 'label'=>'Reviews',      'key'=>'reviews'],
  ['href'=>BASE_PATH . '/src/config/pages/notifications.php','icon'=>'🔔','label'=>'Notifications','key'=>'notifications'],
  ['href'=>BASE_PATH . '/src/config/pages/edit-profile.php','icon'=>'⚙️','label'=>'Settings',     'key'=>'settings'],
];
?>
<aside class="sidebar">
  <div class="sidebar-user" id="sidebarUser">
    <div class="sidebar-avatar" id="sidebarAvatar">U</div>
    <div>
      <div class="sidebar-name" id="sidebarName">Loading…</div>
      <div class="sidebar-email" id="sidebarEmail"></div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <?php foreach ($sidebarLinks as $link): ?>
    <a href="<?= $link['href'] ?>" class="sidebar-link <?= $activePage === $link['key'] ? 'active' : '' ?>">
      <span class="sidebar-icon"><?= $link['icon'] ?></span>
      <?= $link['label'] ?>
    </a>
    <?php endforeach; ?>
  </nav>
</aside>
<style>
.sidebar{width:230px;flex-shrink:0;background:#fff;border:1px solid var(--border);border-radius:16px;padding:20px;height:fit-content}
.sidebar-user{display:flex;align-items:center;gap:12px;padding-bottom:16px;border-bottom:1px solid var(--border);margin-bottom:12px}
.sidebar-avatar{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green2));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:18px;flex-shrink:0}
.sidebar-name{font-size:14px;font-weight:600;color:var(--dark)}
.sidebar-email{font-size:11.5px;color:var(--gray)}
.sidebar-nav{display:flex;flex-direction:column;gap:2px}
.sidebar-link{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;font-size:13.5px;color:var(--dark);transition:.15s}
.sidebar-link:hover{background:var(--cream)}
.sidebar-link.active{background:rgba(44,110,73,.08);color:var(--green);font-weight:600}
.sidebar-icon{font-size:16px;width:22px;text-align:center}
@media(max-width:760px){.sidebar{width:100%;border-radius:12px}}
</style>
<script>
(function(){
  var user=null;try{user=JSON.parse(localStorage.getItem('swapit_user'));}catch(e){}
  if(user){
    var n=document.getElementById('sidebarName');
    var em=document.getElementById('sidebarEmail');
    var av=document.getElementById('sidebarAvatar');
    if(n) n.textContent=user.name||'User';
    if(em) em.textContent=user.email||'';
    if(av) av.textContent=((user.name||user.email||'U')[0]).toUpperCase();
  }
})();
</script>
