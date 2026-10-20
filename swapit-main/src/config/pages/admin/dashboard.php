<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../../includes/header.php';
?>
<style>
.admin-wrap{max-width:1100px;margin:0 auto;padding:32px}
.admin-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px}
.admin-title{font-size:26px;font-weight:800}
.admin-badge{padding:4px 14px;background:rgba(239,68,68,.1);color:var(--red);border-radius:99px;font-size:13px;font-weight:700}
.stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px;margin-bottom:28px}
.stat-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:20px}
.stat-icon{font-size:28px;margin-bottom:10px}
.stat-num{font-family:var(--font-display);font-size:26px;font-weight:800;color:var(--dark);margin-bottom:4px}
.stat-lbl{font-size:13px;color:var(--gray)}
.stat-sub{font-size:12px;color:var(--green);margin-top:3px;font-weight:600}
.admin-section{background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden;margin-bottom:20px}
.as-hd{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)}
.as-title{font-size:15px;font-weight:700}
.as-link{font-size:13px;color:var(--green);font-weight:600;text-decoration:none}
.as-table{width:100%;border-collapse:collapse}
.as-table th{background:var(--cream);text-align:left;padding:10px 16px;font-size:12px;font-weight:700;color:var(--gray);text-transform:uppercase;letter-spacing:.04em}
.as-table td{padding:12px 16px;border-top:1px solid var(--border);font-size:13.5px}
.as-table tr:hover td{background:var(--cream)}
.user-name-cell{display:flex;align-items:center;gap:10px}
.mini-av{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green2));display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;flex-shrink:0;overflow:hidden}
.mini-av img{width:100%;height:100%;object-fit:cover}
.badge{display:inline-block;padding:3px 9px;border-radius:99px;font-size:11.5px;font-weight:600}
.b-active{background:rgba(34,197,94,.1);color:#15803d}
.b-inactive{background:rgba(107,114,128,.1);color:var(--gray)}
.b-banned{background:rgba(239,68,68,.1);color:var(--red)}
.b-admin{background:rgba(124,58,237,.1);color:#5B21B6}
.b-open{background:rgba(245,158,11,.1);color:#B45309}
.b-resolved{background:rgba(34,197,94,.1);color:#15803d}
.admin-nav{display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap}
.admin-nav a{padding:9px 18px;border:1.5px solid var(--border);border-radius:10px;font-size:13.5px;font-weight:600;color:var(--dark);text-decoration:none;transition:.15s}
.admin-nav a:hover,.admin-nav a.active{border-color:var(--green);color:var(--green);background:rgba(44,110,73,.04)}
.loading-ph{text-align:center;padding:40px;color:var(--gray)}
</style>

<div class="admin-wrap">
  <div class="admin-hd">
    <h1 class="admin-title">Admin Dashboard</h1>
    <span class="admin-badge">🔐 Admin</span>
  </div>
  <div class="admin-nav">
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/dashboard.php" class="active">Dashboard</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/users.php">Users</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/listings.php">Listings</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/reports.php">Reports</a>
  </div>
  <div id="adminContent"><div class="loading-ph">Loading…</div></div>
</div>

<script>
var token=localStorage.getItem('swapit_token');
var user=null;try{user=JSON.parse(localStorage.getItem('swapit_user'));}catch(e){}
if(!token||!user||user.role!=='admin'){alert('Admin access only.');window.location.href='<?= BASE_PATH ?>/src/config/pages/home.php';}

async function load(){
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/admin.php?action=stats',{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(!d.ok){document.getElementById('adminContent').innerHTML='<p>Access denied.</p>';return;}
    var s=d.stats;
    var statsHtml='<div class="stats-grid">'
      +'<div class="stat-card"><div class="stat-icon">👥</div><div class="stat-num">'+fmt(s.total_users)+'</div><div class="stat-lbl">Total Users</div><div class="stat-sub">+'+fmt(s.new_users_today||0)+' today</div></div>'
      +'<div class="stat-card"><div class="stat-icon">📋</div><div class="stat-num">'+fmt(s.total_listings)+'</div><div class="stat-lbl">Total Listings</div><div class="stat-sub">'+fmt(s.active_listings||0)+' active</div></div>'
      +'<div class="stat-card"><div class="stat-icon">🔄</div><div class="stat-num">'+fmt(s.total_swaps)+'</div><div class="stat-lbl">Swap Offers</div></div>'
      +'<div class="stat-card"><div class="stat-icon">🔨</div><div class="stat-num">'+fmt(s.total_bids)+'</div><div class="stat-lbl">Total Bids</div></div>'
      +'<div class="stat-card"><div class="stat-icon">⚠️</div><div class="stat-num">'+fmt(s.open_reports||0)+'</div><div class="stat-lbl">Open Reports</div></div>'
      +'<div class="stat-card"><div class="stat-icon">💬</div><div class="stat-num">'+fmt(s.total_messages||0)+'</div><div class="stat-lbl">Messages Sent</div></div>'
    +'</div>';

    var [usersRes,reportsRes]=await Promise.all([
      fetch('<?= BASE_PATH ?>/src/config/api/admin.php?action=users&per_page=5',{headers:{Authorization:'Bearer '+token}}),
      fetch('<?= BASE_PATH ?>/src/config/api/admin.php?action=reports&per_page=5',{headers:{Authorization:'Bearer '+token}})
    ]);
    var ud=await usersRes.json(),rd=await reportsRes.json();

    var usersTable='<div class="admin-section"><div class="as-hd"><div class="as-title">Recent Users</div><a href="<?= BASE_PATH ?>/src/config/pages/admin/users.php" class="as-link">View All →</a></div>'
      +'<table class="as-table"><tr><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th></tr>'
      +(ud.ok?ud.users.slice(0,5).map(function(u){
        return '<tr><td><div class="user-name-cell"><div class="mini-av">'+initials(u.username||'?')+'</div>'+esc(u.username)+'</div></td>'
          +'<td>'+esc(u.email)+'</td>'
          +'<td><span class="badge '+(u.role==='admin'?'b-admin':'')+'">'+esc(u.role||'user')+'</span></td>'
          +'<td><span class="badge '+(u.status==='active'?'b-active':(u.status==='banned'?'b-banned':'b-inactive'))+'">'+esc(u.status||'active')+'</span></td>'
          +'<td style="color:var(--gray);font-size:12.5px">'+new Date(u.created_at).toLocaleDateString()+'</td></tr>';
      }).join(''):'')
      +'</table></div>';

    var reportsTable='<div class="admin-section"><div class="as-hd"><div class="as-title">Recent Reports</div><a href="<?= BASE_PATH ?>/src/config/pages/admin/reports.php" class="as-link">View All →</a></div>'
      +'<table class="as-table"><tr><th>Type</th><th>Reason</th><th>Status</th><th>Date</th></tr>'
      +(rd.ok&&rd.reports?rd.reports.slice(0,5).map(function(rep){
        return '<tr><td>'+capitalize(rep.report_type||(rep.listing_id?'listing':'user'))+'</td>'
          +'<td>'+esc((rep.reason||rep.type||'').slice(0,60))+'</td>'
          +'<td><span class="badge '+(rep.status==='open'?'b-open':'b-resolved')+'">'+capitalize(rep.status||'open')+'</span></td>'
          +'<td style="color:var(--gray);font-size:12.5px">'+new Date(rep.created_at).toLocaleDateString()+'</td></tr>';
      }).join(''):'<tr><td colspan="4" style="text-align:center;padding:24px;color:var(--gray)">No reports</td></tr>')
      +'</table></div>';

    document.getElementById('adminContent').innerHTML=statsHtml+usersTable+reportsTable;
  }catch(e){document.getElementById('adminContent').innerHTML='<p>Error loading dashboard. '+esc(e.message)+'</p>';}
}

function fmt(n){return (n||0).toLocaleString();}
function initials(n){return n.split(' ').map(function(p){return p[0]||'';}).join('').slice(0,2).toUpperCase();}
function capitalize(s){return s?s.charAt(0).toUpperCase()+s.slice(1):'';}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
