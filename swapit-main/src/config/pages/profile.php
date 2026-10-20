<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'User Profile';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.profile-wrap{max-width:960px;margin:0 auto;padding:32px}
.profile-header{background:#fff;border:1px solid var(--border);border-radius:16px;padding:28px;display:flex;gap:24px;align-items:flex-start;margin-bottom:20px}
.profile-av{width:88px;height:88px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green2));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:32px;flex-shrink:0;overflow:hidden}
.profile-av img{width:100%;height:100%;object-fit:cover}
.profile-info{flex:1}
.profile-name{font-size:22px;font-weight:800;margin-bottom:4px}
.profile-username{font-size:13.5px;color:var(--gray);margin-bottom:10px}
.profile-stats{display:flex;gap:24px;flex-wrap:wrap}
.p-stat{text-align:center}
.p-stat-num{font-family:var(--font-display);font-size:18px;font-weight:800;color:var(--green)}
.p-stat-lbl{font-size:12px;color:var(--gray)}
.profile-actions{display:flex;gap:8px;align-items:flex-start;flex-shrink:0}
.btn-sm{padding:8px 16px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;transition:.15s;text-decoration:none;display:inline-block}
.btn-sm-primary{background:var(--green);color:#fff;border:none}
.btn-sm-primary:hover{background:var(--green2)}
.btn-sm-secondary{background:#fff;border:1.5px solid var(--border);color:var(--dark)}
.btn-sm-secondary:hover{border-color:var(--green)}
.profile-bio{font-size:14px;color:var(--gray);margin-top:10px;line-height:1.6}
.profile-tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:22px}
.ptab{padding:11px 20px;font-size:13.5px;font-weight:600;color:var(--gray);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:.15s}
.ptab.active{color:var(--green);border-bottom-color:var(--green)}
.listings-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:16px}
.listing-card{background:#fff;border:1px solid var(--border);border-radius:12px;overflow:hidden;text-decoration:none;color:var(--dark);display:flex;flex-direction:column;transition:.15s}
.listing-card:hover{transform:translateY(-2px);box-shadow:var(--shadow)}
.lc-img{height:150px;background:var(--cream);overflow:hidden;position:relative}
.lc-img img{width:100%;height:100%;object-fit:cover}
.lc-img-ph{display:flex;align-items:center;justify-content:center;height:100%;font-size:40px;color:var(--border)}
.lc-type{position:absolute;top:7px;left:7px;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:700}
.type-sale{background:var(--green);color:#fff}.type-swap{background:var(--orange);color:#fff}.type-auction{background:#7C3AED;color:#fff}
.lc-body{padding:11px 13px;flex:1}
.lc-title{font-size:13px;font-weight:600;margin-bottom:4px;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.lc-price{font-size:14px;font-weight:800;color:var(--green);font-family:var(--font-display)}
.reviews-list{display:flex;flex-direction:column;gap:14px}
.review-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:16px}
.review-hd{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.rev-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green2));display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0}
.rev-name{font-size:13.5px;font-weight:600}
.rev-stars{color:#F59E0B;font-size:14px}
.rev-date{font-size:12px;color:var(--gray);margin-left:auto}
.rev-text{font-size:13.5px;color:var(--gray);line-height:1.6}
.empty-state{text-align:center;padding:60px;color:var(--gray)}
.verified-badge{display:inline-flex;align-items:center;gap:4px;background:rgba(34,197,94,.1);color:var(--green);border-radius:99px;padding:3px 10px;font-size:12px;font-weight:600;margin-left:8px}
.rating-big{display:flex;align-items:center;gap:8px;margin-top:8px}
.rating-num{font-size:24px;font-weight:800;font-family:var(--font-display)}
.rating-stars{font-size:18px}
</style>

<div class="profile-wrap">
  <div id="profileContent"><div style="text-align:center;padding:60px;color:var(--gray)">Loading…</div></div>
</div>

<script>
var userId=new URLSearchParams(location.search).get('id');
var currentUser=null;try{currentUser=JSON.parse(localStorage.getItem('swapit_user'));}catch(e){}
var activeTab='listings';

if(!userId&&currentUser)userId=currentUser.id;

async function load(){
  if(!userId){window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';return;}
  try{
    var token=localStorage.getItem('swapit_token')||'';
    var headers=token?{Authorization:'Bearer '+token}:{};
    var [uRes,lRes,rRes]=await Promise.all([
      fetch('<?= BASE_PATH ?>/src/config/api/users.php?id='+userId,{headers:headers}),
      fetch('<?= BASE_PATH ?>/src/config/api/listings.php?seller_id='+userId+'&status=active'),
      fetch('<?= BASE_PATH ?>/src/config/api/reviews.php?user_id='+userId)
    ]);
    var ud=await uRes.json(),ld=await lRes.json(),rd=await rRes.json();
    if(!ud.ok){document.getElementById('profileContent').innerHTML='<p style="text-align:center;padding:40px">User not found.</p>';return;}
    render(ud.user,ld.ok?ld.listings:[],rd.ok?rd.reviews:[]);
  }catch(ex){console.error(ex);}
}

function render(u,listings,reviews){
  var isOwn=currentUser&&currentUser.id==u.id;
  var avHtml=u.avatar?'<img src="'+esc(u.avatar)+'" alt=""/>':initials(u.first_name||u.username||'?');
  var verBadge=u.is_verified?'<span class="verified-badge">✓ Verified</span>':'';
  var actions='';
  if(isOwn){actions='<a href="<?= BASE_PATH ?>/src/config/pages/edit-profile.php" class="btn-sm btn-sm-secondary">Edit Profile</a>';}
  else if(currentUser){actions='<button class="btn-sm btn-sm-primary" onclick="msgUser()">💬 Message</button>';}
  var html='<div class="profile-header">'
    +'<div class="profile-av">'+avHtml+'</div>'
    +'<div class="profile-info">'
      +'<div class="profile-name">'+esc((u.first_name||'')+' '+(u.last_name||'')).trim()||esc(u.username)+verBadge+'</div>'
      +'<div class="profile-username">@'+esc(u.username)+(u.city?' · 📍 '+esc(u.city):'')+'</div>'
      +(u.bio?'<div class="profile-bio">'+esc(u.bio)+'</div>':'')
      +'<div class="rating-big"><div class="rating-num">'+(u.avg_rating?parseFloat(u.avg_rating).toFixed(1):'—')+'</div>'
      +'<div class="rating-stars">'+stars(u.avg_rating)+'</div>'
      +'<div style="font-size:13px;color:var(--gray)">('+((u.total_reviews)||0)+' reviews)</div></div>'
      +'<div class="profile-stats" style="margin-top:14px">'
        +'<div class="p-stat"><div class="p-stat-num">'+listings.length+'</div><div class="p-stat-lbl">Listings</div></div>'
        +'<div class="p-stat"><div class="p-stat-num">'+((u.total_reviews)||0)+'</div><div class="p-stat-lbl">Reviews</div></div>'
        +'<div class="p-stat"><div class="p-stat-num">'+((u.total_swaps)||0)+'</div><div class="p-stat-lbl">Swaps</div></div>'
        +'<div class="p-stat"><div class="p-stat-num">'+sinceYear(u.created_at)+'</div><div class="p-stat-lbl">Member Since</div></div>'
      +'</div>'
    +'</div>'
    +'<div class="profile-actions">'+actions+'</div>'
  +'</div>'
  +'<div class="profile-tabs">'
    +'<div class="ptab active" id="tab-listings" onclick="switchTab(\'listings\')">Listings ('+listings.length+')</div>'
    +'<div class="ptab" id="tab-reviews" onclick="switchTab(\'reviews\')">Reviews ('+reviews.length+')</div>'
  +'</div>'
  +'<div id="tabContent"></div>';
  document.getElementById('profileContent').innerHTML=html;
  renderTab('listings',listings,reviews);
}

function switchTab(tab){
  activeTab=tab;
  document.querySelectorAll('.ptab').forEach(function(t){t.classList.toggle('active',t.id==='tab-'+tab);});
}

function renderTab(tab,listings,reviews){
  var el=document.getElementById('tabContent');
  if(tab==='listings'){
    el.innerHTML=listings.length?'<div class="listings-grid">'+listings.map(renderCard).join('')+'</div>':'<div class="empty-state">No listings yet.</div>';
  }else{
    el.innerHTML=reviews.length?'<div class="reviews-list">'+reviews.map(renderReview).join('')+'</div>':'<div class="empty-state">No reviews yet.</div>';
  }
}

function renderCard(l){
  var typeLabel={sale:'For Sale',swap:'Swap',auction:'Auction'};
  var typeCls={sale:'type-sale',swap:'type-swap',auction:'type-auction'};
  var img=l.primary_image?'<img src="'+esc(l.primary_image)+'" alt="" loading="lazy"/>':'<div class="lc-img-ph">📦</div>';
  var price=l.price!=null?'<div class="lc-price">JD '+parseFloat(l.price).toFixed(2)+'</div>':(l.type==='swap'?'<div class="lc-price" style="color:var(--orange)">Swap</div>':'');
  return '<a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+l.id+'" class="listing-card">'
    +'<div class="lc-img">'+img+'<div class="lc-type '+(typeCls[l.type]||'type-sale')+'">'+(typeLabel[l.type]||l.type)+'</div></div>'
    +'<div class="lc-body"><div class="lc-title">'+esc(l.title||'Untitled')+'</div>'+price+'</div></a>';
}

function renderReview(r){
  return '<div class="review-card">'
    +'<div class="review-hd"><div class="rev-av">'+initials(r.reviewer_name||'?')+'</div>'
    +'<div><div class="rev-name">'+esc(r.reviewer_name||'Anonymous')+'</div>'
    +'<div class="rev-stars">'+stars(r.rating)+'</div></div>'
    +'<div class="rev-date">'+relTime(r.created_at)+'</div></div>'
    +'<div class="rev-text">'+esc(r.comment||'')+'</div></div>';
}

function msgUser(){window.location.href='<?= BASE_PATH ?>/src/config/pages/chat.php?user_id='+userId;}
function stars(n){var r=Math.round(n||0);return '★'.repeat(r)+'☆'.repeat(5-r);}
function initials(n){return n.split(' ').map(function(p){return p[0]||'';}).join('').slice(0,2).toUpperCase();}
function sinceYear(dt){return dt?new Date(dt).getFullYear():'—';}
function relTime(dt){var d=new Date(dt),now=new Date(),diff=(now-d)/1000;if(diff<86400)return 'today';return Math.floor(diff/86400)+'d ago';}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}

var cachedListings=[],cachedReviews=[];
var origRender=renderTab;
renderTab=function(tab,l,r){if(l)cachedListings=l;if(r)cachedReviews=r;origRender(tab,cachedListings,cachedReviews);};
window.switchTab=function(tab){activeTab=tab;document.querySelectorAll('.ptab').forEach(function(t){t.classList.toggle('active',t.id==='tab-'+tab);});renderTab(tab);};
load();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
