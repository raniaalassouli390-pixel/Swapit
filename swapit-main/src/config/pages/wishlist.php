<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'My Wishlist';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.wl-wrap{max-width:1060px;margin:0 auto;padding:32px}
.wl-title{font-size:24px;font-weight:800;margin-bottom:24px}
.listings-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px}
.listing-card{background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:.18s;color:var(--dark);display:flex;flex-direction:column;position:relative}
.listing-card:hover{transform:translateY(-3px);box-shadow:var(--shadow)}
.lc-img{height:170px;background:var(--cream);position:relative;overflow:hidden}
.lc-img a{display:block;height:100%}
.lc-img img{width:100%;height:100%;object-fit:cover}
.lc-img-ph{display:flex;align-items:center;justify-content:center;height:100%;font-size:42px;color:var(--border)}
.lc-type{position:absolute;top:8px;left:8px;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:700}
.type-sale{background:var(--green);color:#fff}.type-swap{background:var(--orange);color:#fff}.type-auction{background:#7C3AED;color:#fff}
.rm-btn{position:absolute;top:8px;right:8px;width:28px;height:28px;background:rgba(255,255,255,.9);border:none;border-radius:50%;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.15s}
.rm-btn:hover{background:#fff;transform:scale(1.1)}
.lc-body{padding:12px 14px;flex:1;display:flex;flex-direction:column}
.lc-title{font-size:13.5px;font-weight:600;margin-bottom:4px;line-height:1.4;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.lc-title a{color:var(--dark);text-decoration:none}
.lc-title a:hover{color:var(--green)}
.lc-meta{font-size:11.5px;color:var(--gray);margin-bottom:6px}
.lc-price{font-family:var(--font-display);font-size:15px;font-weight:800;color:var(--green);margin-top:auto}
.empty-state{text-align:center;padding:80px 20px;color:var(--gray)}
.empty-icon{font-size:52px;margin-bottom:16px}
.empty-link{color:var(--green);font-weight:600}
</style>

<div class="wl-wrap">
  <h1 class="wl-title">❤️ My Wishlist</h1>
  <div id="wlContent"><div style="text-align:center;padding:60px;color:var(--gray)">Loading…</div></div>
</div>

<script>
var token=localStorage.getItem('swapit_token');
if(!token)window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';
var wishlistData=[];

async function load(){
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/wishlist.php',{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(d.ok){wishlistData=d.wishlist;render();}
    else{document.getElementById('wlContent').innerHTML='<div class="empty-state"><p>Error loading wishlist.</p></div>';}
  }catch(e){document.getElementById('wlContent').innerHTML='<div class="empty-state"><p>Network error.</p></div>';}
}

function render(){
  if(!wishlistData.length){
    document.getElementById('wlContent').innerHTML='<div class="empty-state"><div class="empty-icon">🤍</div><p>Your wishlist is empty.</p><a href="<?= BASE_PATH ?>/src/config/pages/browse.php" class="empty-link">Browse listings →</a></div>';
    return;
  }
  document.getElementById('wlContent').innerHTML='<div class="listings-grid">'+wishlistData.map(renderCard).join('')+'</div>';
}

function renderCard(w){
  var l=w;
  var typeLabel={sale:'For Sale',swap:'Swap',auction:'Auction'};
  var typeCls={sale:'type-sale',swap:'type-swap',auction:'type-auction'};
  var img=l.primary_image?'<a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+l.listing_id+'"><img src="'+esc(l.primary_image)+'" alt="" loading="lazy"/></a>':'<div class="lc-img-ph">📦</div>';
  var price=l.price!=null?'<div class="lc-price">JD '+parseFloat(l.price).toFixed(2)+'</div>':(l.type==='swap'?'<div class="lc-price" style="color:var(--orange)">Swap</div>':'');
  return '<div class="listing-card">'
    +'<div class="lc-img">'+img+'<div class="lc-type '+(typeCls[l.type]||'type-sale')+'">'+(typeLabel[l.type]||l.type)+'</div>'
    +'<button class="rm-btn" onclick="remove('+l.listing_id+')" title="Remove">✕</button></div>'
    +'<div class="lc-body"><div class="lc-title"><a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+l.listing_id+'">'+esc(l.title||'Untitled')+'</a></div>'
    +'<div class="lc-meta">📍 '+esc(l.city||'Jordan')+'</div>'
    +price+'</div></div>';
}

async function remove(listingId){
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/wishlist.php?listing_id='+listingId,{method:'DELETE',headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(d.ok){wishlistData=wishlistData.filter(function(w){return w.listing_id!==listingId;});render();}
    else alert(d.error||'Remove failed.');
  }catch(e){alert('Network error.');}
}

function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
