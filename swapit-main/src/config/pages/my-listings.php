<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'My Listings';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.ml-wrap{max-width:1060px;margin:0 auto;padding:32px}
.ml-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.ml-title{font-size:24px;font-weight:800}
.new-btn{padding:10px 20px;background:var(--green);color:#fff;border:none;border-radius:10px;font-family:var(--font-display);font-size:14px;font-weight:700;cursor:pointer;text-decoration:none;display:inline-block;transition:.15s}
.new-btn:hover{background:var(--green2)}
.filter-tabs{display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:22px}
.ftab{padding:10px 18px;font-size:13.5px;font-weight:600;color:var(--gray);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-2px;transition:.15s}
.ftab.active{color:var(--green);border-bottom-color:var(--green)}
.listings-table{background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden}
.lt-head{display:grid;grid-template-columns:2fr 1fr 1fr 1fr 80px;gap:0;background:var(--cream);padding:12px 18px;font-size:12px;font-weight:700;color:var(--gray);text-transform:uppercase;letter-spacing:.04em}
.lt-row{display:grid;grid-template-columns:2fr 1fr 1fr 1fr 80px;gap:0;padding:14px 18px;border-top:1px solid var(--border);align-items:center;transition:.15s}
.lt-row:hover{background:var(--cream)}
.lt-img-title{display:flex;align-items:center;gap:12px}
.lt-img{width:48px;height:48px;border-radius:9px;background:var(--cream);overflow:hidden;flex-shrink:0}
.lt-img img{width:100%;height:100%;object-fit:cover}
.lt-img-ph{display:flex;align-items:center;justify-content:center;height:100%;font-size:22px;color:var(--border)}
.lt-row-title{font-size:13.5px;font-weight:600;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;max-width:260px}
.lt-row-meta{font-size:12px;color:var(--gray)}
.status-badge{display:inline-block;padding:3px 10px;border-radius:99px;font-size:12px;font-weight:600}
.status-active{background:rgba(34,197,94,.1);color:#15803d}
.status-inactive{background:rgba(107,114,128,.1);color:var(--gray)}
.status-sold{background:rgba(239,68,68,.1);color:var(--red)}
.status-pending{background:rgba(245,158,11,.1);color:#B45309}
.lt-actions{display:flex;gap:6px}
.action-btn{width:30px;height:30px;display:flex;align-items:center;justify-content:center;border-radius:7px;border:none;cursor:pointer;font-size:14px;transition:.15s}
.action-btn:hover{background:var(--cream)}
.empty-state{text-align:center;padding:70px;color:var(--gray)}
.price-cell{font-family:var(--font-display);font-size:14px;font-weight:700;color:var(--green)}
</style>

<div class="ml-wrap">
  <div class="ml-hd">
    <h1 class="ml-title">My Listings</h1>
    <a href="<?= BASE_PATH ?>/src/config/pages/create-listing.php" class="new-btn">+ New Listing</a>
  </div>
  <div class="filter-tabs">
    <div class="ftab active" data-status="all" onclick="setTab('all',this)">All</div>
    <div class="ftab" data-status="active" onclick="setTab('active',this)">Active</div>
    <div class="ftab" data-status="inactive" onclick="setTab('inactive',this)">Inactive</div>
    <div class="ftab" data-status="sold" onclick="setTab('sold',this)">Sold</div>
  </div>
  <div id="mlContent"><div style="text-align:center;padding:60px;color:var(--gray)">Loading…</div></div>
</div>

<script>
var token=localStorage.getItem('swapit_token');
if(!token)window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';
var currentStatus='all';
var allListings=[];

async function load(){
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/listings.php?my=1',{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(d.ok){allListings=d.listings;renderTable();}
    else{document.getElementById('mlContent').innerHTML='<div class="empty-state">Error loading listings.</div>';}
  }catch(e){document.getElementById('mlContent').innerHTML='<div class="empty-state">Network error.</div>';}
}

function setTab(status,el){
  currentStatus=status;
  document.querySelectorAll('.ftab').forEach(function(t){t.classList.toggle('active',t.dataset.status===status);});
  renderTable();
}

function renderTable(){
  var filtered=currentStatus==='all'?allListings:allListings.filter(function(l){return l.status===currentStatus;});
  if(!filtered.length){
    document.getElementById('mlContent').innerHTML='<div class="empty-state"><div style="font-size:44px;margin-bottom:12px">📭</div><p>No listings here yet.</p><a href="<?= BASE_PATH ?>/src/config/pages/create-listing.php" class="new-btn" style="display:inline-block;margin-top:14px">+ Create one</a></div>';
    return;
  }
  var rows=filtered.map(function(l){
    var img=l.primary_image?'<img src="'+esc(l.primary_image)+'" alt="" loading="lazy"/>':'<div class="lt-img-ph">📦</div>';
    var price=l.price!=null?'JD '+parseFloat(l.price).toFixed(2):(l.type==='swap'?'Swap':'—');
    var statusCls={'active':'status-active','inactive':'status-inactive','sold':'status-sold','pending':'status-pending'};
    return '<div class="lt-row">'
      +'<div class="lt-img-title"><div class="lt-img">'+img+'</div><div>'
        +'<div class="lt-row-title">'+esc(l.title||'Untitled')+'</div>'
        +'<div class="lt-row-meta">'+capitalize(l.type)+'</div>'
      +'</div></div>'
      +'<div class="price-cell">'+esc(price)+'</div>'
      +'<div><span class="status-badge '+(statusCls[l.status]||'status-inactive')+'">'+capitalize(l.status||'inactive')+'</span></div>'
      +'<div class="lt-row-meta">'+relTime(l.created_at)+'</div>'
      +'<div class="lt-actions">'
        +'<a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+l.id+'" class="action-btn" title="View">👁</a>'
        +'<a href="<?= BASE_PATH ?>/src/config/pages/edit-listing.php?id='+l.id+'" class="action-btn" title="Edit">✏️</a>'
        +'<button class="action-btn" title="Delete" onclick="del('+l.id+')">🗑️</button>'
      +'</div>'
    +'</div>';
  }).join('');
  document.getElementById('mlContent').innerHTML='<div class="listings-table">'
    +'<div class="lt-head"><div>Listing</div><div>Price</div><div>Status</div><div>Date</div><div>Actions</div></div>'
    +rows+'</div>';
}

async function del(id){
  if(!confirm('Delete this listing?'))return;
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/listings.php?id='+id,{method:'DELETE',headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(d.ok){allListings=allListings.filter(function(l){return l.id!==id;});renderTable();}
    else alert(d.error||'Delete failed.');
  }catch(e){alert('Network error.');}
}

function capitalize(s){return s?s.charAt(0).toUpperCase()+s.slice(1):'';}
function relTime(dt){var d=new Date(dt),now=new Date(),diff=(now-d)/1000;if(diff<86400)return 'today';if(diff<604800)return Math.floor(diff/86400)+'d ago';return d.toLocaleDateString();}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
