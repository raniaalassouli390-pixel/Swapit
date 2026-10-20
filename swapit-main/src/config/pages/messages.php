<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Messages';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.msg-wrap{max-width:800px;margin:0 auto;padding:32px}
.msg-title{font-size:24px;font-weight:800;margin-bottom:24px}
.conv-list{display:flex;flex-direction:column;gap:2px}
.conv-item{background:#fff;border:1px solid var(--border);border-radius:12px;padding:14px 18px;display:flex;gap:14px;align-items:center;cursor:pointer;text-decoration:none;color:var(--dark);transition:.15s;margin-bottom:4px}
.conv-item:hover{border-color:var(--green);background:#FAFFF9}
.conv-item.unread{border-left:3px solid var(--green)}
.conv-av{width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green2));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:17px;flex-shrink:0;overflow:hidden}
.conv-av img{width:100%;height:100%;object-fit:cover}
.conv-info{flex:1;min-width:0}
.conv-name{font-size:14.5px;font-weight:700}
.conv-preview{font-size:13px;color:var(--gray);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px}
.conv-item.unread .conv-preview{color:var(--dark);font-weight:500}
.conv-meta{text-align:right;flex-shrink:0}
.conv-time{font-size:12px;color:var(--gray);margin-bottom:4px}
.unread-dot{width:9px;height:9px;background:var(--green);border-radius:50%;margin-left:auto}
.conv-listing-thumb{width:44px;height:44px;border-radius:8px;overflow:hidden;flex-shrink:0;background:var(--cream)}
.conv-listing-thumb img{width:100%;height:100%;object-fit:cover}
.empty-state{text-align:center;padding:80px 20px;color:var(--gray)}
.empty-icon{font-size:52px;margin-bottom:16px}
.skeleton-conv{background:#fff;border:1px solid var(--border);border-radius:12px;height:76px;margin-bottom:4px}
</style>

<div class="msg-wrap">
  <h1 class="msg-title">💬 Messages</h1>
  <div id="convList"><div class="skeleton-conv skeleton"></div><div class="skeleton-conv skeleton"></div><div class="skeleton-conv skeleton"></div></div>
</div>

<script>
var token=localStorage.getItem('swapit_token');
if(!token)window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';
var currentUser=null;try{currentUser=JSON.parse(localStorage.getItem('swapit_user'));}catch(e){}

async function load(){
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/messages.php',{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    var el=document.getElementById('convList');
    if(d.ok&&d.conversations&&d.conversations.length){
      el.innerHTML=d.conversations.map(renderConv).join('');
    }else{
      el.innerHTML='<div class="empty-state"><div class="empty-icon">💬</div><p>No messages yet.</p><a href="<?= BASE_PATH ?>/src/config/pages/browse.php" style="color:var(--green);font-weight:600">Browse listings to start a conversation</a></div>';
    }
  }catch(e){document.getElementById('convList').innerHTML='<div class="empty-state"><p>Error loading messages.</p></div>';}
}

function renderConv(c){
  var isUnread=c.unread_count>0;
  var otherUser=currentUser&&c.user1_id==currentUser.id?{id:c.user2_id,name:c.user2_name,avatar:c.user2_avatar}:{id:c.user1_id,name:c.user1_name,avatar:c.user1_avatar};
  var avHtml=otherUser.avatar?'<img src="'+esc(otherUser.avatar)+'" alt=""/>':initials(otherUser.name||'?');
  var listingThumb=c.listing_image?'<div class="conv-listing-thumb"><img src="'+esc(c.listing_image)+'" alt=""/></div>':'';
  return '<a href="<?= BASE_PATH ?>/src/config/pages/chat.php?conv_id='+c.id+'" class="conv-item'+(isUnread?' unread':'')+'">'
    +'<div class="conv-av">'+avHtml+'</div>'
    +'<div class="conv-info">'
      +'<div class="conv-name">'+esc(otherUser.name||'Unknown User')+'</div>'
      +'<div class="conv-preview">'+(c.last_message?esc(c.last_message):'Start the conversation…')+'</div>'
    +'</div>'
    +listingThumb
    +'<div class="conv-meta">'
      +'<div class="conv-time">'+relTime(c.last_message_at||c.updated_at)+'</div>'
      +(isUnread?'<div class="unread-dot"></div>':'')
    +'</div>'
  +'</a>';
}

function initials(n){return n.split(' ').map(function(p){return p[0]||'';}).join('').slice(0,2).toUpperCase();}
function relTime(dt){if(!dt)return '';var d=new Date(dt),now=new Date(),diff=(now-d)/1000;if(diff<3600)return Math.floor(diff/60)+'m';if(diff<86400)return Math.floor(diff/3600)+'h';return Math.floor(diff/86400)+'d';}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
