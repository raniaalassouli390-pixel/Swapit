<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = 'Admin — Reports';
require_once __DIR__ . '/../../includes/header.php';
?>
<style>
.admin-wrap{max-width:1060px;margin:0 auto;padding:32px}
.admin-title{font-size:24px;font-weight:800;margin-bottom:24px}
.admin-nav{display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap}
.admin-nav a{padding:9px 18px;border:1.5px solid var(--border);border-radius:10px;font-size:13.5px;font-weight:600;color:var(--dark);text-decoration:none;transition:.15s}
.admin-nav a:hover,.admin-nav a.active{border-color:var(--green);color:var(--green);background:rgba(44,110,73,.04)}
.toolbar{display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap}
.filter-sel{padding:8px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:13.5px;outline:none;background:#fff}
.reports-list{display:flex;flex-direction:column;gap:12px}
.report-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px;transition:.15s}
.report-card:hover{box-shadow:var(--shadow)}
.report-card.open-report{border-left:3px solid var(--red);padding-left:15px}
.report-hd{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px}
.report-type{font-size:13px;font-weight:700;color:var(--dark)}
.report-date{font-size:12px;color:var(--gray)}
.report-body{font-size:13.5px;color:var(--gray);line-height:1.6;margin-bottom:14px}
.report-meta{display:flex;gap:14px;flex-wrap:wrap;margin-bottom:12px}
.report-meta-item{font-size:12.5px;color:var(--gray)}
.report-meta-item strong{color:var(--dark)}
.badge{display:inline-block;padding:3px 10px;border-radius:99px;font-size:11.5px;font-weight:600}
.b-open{background:rgba(245,158,11,.1);color:#B45309}
.b-resolved{background:rgba(34,197,94,.1);color:#15803d}
.b-dismissed{background:rgba(107,114,128,.1);color:var(--gray)}
.report-actions{display:flex;gap:8px;flex-wrap:wrap}
.rep-btn{padding:7px 16px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;border:1.5px solid var(--border);background:#fff;color:var(--dark);transition:.15s}
.rep-btn:hover{border-color:var(--green);color:var(--green)}
.rep-btn-resolve{background:rgba(34,197,94,.08);border-color:rgba(34,197,94,.2);color:#15803d}
.rep-btn-dismiss{background:rgba(107,114,128,.08);border-color:rgba(107,114,128,.2);color:var(--gray)}
.empty-state{text-align:center;padding:80px 20px;color:var(--gray)}
.pagination{display:flex;justify-content:center;gap:6px;margin-top:16px;flex-wrap:wrap}
.pg-btn{padding:7px 13px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;cursor:pointer;background:#fff;color:var(--dark);transition:.15s}
.pg-btn.active{background:var(--green);color:#fff;border-color:var(--green)}
.pg-btn:hover:not(.active){border-color:var(--green)}
</style>

<div class="admin-wrap">
  <h1 class="admin-title">Reports Management</h1>
  <div class="admin-nav">
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/dashboard.php">Dashboard</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/users.php">Users</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/listings.php">Listings</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/reports.php" class="active">Reports</a>
  </div>
  <div class="toolbar">
    <select class="filter-sel" id="statusFilter" onchange="page=1;load()">
      <option value="open">Open</option>
      <option value="reviewing">Under Review</option>
      <option value="resolved">Resolved</option>
      <option value="">All</option>
    </select>
    <select class="filter-sel" id="typeFilter" onchange="page=1;load()">
      <option value="">All Types</option>
      <option value="listing">Listing</option>
      <option value="user">User</option>
    </select>
  </div>
  <div id="reportsContent"><div style="text-align:center;padding:60px;color:var(--gray)">Loading…</div></div>
  <div class="pagination" id="pagination"></div>
</div>

<script>
var token=localStorage.getItem('swapit_token');
var user=null;try{user=JSON.parse(localStorage.getItem('swapit_user'));}catch(e){}
if(!token||!user||user.role!=='admin'){window.location.href='<?= BASE_PATH ?>/src/config/pages/home.php';}
var page=1,totalPages=1;

async function load(){
  var status=document.getElementById('statusFilter').value;
  var type=document.getElementById('typeFilter').value;
  var qp=new URLSearchParams({action:'reports',page:page,per_page:15});
  if(status)qp.set('status',status);
  if(type)qp.set('report_type',type);
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/admin.php?'+qp.toString(),{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(!d.ok){document.getElementById('reportsContent').innerHTML='<div class="empty-state">Error: '+esc(d.error||'')+'</div>';return;}
    totalPages=d.total_pages||1;
    var el=document.getElementById('reportsContent');
    if(!d.reports||!d.reports.length){el.innerHTML='<div class="empty-state">✅ No reports here.</div>';return;}
    el.innerHTML='<div class="reports-list">'+d.reports.map(renderReport).join('')+'</div>';
    renderPagination();
  }catch(e){document.getElementById('reportsContent').innerHTML='<div class="empty-state">Network error.</div>';}
}

function renderReport(rep){
  var isOpen=rep.status==='open';
  var statusCls={open:'b-open',resolved:'b-resolved',reviewing:'b-dismissed'};
  var targetLink='';
  if(rep.report_type==='listing')targetLink='<a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+rep.target_id+'" target="_blank" style="color:var(--green);font-weight:600">View Listing →</a>';
  else if(rep.report_type==='user')targetLink='<a href="<?= BASE_PATH ?>/src/config/pages/profile.php?id='+rep.target_id+'" target="_blank" style="color:var(--green);font-weight:600">View User →</a>';
  return '<div class="report-card'+(isOpen?' open-report':'')+'">'
    +'<div class="report-hd">'
      +'<div>'
        +'<div class="report-type">⚠️ '+capitalize(rep.report_type||'listing')+' Report'
        +(rep.listing_title?' — '+esc(rep.listing_title):'')
        +(rep.reported_name&&!rep.listing_title?' — '+esc(rep.reported_name):'')
        +'</div>'
        +'<div style="margin-top:4px"><span class="badge '+(statusCls[rep.status]||'b-open')+'">'+capitalize(rep.status||'open')+'</span></div>'
      +'</div>'
      +'<div class="report-date">'+new Date(rep.created_at).toLocaleString()+'</div>'
    +'</div>'
    +'<div class="report-meta">'
      +'<div class="report-meta-item"><strong>Reason:</strong> '+esc(rep.reason||'—')+'</div>'
      +'<div class="report-meta-item"><strong>Reported by:</strong> '+esc(rep.reporter_name||'Anonymous')+'</div>'
      +(targetLink?'<div class="report-meta-item">'+targetLink+'</div>':'')
    +'</div>'
    +(rep.description?'<div class="report-body">'+esc(rep.description)+'</div>':'')
    +(isOpen?'<div class="report-actions">'
      +'<button class="rep-btn rep-btn-resolve" onclick="updateReport('+rep.id+',\'resolved\')">✓ Resolve</button>'
      +'<button class="rep-btn rep-btn-dismiss" onclick="updateReport('+rep.id+',\'reviewing\')">Mark Reviewing</button>'
      +(rep.report_type==='listing'?'<button class="rep-btn" onclick="removeListing('+rep.target_id+','+rep.id+')">🗑️ Remove Listing</button>':'')
    +'</div>':'')
  +'</div>';
}

async function updateReport(id,status){
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/admin.php?action=report&id='+id,{method:'PUT',headers:{'Content-Type':'application/json',Authorization:'Bearer '+token},body:JSON.stringify({status:status})});
    var d=await r.json();
    if(d.ok)load();else alert(d.error||'Failed.');
  }catch(e){alert('Error.');}
}

async function removeListing(listingId,reportId){
  if(!confirm('Remove this listing and resolve the report?'))return;
  try{
    await fetch('<?= BASE_PATH ?>/src/config/api/admin.php?action=listing&id='+listingId,{method:'DELETE',headers:{Authorization:'Bearer '+token}});
    await fetch('<?= BASE_PATH ?>/src/config/api/admin.php?action=report&id='+reportId,{method:'PUT',headers:{'Content-Type':'application/json',Authorization:'Bearer '+token},body:JSON.stringify({status:'resolved'})});
    load();
  }catch(e){alert('Error.');}
}

function renderPagination(){
  var html='';
  for(var i=1;i<=totalPages;i++){html+='<button class="pg-btn'+(i===page?' active':'')+'" onclick="gotoPage('+i+')">'+i+'</button>';}
  document.getElementById('pagination').innerHTML=html;
}
function gotoPage(p){page=p;load();}

function capitalize(s){return s?s.charAt(0).toUpperCase()+s.slice(1):'';}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
