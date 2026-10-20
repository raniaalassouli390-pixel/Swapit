<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'My Orders';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.orders-wrap{max-width:900px;margin:0 auto;padding:32px}
.orders-title{font-size:24px;font-weight:800;margin-bottom:24px}
.orders-tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:22px}
.otab{padding:10px 20px;font-size:13.5px;font-weight:600;color:var(--gray);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:.15s}
.otab.active{color:var(--green);border-bottom-color:var(--green)}
.orders-list{display:flex;flex-direction:column;gap:12px}
.order-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px;display:flex;gap:16px;align-items:flex-start;transition:.15s}
.order-card:hover{box-shadow:var(--shadow)}
.order-img{width:68px;height:68px;border-radius:10px;background:var(--cream);overflow:hidden;flex-shrink:0}
.order-img img{width:100%;height:100%;object-fit:cover}
.order-img-ph{display:flex;align-items:center;justify-content:center;height:100%;font-size:26px;color:var(--border)}
.order-info{flex:1;min-width:0}
.order-title{font-size:14.5px;font-weight:700;margin-bottom:4px}
.order-title a{color:var(--dark);text-decoration:none}
.order-title a:hover{color:var(--green)}
.order-sub{font-size:12.5px;color:var(--gray);margin-bottom:8px}
.order-actions{display:flex;gap:8px;flex-wrap:wrap}
.order-btn{padding:6px 14px;border-radius:8px;font-size:12.5px;font-weight:600;cursor:pointer;border:1.5px solid var(--border);background:#fff;color:var(--dark);transition:.15s;text-decoration:none;display:inline-block}
.order-btn:hover{border-color:var(--green);color:var(--green)}
.order-btn-primary{background:var(--green);color:#fff;border-color:var(--green)}
.order-btn-primary:hover{background:var(--green2)}
.order-meta{text-align:right;flex-shrink:0}
.order-price{font-family:var(--font-display);font-size:16px;font-weight:800;color:var(--green)}
.order-status{display:inline-block;padding:4px 11px;border-radius:99px;font-size:12px;font-weight:600;margin-top:5px}
.s-pending{background:rgba(245,158,11,.1);color:#B45309}
.s-accepted{background:rgba(34,197,94,.1);color:#15803d}
.s-rejected{background:rgba(239,68,68,.1);color:var(--red)}
.s-cancelled{background:rgba(107,114,128,.1);color:var(--gray)}
.s-won{background:rgba(124,58,237,.1);color:#5B21B6}
.empty-state{text-align:center;padding:80px 20px;color:var(--gray)}
</style>

<div class="orders-wrap">
  <h1 class="orders-title">📦 My Orders & Activity</h1>
  <div class="orders-tabs">
    <div class="otab active" onclick="switchTab('swaps',this)">Swap Offers</div>
    <div class="otab" onclick="switchTab('bids',this)">My Bids</div>
  </div>
  <div id="ordersContent"><div style="text-align:center;padding:60px;color:var(--gray)">Loading…</div></div>
</div>

<script>
var token=localStorage.getItem('swapit_token');
if(!token)window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';
var currentUser=null;try{currentUser=JSON.parse(localStorage.getItem('swapit_user'));}catch(e){}
var swapData=[],bidData=[];
var currentTab='swaps';

async function load(){
  try{
    var [swapRes,bidRes]=await Promise.all([
      fetch('<?= BASE_PATH ?>/src/config/api/swaps.php',{headers:{Authorization:'Bearer '+token}}),
      fetch('<?= BASE_PATH ?>/src/config/api/bids.php?my=1',{headers:{Authorization:'Bearer '+token}})
    ]);
    var sd=await swapRes.json(),bd=await bidRes.json();
    if(sd.ok)swapData=sd.offers||sd.swaps||[];
    if(bd.ok)bidData=bd.bids||[];
    render();
  }catch(e){document.getElementById('ordersContent').innerHTML='<div class="empty-state"><p>Error loading.</p></div>';}
}

function switchTab(tab,el){
  currentTab=tab;
  document.querySelectorAll('.otab').forEach(function(t){t.classList.remove('active');});
  el.classList.add('active');render();
}

function render(){
  if(currentTab==='swaps'){renderSwaps();}
  else{renderBids();}
}

function renderSwaps(){
  if(!swapData.length){
    document.getElementById('ordersContent').innerHTML='<div class="empty-state"><p>No swap offers yet.</p><a href="<?= BASE_PATH ?>/src/config/pages/browse.php?type=swap" style="color:var(--green);font-weight:600">Browse swap listings →</a></div>';
    return;
  }
  document.getElementById('ordersContent').innerHTML='<div class="orders-list">'+swapData.map(renderSwap).join('')+'</div>';
}

function renderSwap(o){
  var img=o.listing_image?'<img src="'+esc(o.listing_image)+'" alt="" loading="lazy"/>':'<div class="order-img-ph">🔄</div>';
  var isSender=currentUser&&o.requester_id==currentUser.id;
  var statusCls={pending:'s-pending',accepted:'s-accepted',rejected:'s-rejected',cancelled:'s-cancelled'};
  var actions='<a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+o.listing_id+'" class="order-btn">View Listing</a>';
  if(!isSender&&o.status==='pending')actions+='<button class="order-btn order-btn-primary" onclick="acceptSwap('+o.id+')">Accept</button><button class="order-btn" onclick="rejectSwap('+o.id+')">Decline</button>';
  if(isSender&&o.status==='accepted')actions+='<a href="<?= BASE_PATH ?>/src/config/pages/reviews.php?user_id='+o.seller_id+'" class="order-btn order-btn-primary">⭐ Review</a>';
  return '<div class="order-card"><div class="order-img">'+img+'</div>'
    +'<div class="order-info">'
      +'<div class="order-title"><a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+o.listing_id+'">'+esc(o.listing_title||'Swap Listing')+'</a></div>'
      +'<div class="order-sub">'+(isSender?'You offered a swap':'Swap request received')+' · '+relTime(o.created_at)+'</div>'
      +(o.message?'<div style="font-size:13px;color:var(--gray);margin-bottom:8px">'+esc(o.message)+'</div>':'')
      +'<div class="order-actions">'+actions+'</div>'
    +'</div>'
    +'<div class="order-meta"><div class="order-price">'+(o.listing_price!=null?'JD '+parseFloat(o.listing_price).toFixed(2):'Swap')+'</div>'
    +'<div class="order-status '+(statusCls[o.status]||'s-pending')+'">'+capitalize(o.status||'pending')+'</div></div>'
  +'</div>';
}

function renderBids(){
  if(!bidData.length){
    document.getElementById('ordersContent').innerHTML='<div class="empty-state"><p>No bids placed yet.</p><a href="<?= BASE_PATH ?>/src/config/pages/auction.php" style="color:var(--green);font-weight:600">Browse auctions →</a></div>';
    return;
  }
  document.getElementById('ordersContent').innerHTML='<div class="orders-list">'+bidData.map(renderBid).join('')+'</div>';
}

function renderBid(b){
  var img=b.listing_image?'<img src="'+esc(b.listing_image)+'" alt="" loading="lazy"/>':'<div class="order-img-ph">🔨</div>';
  var ended=b.auction_end&&new Date(b.auction_end)<new Date();
  var statusHtml=ended?(b.is_winning?'<div class="order-status s-won">🏆 Won</div>':'<div class="order-status s-rejected">Outbid</div>'):(b.is_winning?'<div class="order-status s-accepted">Winning</div>':'<div class="order-status s-pending">Outbid</div>');
  return '<div class="order-card"><div class="order-img">'+img+'</div>'
    +'<div class="order-info">'
      +'<div class="order-title"><a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+b.listing_id+'">'+esc(b.listing_title||'Auction')+'</a></div>'
      +'<div class="order-sub">Your bid: JD '+parseFloat(b.amount).toFixed(2)+' · '+relTime(b.created_at)+'</div>'
      +'<div class="order-actions"><a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+b.listing_id+'" class="order-btn">View Auction</a></div>'
    +'</div>'
    +'<div class="order-meta"><div class="order-price">JD '+parseFloat(b.amount).toFixed(2)+'</div>'+statusHtml+'</div>'
  +'</div>';
}

async function acceptSwap(id){
  try{var r=await fetch('<?= BASE_PATH ?>/src/config/api/swaps.php?id='+id,{method:'PUT',headers:{'Content-Type':'application/json',Authorization:'Bearer '+token},body:JSON.stringify({action:'accept'})});var d=await r.json();if(d.ok)load();else alert(d.error);}catch(e){alert('Error.');}
}
async function rejectSwap(id){
  try{var r=await fetch('<?= BASE_PATH ?>/src/config/api/swaps.php?id='+id,{method:'PUT',headers:{'Content-Type':'application/json',Authorization:'Bearer '+token},body:JSON.stringify({action:'reject'})});var d=await r.json();if(d.ok)load();else alert(d.error);}catch(e){alert('Error.');}
}

function capitalize(s){return s?s.charAt(0).toUpperCase()+s.slice(1):'';}
function relTime(dt){var d=new Date(dt),now=new Date(),diff=(now-d)/1000;if(diff<3600)return Math.floor(diff/60)+'m ago';if(diff<86400)return Math.floor(diff/3600)+'h ago';return Math.floor(diff/86400)+'d ago';}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
