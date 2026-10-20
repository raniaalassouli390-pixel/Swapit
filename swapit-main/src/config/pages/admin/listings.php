<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = 'Admin — Listings';
require_once __DIR__ . '/../../includes/header.php';
?>
<style>
.admin-wrap{max-width:1160px;margin:0 auto;padding:32px}
.admin-title{font-size:24px;font-weight:800;margin-bottom:24px}
.admin-nav{display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap}
.admin-nav a{padding:9px 18px;border:1.5px solid var(--border);border-radius:10px;font-size:13.5px;font-weight:600;color:var(--dark);text-decoration:none;transition:.15s}
.admin-nav a:hover,.admin-nav a.active{border-color:var(--green);color:var(--green);background:rgba(44,110,73,.04)}
.toolbar{display:flex;gap:10px;margin-bottom:18px;align-items:center;flex-wrap:wrap}
.search-input{padding:9px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:13.5px;outline:none;min-width:220px}
.search-input:focus{border-color:var(--green)}
.filter-sel{padding:8px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:13.5px;outline:none;background:#fff}
.listings-table-wrap{background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden;overflow-x:auto}
.listings-table{width:100%;border-collapse:collapse;min-width:800px}
.listings-table th{background:var(--cream);text-align:left;padding:11px 16px;font-size:12px;font-weight:700;color:var(--gray);text-transform:uppercase;letter-spacing:.04em}
.listings-table td{padding:11px 16px;border-top:1px solid var(--border);font-size:13.5px;vertical-align:middle}
.listings-table tr:hover td{background:var(--cream)}
.lt-thumb{width:42px;height:42px;border-radius:8px;background:var(--cream);overflow:hidden;flex-shrink:0}
.lt-thumb img{width:100%;height:100%;object-fit:cover}
.lt-cell{display:flex;align-items:center;gap:10px}
.badge{display:inline-block;padding:3px 9px;border-radius:99px;font-size:11.5px;font-weight:600}
.b-active{background:rgba(34,197,94,.1);color:#15803d}
.b-inactive{background:rgba(107,114,128,.1);color:var(--gray)}
.b-sold{background:rgba(59,130,246,.1);color:#1D4ED8}
.b-removed{background:rgba(239,68,68,.1);color:var(--red)}
.type-sale{background:var(--green);color:#fff}.type-swap{background:var(--orange);color:#fff}.type-auction{background:#7C3AED;color:#fff}
.del-btn{padding:5px 12px;background:rgba(239,68,68,.08);border:1.5px solid rgba(239,68,68,.2);color:var(--red);border-radius:7px;font-size:12.5px;font-weight:600;cursor:pointer}
.del-btn:hover{background:rgba(239,68,68,.15)}
.view-btn{padding:5px 12px;background:#fff;border:1.5px solid var(--border);border-radius:7px;font-size:12.5px;font-weight:600;cursor:pointer;text-decoration:none;color:var(--dark);display:inline-block}
.view-btn:hover{border-color:var(--green)}
.pagination{display:flex;justify-content:center;gap:6px;margin-top:16px;flex-wrap:wrap}
.pg-btn{padding:7px 13px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;cursor:pointer;background:#fff;color:var(--dark);transition:.15s}
.pg-btn.active{background:var(--green);color:#fff;border-color:var(--green)}
.pg-btn:hover:not(.active){border-color:var(--green)}
</style>

<div class="admin-wrap">
  <h1 class="admin-title">Listings Management</h1>
  <div class="admin-nav">
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/dashboard.php">Dashboard</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/users.php">Users</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/listings.php" class="active">Listings</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/reports.php">Reports</a>
  </div>
  <div class="toolbar">
    <input type="text" class="search-input" id="listSearch" placeholder="Search title or seller…" oninput="debounce()"/>
    <select class="filter-sel" id="typeFilter" onchange="page=1;load()">
      <option value="">All Types</option>
      <option value="sale">For Sale</option>
      <option value="swap">Swap</option>
      <option value="auction">Auction</option>
    </select>
    <select class="filter-sel" id="statusFilter" onchange="page=1;load()">
      <option value="">All Status</option>
      <option value="active">Active</option>
      <option value="inactive">Inactive</option>
      <option value="sold">Sold</option>
    </select>
  </div>
  <div class="listings-table-wrap">
    <table class="listings-table">
      <thead><tr><th>Listing</th><th>Seller</th><th>Type</th><th>Price</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody id="listBody"><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--gray)">Loading…</td></tr></tbody>
    </table>
  </div>
  <div class="pagination" id="pagination"></div>
</div>

<script>
var token=localStorage.getItem('swapit_token');
var user=null;try{user=JSON.parse(localStorage.getItem('swapit_user'));}catch(e){}
if(!token||!user||user.role!=='admin'){window.location.href='<?= BASE_PATH ?>/src/config/pages/home.php';}
var page=1,totalPages=1;var debTimer=null;
function debounce(){clearTimeout(debTimer);debTimer=setTimeout(function(){page=1;load();},400);}

async function load(){
  var search=document.getElementById('listSearch').value.trim();
  var type=document.getElementById('typeFilter').value;
  var status=document.getElementById('statusFilter').value;
  var qp=new URLSearchParams({action:'listings',page:page,per_page:20});
  if(search)qp.set('search',search);
  if(type)qp.set('type',type);
  if(status)qp.set('status',status);
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/admin.php?'+qp.toString(),{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(!d.ok){document.getElementById('listBody').innerHTML='<tr><td colspan="7">Error.</td></tr>';return;}
    totalPages=d.total_pages||1;
    document.getElementById('listBody').innerHTML=d.listings.map(renderRow).join('')||'<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--gray)">No listings found.</td></tr>';
    renderPagination();
  }catch(e){}
}

function renderRow(l){
  var img=l.primary_image?'<img src="'+esc(l.primary_image)+'" alt="" loading="lazy"/>':'';
  var typeLabel={sale:'For Sale',swap:'Swap',auction:'Auction'};
  var typeCls={sale:'type-sale',swap:'type-swap',auction:'type-auction'};
  var statusCls={active:'b-active',inactive:'b-inactive',sold:'b-sold',removed:'b-removed'};
  var price=l.price!=null?'JD '+parseFloat(l.price).toFixed(2):(l.type==='swap'?'Swap':'—');
  return '<tr>'
    +'<td><div class="lt-cell"><div class="lt-thumb">'+img+'</div><div style="font-weight:600;font-size:13px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">'+esc(l.title||'Untitled')+'</div></div></td>'
    +'<td style="color:var(--gray);font-size:13px">'+esc(l.seller_name||'—')+'</td>'
    +'<td><span class="badge '+(typeCls[l.type]||'type-sale')+'" style="font-size:11px">'+(typeLabel[l.type]||l.type)+'</span></td>'
    +'<td style="font-weight:700;color:var(--green);font-family:var(--font-display)">'+esc(price)+'</td>'
    +'<td><span class="badge '+(statusCls[l.status]||'b-inactive')+'">'+capitalize(l.status||'—')+'</span></td>'
    +'<td style="color:var(--gray);font-size:12.5px">'+new Date(l.created_at).toLocaleDateString()+'</td>'
    +'<td style="display:flex;gap:6px;align-items:center">'
      +'<a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+l.id+'" class="view-btn" target="_blank">View</a>'
      +'<button class="del-btn" onclick="removeListing('+l.id+')">Remove</button>'
    +'</td></tr>';
}

function renderPagination(){
  var html='';
  for(var i=1;i<=Math.min(totalPages,10);i++){html+='<button class="pg-btn'+(i===page?' active':'')+'" onclick="gotoPage('+i+')">'+i+'</button>';}
  document.getElementById('pagination').innerHTML=html;
}
function gotoPage(p){page=p;load();}

async function removeListing(id){
  if(!confirm('Remove this listing? It will be hidden from search results.'))return;
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/admin.php?action=listing&id='+id,{method:'DELETE',headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(d.ok)load();else alert(d.error||'Failed.');
  }catch(e){alert('Error.');}
}

function capitalize(s){return s?s.charAt(0).toUpperCase()+s.slice(1):'';}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
