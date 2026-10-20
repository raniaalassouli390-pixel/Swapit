<?php
require_once __DIR__ . '/../../config.php';
$pageTitle = 'Admin — Users';
require_once __DIR__ . '/../../includes/header.php';
?>
<style>
.admin-wrap{max-width:1100px;margin:0 auto;padding:32px}
.admin-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.admin-title{font-size:24px;font-weight:800}
.admin-nav{display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap}
.admin-nav a{padding:9px 18px;border:1.5px solid var(--border);border-radius:10px;font-size:13.5px;font-weight:600;color:var(--dark);text-decoration:none;transition:.15s}
.admin-nav a:hover,.admin-nav a.active{border-color:var(--green);color:var(--green);background:rgba(44,110,73,.04)}
.toolbar{display:flex;gap:10px;margin-bottom:18px;align-items:center;flex-wrap:wrap}
.search-input{padding:9px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:13.5px;outline:none;min-width:220px}
.search-input:focus{border-color:var(--green)}
.filter-sel{padding:8px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:13.5px;outline:none;background:#fff}
.filter-sel:focus{border-color:var(--green)}
.users-table-wrap{background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden}
.users-table{width:100%;border-collapse:collapse}
.users-table th{background:var(--cream);text-align:left;padding:11px 16px;font-size:12px;font-weight:700;color:var(--gray);text-transform:uppercase;letter-spacing:.04em}
.users-table td{padding:12px 16px;border-top:1px solid var(--border);font-size:13.5px;vertical-align:middle}
.users-table tr:hover td{background:var(--cream)}
.user-cell{display:flex;align-items:center;gap:10px}
.mini-av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green2));display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0;overflow:hidden}
.mini-av img{width:100%;height:100%;object-fit:cover}
.badge{display:inline-block;padding:3px 9px;border-radius:99px;font-size:11.5px;font-weight:600}
.b-active{background:rgba(34,197,94,.1);color:#15803d}
.b-inactive{background:rgba(107,114,128,.1);color:var(--gray)}
.b-banned{background:rgba(239,68,68,.1);color:var(--red)}
.b-admin{background:rgba(124,58,237,.1);color:#5B21B6}
.row-actions{display:flex;gap:6px}
.btn-mini{padding:5px 11px;border:1.5px solid var(--border);border-radius:7px;font-size:12.5px;font-weight:600;cursor:pointer;background:#fff;color:var(--dark);transition:.15s}
.btn-mini:hover{border-color:var(--green);color:var(--green)}
.btn-mini.danger:hover{border-color:var(--red);color:var(--red);background:rgba(239,68,68,.05)}
.modal-mask{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center;padding:20px}
.modal-mask.open{display:flex}
.modal-card{background:#fff;border-radius:18px;padding:26px 28px;width:100%;max-width:480px;max-height:90vh;overflow-y:auto}
.modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
.modal-title{font-size:18px;font-weight:800}
.modal-close{background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray)}
.field{margin-bottom:14px}
.field label{display:block;font-size:12.5px;font-weight:600;color:var(--dark);margin-bottom:6px}
.field input,.field select{width:100%;padding:9px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:13.5px;outline:none;background:#fff}
.field input:focus,.field select:focus{border-color:var(--green)}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.modal-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:18px}
.btn-cancel{padding:9px 16px;border:1.5px solid var(--border);background:#fff;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer}
.btn-save{padding:9px 18px;background:var(--green);color:#fff;border:none;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer}
.btn-save:hover{background:var(--green2)}
.btn-save:disabled{opacity:.6;cursor:not-allowed}
.modal-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--red);border-radius:9px;padding:9px 12px;font-size:13px;margin-bottom:12px;display:none}
.pagination{display:flex;justify-content:center;gap:6px;margin-top:16px;flex-wrap:wrap}
.pg-btn{padding:7px 13px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;cursor:pointer;background:#fff;color:var(--dark);transition:.15s}
.pg-btn.active{background:var(--green);color:#fff;border-color:var(--green)}
.pg-btn:hover:not(.active){border-color:var(--green)}
.pg-btn:disabled{opacity:.4;cursor:not-allowed}
</style>

<div class="admin-wrap">
  <div class="admin-hd">
    <h1 class="admin-title">User Management</h1>
  </div>
  <div class="admin-nav">
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/dashboard.php">Dashboard</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/users.php" class="active">Users</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/listings.php">Listings</a>
    <a href="<?= BASE_PATH ?>/src/config/pages/admin/reports.php">Reports</a>
  </div>
  <div class="toolbar">
    <input type="text" class="search-input" id="userSearch" placeholder="Search username or email…" oninput="debounce()"/>
    <select class="filter-sel" id="roleFilter" onchange="page=1;load()">
      <option value="">All Roles</option>
      <option value="user">User</option>
      <option value="admin">Admin</option>
    </select>
    <select class="filter-sel" id="statusFilter" onchange="page=1;load()">
      <option value="">All Status</option>
      <option value="active">Active</option>
      <option value="suspended">Suspended</option>
      <option value="unverified">Unverified</option>
    </select>
  </div>
  <div class="users-table-wrap">
    <table class="users-table">
      <thead><tr><th>User</th><th>Email</th><th>Role</th><th>Status</th><th>Listings</th><th>Joined</th><th>Action</th></tr></thead>
      <tbody id="usersBody"><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--gray)">Loading…</td></tr></tbody>
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
  var search=document.getElementById('userSearch').value.trim();
  var role=document.getElementById('roleFilter').value;
  var status=document.getElementById('statusFilter').value;
  var qp=new URLSearchParams({action:'users',page:page,per_page:20});
  if(search)qp.set('search',search);
  if(role)qp.set('role',role);
  if(status)qp.set('status',status);
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/admin.php?'+qp.toString(),{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(!d.ok){document.getElementById('usersBody').innerHTML='<tr><td colspan="7">Error: '+esc(d.error||'')+'</td></tr>';return;}
    totalPages=d.pages||1;
    document.getElementById('usersBody').innerHTML=d.users.map(renderRow).join('')||'<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--gray)">No users found.</td></tr>';
    renderPagination();
  }catch(e){}
}

var usersById = {};

function renderRow(u){
  usersById[u.id] = u;
  var avHtml=u.avatar?'<img src="'+esc(u.avatar)+'" alt=""/>':initials(u.username||'?');
  var roleCls=u.role==='admin'?'b-admin':'';
  var statusCls=u.status==='active'?'b-active':(u.status==='suspended'?'b-banned':'b-inactive');
  var statusLabel=u.status==='suspended'?'Deactivated':capitalize(u.status||'active');
  var isSelf = user && user.id == u.id;
  return '<tr><td><div class="user-cell"><div class="mini-av">'+avHtml+'</div><div><div style="font-weight:600">'+esc(u.username||'—')+'</div><div style="font-size:12px;color:var(--gray)">'+esc((u.first_name||'')+' '+(u.last_name||'')).trim()+'</div></div></div></td>'
    +'<td>'+esc(u.email)+'</td>'
    +'<td><span class="badge '+roleCls+'">'+esc(u.role||'user')+'</span></td>'
    +'<td><span class="badge '+statusCls+'">'+statusLabel+'</span></td>'
    +'<td style="color:var(--gray)">'+fmt(u.listing_count||0)+'</td>'
    +'<td style="color:var(--gray);font-size:12.5px">'+new Date(u.created_at).toLocaleDateString()+'</td>'
    +'<td><div class="row-actions">'
      +'<button class="btn-mini" onclick="openEdit('+u.id+')">Edit</button>'
      +(isSelf ? '' : '<button class="btn-mini danger" onclick="deleteUser('+u.id+')">Delete</button>')
    +'</div></td></tr>';
}

function renderPagination(){
  var html='';
  for(var i=1;i<=totalPages;i++){html+='<button class="pg-btn'+(i===page?' active':'')+'" onclick="gotoPage('+i+')">'+i+'</button>';}
  document.getElementById('pagination').innerHTML=html;
}
function gotoPage(p){page=p;load();}

var editingId = null;

function openEdit(id){
  var u = usersById[id]; if(!u) return;
  editingId = id;
  document.getElementById('editError').style.display = 'none';
  document.getElementById('editUsername').value   = u.username || '';
  document.getElementById('editEmail').value      = u.email || '';
  document.getElementById('editFirstName').value  = u.first_name || '';
  document.getElementById('editLastName').value   = u.last_name || '';
  document.getElementById('editRole').value       = u.role || 'user';
  document.getElementById('editStatus').value     = u.status || 'active';
  document.getElementById('editPassword').value = '';
  var isSelf = user && user.id == id;
  document.getElementById('editRole').disabled    = isSelf;
  document.getElementById('editStatus').disabled  = isSelf;
  document.getElementById('editSelfNote').style.display = isSelf ? 'block' : 'none';
  document.getElementById('editModal').classList.add('open');
}
function closeEdit(){
  document.getElementById('editModal').classList.remove('open');
  editingId = null;
}
async function saveEdit(){
  if(!editingId) return;
  var btn = document.getElementById('editSaveBtn');
  var err = document.getElementById('editError');
  err.style.display='none'; btn.disabled=true; btn.textContent='Saving…';
  var u = usersById[editingId];
  var isSelf = user && user.id == editingId;
  var body = {
    username:   document.getElementById('editUsername').value.trim(),
    email:      document.getElementById('editEmail').value.trim(),
    first_name: document.getElementById('editFirstName').value.trim(),
    last_name:  document.getElementById('editLastName').value.trim()
  };
  if (!isSelf) {
    body.role   = document.getElementById('editRole').value;
    body.status = document.getElementById('editStatus').value;
  }
  var newPassword = document.getElementById('editPassword').value;
  if (newPassword) body.password = newPassword;
  try{
    var r = await fetch('<?= BASE_PATH ?>/src/config/api/admin.php?action=user&id='+editingId,{method:'PUT',headers:{'Content-Type':'application/json',Authorization:'Bearer '+token},body:JSON.stringify(body)});
    var d = await r.json();
    if (d.ok) { closeEdit(); load(); }
    else      { err.textContent = d.error || 'Update failed.'; err.style.display='block'; }
  }catch(e){ err.textContent = 'Network error.'; err.style.display='block'; }
  finally{ btn.disabled=false; btn.textContent='Save changes'; }
}

async function deleteUser(id){
  var u = usersById[id];
  var label = u ? (u.username || u.email || ('user #'+id)) : ('user #'+id);
  if (!confirm('Permanently delete '+label+'?\n\nThis will also remove all their listings, bids, swaps, reviews and messages. This cannot be undone.')) return;
  try{
    var r = await fetch('<?= BASE_PATH ?>/src/config/api/admin.php?action=user&id='+id,{method:'DELETE',headers:{Authorization:'Bearer '+token}});
    var d = await r.json();
    if (d.ok) load(); else alert(d.error || 'Delete failed.');
  }catch(e){ alert('Network error.'); }
}

function initials(n){return n.split(' ').map(function(p){return p[0]||'';}).join('').slice(0,2).toUpperCase();}
function capitalize(s){return s?s.charAt(0).toUpperCase()+s.slice(1):'';}
function fmt(n){return (n||0).toLocaleString();}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<div class="modal-mask" id="editModal" onclick="if(event.target===this)closeEdit()">
  <div class="modal-card">
    <div class="modal-head">
      <div class="modal-title">Edit user</div>
      <button class="modal-close" onclick="closeEdit()">×</button>
    </div>
    <div class="modal-error" id="editError"></div>
    <div id="editSelfNote" style="display:none;background:rgba(124,58,237,.08);border:1px solid rgba(124,58,237,.2);color:#5B21B6;border-radius:9px;padding:9px 12px;font-size:12.5px;margin-bottom:12px">
      You're editing your own account — role &amp; status are locked.
    </div>
    <div class="field-row">
      <div class="field"><label>First name</label><input type="text" id="editFirstName" maxlength="50"/></div>
      <div class="field"><label>Last name</label><input type="text" id="editLastName" maxlength="50"/></div>
    </div>
    <div class="field"><label>Username</label><input type="text" id="editUsername" maxlength="50"/></div>
    <div class="field"><label>Email</label><input type="email" id="editEmail"/></div>
    <div class="field-row">
      <div class="field">
        <label>Role</label>
        <select id="editRole">
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div class="field">
        <label>Status</label>
        <select id="editStatus">
          <option value="active">Active</option>
          <option value="suspended">Deactivated</option>
          <option value="unverified">Unverified</option>
        </select>
      </div>
    </div>
    <div class="field">
      <label>New password <span style="font-weight:400;color:var(--gray)">(leave blank to keep current)</span></label>
      <input type="password" id="editPassword" minlength="8" autocomplete="new-password" placeholder="Min. 8 characters"/>
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" onclick="closeEdit()">Cancel</button>
      <button class="btn-save" id="editSaveBtn" onclick="saveEdit()">Save changes</button>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
