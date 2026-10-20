<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Notifications';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.notif-wrap{max-width:720px;margin:0 auto;padding:32px}
.notif-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.notif-title{font-size:24px;font-weight:800}
.mark-all-btn{font-size:13px;color:var(--green);font-weight:600;background:none;border:none;cursor:pointer}
.notif-list{display:flex;flex-direction:column;gap:4px}
.notif-item{background:#fff;border:1px solid var(--border);border-radius:12px;padding:14px 18px;display:flex;gap:14px;align-items:flex-start;cursor:pointer;transition:.15s}
.notif-item:hover{background:#FAFFF9;border-color:var(--green)}
.notif-item.unread{border-left:3px solid var(--green);padding-left:15px}
.notif-icon{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.notif-icon-bid{background:rgba(124,58,237,.1)}
.notif-icon-swap{background:rgba(249,115,22,.1)}
.notif-icon-msg{background:rgba(44,110,73,.1)}
.notif-icon-review{background:rgba(245,158,11,.1)}
.notif-icon-sys{background:rgba(107,114,128,.1)}
.notif-body{flex:1}
.notif-text{font-size:14px;color:var(--dark);line-height:1.5;margin-bottom:3px}
.notif-item.unread .notif-text{font-weight:600}
.notif-time{font-size:12px;color:var(--gray)}
.notif-dot{width:8px;height:8px;background:var(--green);border-radius:50%;flex-shrink:0;margin-top:6px}
.empty-state{text-align:center;padding:80px 20px;color:var(--gray)}
.empty-icon{font-size:52px;margin-bottom:16px}
</style>

<div class="notif-wrap">
  <div class="notif-hd">
    <h1 class="notif-title">🔔 Notifications</h1>
    <button class="mark-all-btn" onclick="markAllRead()">Mark all as read</button>
  </div>
  <div id="notifList"><div style="text-align:center;padding:60px;color:var(--gray)">Loading…</div></div>
</div>

<script>
var token=localStorage.getItem('swapit_token');
if(!token)window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';
var notifData=[];

async function load(){
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/notifications.php',{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(d.ok){notifData=d.notifications||[];render();}
    else document.getElementById('notifList').innerHTML='<div class="empty-state"><p>Error loading.</p></div>';
  }catch(e){document.getElementById('notifList').innerHTML='<div class="empty-state"><p>Network error.</p></div>';}
}

function render(){
  if(!notifData.length){
    document.getElementById('notifList').innerHTML='<div class="empty-state"><div class="empty-icon">🔔</div><p>All caught up! No notifications.</p></div>';
    return;
  }
  document.getElementById('notifList').innerHTML='<div class="notif-list">'+notifData.map(renderNotif).join('')+'</div>';
}

var typeIcons={bid:'🔨',swap:'🔄',message:'💬',review:'⭐',system:'📢',outbid:'📈',accepted:'✅',rejected:'❌'};
var typeIconCls={bid:'notif-icon-bid',swap:'notif-icon-swap',message:'notif-icon-msg',review:'notif-icon-review',system:'notif-icon-sys',outbid:'notif-icon-bid',accepted:'notif-icon-swap',rejected:'notif-icon-sys'};

function renderNotif(n){
  var icon=typeIcons[n.type]||'🔔';
  var cls=typeIconCls[n.type]||'notif-icon-sys';
  return '<div class="notif-item'+(n.is_read?'':' unread')+'" onclick="openNotif('+n.id+',\''+esc(n.link||'')+'\','+n.is_read+')">'
    +'<div class="notif-icon '+cls+'">'+icon+'</div>'
    +'<div class="notif-body"><div class="notif-text">'+esc(n.message||n.body||'Notification')+'</div>'
    +'<div class="notif-time">'+relTime(n.created_at)+'</div></div>'
    +(n.is_read?'':'<div class="notif-dot"></div>')
  +'</div>';
}

async function openNotif(id,link,isRead){
  if(!isRead){
    try{await fetch('<?= BASE_PATH ?>/src/config/api/notifications.php?id='+id,{method:'PUT',headers:{Authorization:'Bearer '+token}});}catch(e){}
    var n=notifData.find(function(x){return x.id===id;});if(n)n.is_read=true;
    render();
  }
  if(link)window.location.href=link;
}

async function markAllRead(){
  try{await fetch('<?= BASE_PATH ?>/src/config/api/notifications.php?action=mark_all',{method:'PUT',headers:{Authorization:'Bearer '+token}});}catch(e){}
  notifData.forEach(function(n){n.is_read=true;});render();
}

function relTime(dt){var d=new Date(dt),now=new Date(),diff=(now-d)/1000;if(diff<60)return 'just now';if(diff<3600)return Math.floor(diff/60)+'m ago';if(diff<86400)return Math.floor(diff/3600)+'h ago';return Math.floor(diff/86400)+'d ago';}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
