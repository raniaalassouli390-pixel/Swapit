<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Swap Offers';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.so-wrap{max-width:900px;margin:0 auto;padding:32px}
.so-title{font-size:24px;font-weight:800;margin-bottom:6px}
.so-sub{font-size:14px;color:var(--gray);margin-bottom:24px}
.so-tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:22px}
.sotab{padding:10px 20px;font-size:13.5px;font-weight:600;color:var(--gray);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:.15s}
.sotab.active{color:var(--green);border-bottom-color:var(--green)}
.offers-list{display:flex;flex-direction:column;gap:14px}
.offer-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px;transition:.15s}
.offer-card:hover{box-shadow:var(--shadow)}
.offer-hd{display:flex;align-items:center;gap:12px;margin-bottom:12px}
.offer-av{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green2));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0}
.offer-av img{width:100%;height:100%;object-fit:cover;border-radius:50%}
.offer-user{font-size:14px;font-weight:700}
.offer-date{font-size:12px;color:var(--gray);margin-left:auto}
.offer-listing{display:flex;gap:12px;align-items:center;background:var(--cream);border-radius:10px;padding:10px;margin-bottom:12px}
.offer-listing-img{width:52px;height:52px;border-radius:8px;background:#fff;overflow:hidden;flex-shrink:0}
.offer-listing-img img{width:100%;height:100%;object-fit:cover}
.offer-listing-title{font-size:13.5px;font-weight:600}
.offer-listing-price{font-size:13px;color:var(--green);font-weight:700}
.offer-msg{font-size:13.5px;color:var(--gray);line-height:1.6;margin-bottom:14px}
.offer-actions{display:flex;gap:8px;flex-wrap:wrap}
.accept-btn{padding:8px 18px;background:var(--green);color:#fff;border:none;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;transition:.15s}
.accept-btn:hover{background:var(--green2)}
.reject-btn{padding:8px 18px;background:rgba(239,68,68,.08);border:1.5px solid rgba(239,68,68,.2);color:var(--red);border-radius:9px;font-size:13px;font-weight:600;cursor:pointer}
.cancel-btn{padding:8px 18px;background:none;border:1.5px solid var(--border);border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;color:var(--gray)}
.msg-btn{padding:8px 18px;background:none;border:1.5px solid var(--green);border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;color:var(--green)}
.offer-status{display:inline-block;padding:4px 12px;border-radius:99px;font-size:12px;font-weight:600;margin-left:auto}
.status-pending{background:rgba(245,158,11,.1);color:#B45309}
.status-accepted{background:rgba(34,197,94,.1);color:#15803d}
.status-rejected{background:rgba(239,68,68,.1);color:var(--red)}
.status-cancelled{background:rgba(107,114,128,.1);color:var(--gray)}
.empty-state{text-align:center;padding:70px;color:var(--gray)}
.empty-icon{font-size:50px;margin-bottom:14px}
</style>

<div class="so-wrap">
  <h1 class="so-title">Swap Offers</h1>
  <p class="so-sub">Manage swap requests you've received and sent</p>
  <div class="so-tabs">
    <div class="sotab active" onclick="switchTab('received',this)">Received</div>
    <div class="sotab" onclick="switchTab('sent',this)">Sent</div>
  </div>
  <div id="offersContent"><div style="text-align:center;padding:60px;color:var(--gray)">Loading…</div></div>
</div>

<script>
var token=localStorage.getItem('swapit_token');
if(!token)window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';
var currentUser=null;try{currentUser=JSON.parse(localStorage.getItem('swapit_user'));}catch(e){}
var allOffers=[];
var currentTab='received';

async function load(){
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/swaps.php',{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(d.ok){allOffers=d.offers||d.swaps||[];render();}
    else document.getElementById('offersContent').innerHTML='<div class="empty-state"><p>Error loading offers.</p></div>';
  }catch(e){document.getElementById('offersContent').innerHTML='<div class="empty-state"><p>Network error.</p></div>';}
}

function switchTab(tab,el){
  currentTab=tab;
  document.querySelectorAll('.sotab').forEach(function(t){t.classList.remove('active');});
  el.classList.add('active');
  render();
}

function render(){
  var filtered=allOffers.filter(function(o){
    if(currentTab==='received')return currentUser&&o.seller_id==currentUser.id;
    return currentUser&&o.requester_id==currentUser.id;
  });
  if(!filtered.length){
    document.getElementById('offersContent').innerHTML='<div class="empty-state"><div class="empty-icon">🔄</div><p>No '+currentTab+' offers yet.</p></div>';
    return;
  }
  document.getElementById('offersContent').innerHTML='<div class="offers-list">'+filtered.map(renderOffer).join('')+'</div>';
}

function renderOffer(o){
  var isReceived=currentTab==='received';
  var otherName=isReceived?(o.requester_name||'Unknown'):(o.seller_name||'Unknown');
  var avHtml=o.requester_avatar&&isReceived?'<img src="'+esc(o.requester_avatar)+'" alt=""/>':initials(otherName);
  var listingImg=o.listing_image?'<img src="'+esc(o.listing_image)+'" alt="" loading="lazy"/>':'';
  var statusCls={pending:'status-pending',accepted:'status-accepted',rejected:'status-rejected',cancelled:'status-cancelled'};
  var actionBtns='';
  if(o.status==='pending'){
    if(isReceived){
      actionBtns='<button class="accept-btn" onclick="respond('+o.id+',\'accept\')">✓ Accept</button>'
        +'<button class="reject-btn" onclick="respond('+o.id+',\'reject\')">✕ Decline</button>'
        +'<button class="msg-btn" onclick="msgUser('+(o.requester_id)+')">💬 Message</button>';
    }else{
      actionBtns='<button class="cancel-btn" onclick="respond('+o.id+',\'cancel\')">Cancel Offer</button>'
        +'<button class="msg-btn" onclick="msgUser('+(o.seller_id)+')">💬 Message</button>';
    }
  }
  return '<div class="offer-card">'
    +'<div class="offer-hd">'
      +'<div class="offer-av">'+avHtml+'</div>'
      +'<div><div class="offer-user">'+esc(otherName)+'</div>'
      +'<div style="font-size:12px;color:var(--gray)">'+(isReceived?'wants to swap for:':'offer for:')+'</div></div>'
      +'<div class="offer-date">'+relTime(o.created_at)+'</div>'
      +'<div class="offer-status '+(statusCls[o.status]||'status-pending')+'">'+capitalize(o.status||'pending')+'</div>'
    +'</div>'
    +'<div class="offer-listing">'
      +'<div class="offer-listing-img">'+listingImg+'</div>'
      +'<div><div class="offer-listing-title">'+esc(o.listing_title||'—')+'</div>'
      +'<div class="offer-listing-price">'+(o.listing_price!=null?'JD '+parseFloat(o.listing_price).toFixed(2):'Swap')+'</div></div>'
    +'</div>'
    +(o.message?'<div class="offer-msg">'+esc(o.message)+'</div>':'')
    +(actionBtns?'<div class="offer-actions">'+actionBtns+'</div>':'')
  +'</div>';
}

async function respond(id,action){
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/swaps.php?id='+id,{method:'PUT',headers:{'Content-Type':'application/json',Authorization:'Bearer '+token},body:JSON.stringify({action:action})});
    var d=await r.json();
    if(d.ok){load();}else alert(d.error||'Action failed.');
  }catch(e){alert('Network error.');}
}

function msgUser(userId){window.location.href='<?= BASE_PATH ?>/src/config/pages/chat.php?user_id='+userId;}
function initials(n){return n.split(' ').map(function(p){return p[0]||'';}).join('').slice(0,2).toUpperCase();}
function capitalize(s){return s?s.charAt(0).toUpperCase()+s.slice(1):'';}
function relTime(dt){var d=new Date(dt),now=new Date(),diff=(now-d)/1000;if(diff<3600)return Math.floor(diff/60)+'m ago';if(diff<86400)return Math.floor(diff/3600)+'h ago';return Math.floor(diff/86400)+'d ago';}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
