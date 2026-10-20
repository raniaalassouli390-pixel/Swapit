<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Chat';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.chat-wrap{max-width:780px;margin:0 auto;padding:24px 32px}
.chat-hd{display:flex;align-items:center;gap:12px;background:#fff;border:1px solid var(--border);border-radius:14px;padding:14px 18px;margin-bottom:16px}
.chat-back{background:none;border:none;font-size:18px;cursor:pointer;color:var(--gray);padding:0 8px 0 0}
.chat-av{width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green2));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:15px;flex-shrink:0;overflow:hidden}
.chat-av img{width:100%;height:100%;object-fit:cover}
.chat-user-info{flex:1}
.chat-user-name{font-size:15px;font-weight:700}
.chat-status{font-size:12px;color:var(--gray)}
.chat-listing-link{display:flex;align-items:center;gap:10px;background:var(--cream);border-radius:10px;padding:10px 14px;margin-bottom:14px;text-decoration:none;color:var(--dark);border:1px solid var(--border);transition:.15s}
.chat-listing-link:hover{border-color:var(--green)}
.cl-thumb{width:44px;height:44px;border-radius:8px;background:#fff;overflow:hidden;flex-shrink:0}
.cl-thumb img{width:100%;height:100%;object-fit:cover}
.cl-info{font-size:13px;font-weight:600}
.cl-price{font-size:12.5px;color:var(--green);font-weight:700}
.messages-area{height:420px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;padding:4px 0;margin-bottom:14px}
.msg-bubble-wrap{display:flex;align-items:flex-end;gap:8px}
.msg-bubble-wrap.mine{flex-direction:row-reverse}
.msg-bubble-av{width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green2));display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;flex-shrink:0}
.msg-bubble{max-width:68%;padding:10px 14px;border-radius:14px;font-size:13.5px;line-height:1.5}
.msg-bubble.mine{background:var(--green);color:#fff;border-bottom-right-radius:4px}
.msg-bubble.theirs{background:#fff;border:1px solid var(--border);color:var(--dark);border-bottom-left-radius:4px}
.msg-time{font-size:11px;opacity:.6;margin-top:3px;text-align:right}
.mine .msg-time{text-align:right}
.theirs .msg-time{text-align:left}
.chat-input-bar{display:flex;gap:10px;background:#fff;border:1px solid var(--border);border-radius:14px;padding:12px 14px;align-items:flex-end}
.chat-input{flex:1;border:none;outline:none;font-size:14px;font-family:var(--font-body);resize:none;max-height:100px;color:var(--dark)}
.send-btn{width:38px;height:38px;background:var(--green);border:none;border-radius:9px;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:.15s}
.send-btn:hover{background:var(--green2)}
.date-divider{text-align:center;font-size:12px;color:var(--gray);margin:6px 0;position:relative}
.date-divider::before{content:'';position:absolute;top:50%;left:0;right:0;height:1px;background:var(--border);z-index:0}
.date-divider span{position:relative;background:var(--bg);padding:0 12px;z-index:1}
.empty-chat{text-align:center;padding:60px 20px;color:var(--gray)}
</style>

<div class="chat-wrap">
  <div class="chat-hd">
    <button class="chat-back" onclick="history.back()">←</button>
    <div class="chat-av" id="chatAv">?</div>
    <div class="chat-user-info">
      <div class="chat-user-name" id="chatUserName">Loading…</div>
      <div class="chat-status" id="chatStatus"></div>
    </div>
  </div>
  <div id="listingBanner"></div>
  <div class="messages-area" id="messagesArea">
    <div class="empty-chat">Send a message to start the conversation</div>
  </div>
  <div class="chat-input-bar">
    <textarea class="chat-input" id="chatInput" placeholder="Type a message…" rows="1" onkeydown="handleKey(event)"></textarea>
    <button class="send-btn" onclick="send()">➤</button>
  </div>
</div>

<script>
var token=localStorage.getItem('swapit_token');
if(!token)window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';
var currentUser=null;try{currentUser=JSON.parse(localStorage.getItem('swapit_user'));}catch(e){}
var params=new URLSearchParams(location.search);
var convId=params.get('conv_id');
var otherUserId=params.get('user_id');
var listingId=params.get('listing_id');
if(otherUserId==='undefined'||otherUserId==='null'||otherUserId==='')otherUserId=null;
if(otherUserId)otherUserId=parseInt(otherUserId,10);
if(otherUserId!==null&&isNaN(otherUserId))otherUserId=null;
if(listingId)listingId=parseInt(listingId,10);
if(convId)convId=parseInt(convId,10);
var convData=null;
var pollInterval=null;

async function load(){
  if(convId){
    await loadConv(convId);
  }else if(otherUserId||listingId){
    try{
      var r=await fetch('<?= BASE_PATH ?>/src/config/api/messages.php',{headers:{Authorization:'Bearer '+token}});
      var d=await r.json();
      if(d.ok){
        var existing=d.conversations&&d.conversations.find(function(c){
          return (c.user1_id==otherUserId||c.user2_id==otherUserId)||(listingId&&c.listing_id==listingId);
        });
        if(existing){convId=existing.id;await loadConv(convId);}
        else{await initNewConv();}
      }else{await initNewConv();}
    }catch(e){await initNewConv();}
  }
  pollInterval=setInterval(function(){if(convId)loadMessages();},8000);
}

async function initNewConv(){
  if(!otherUserId&&listingId){
    try{
      var lr=await fetch('<?= BASE_PATH ?>/src/config/api/listings.php?id='+listingId);
      var ld=await lr.json();
      if(ld.ok&&ld.listing){
        otherUserId=ld.listing.user_id||(ld.listing.seller&&ld.listing.seller.id)||null;
      }
    }catch(e){}
  }
  if(otherUserId){
    try{
      var r=await fetch('<?= BASE_PATH ?>/src/config/api/users.php?id='+otherUserId,{headers:{Authorization:'Bearer '+token}});
      var d=await r.json();
      if(d.ok){setHeader(d.user);}
    }catch(e){}
  }
  if(listingId){await loadListingBanner(listingId);}
  document.getElementById('messagesArea').innerHTML='<div class="empty-chat">Start the conversation below 👇</div>';
}

async function loadConv(cid){
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/messages.php?conv_id='+cid,{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(d.ok){
      convData=d;
      var other=currentUser&&d.conversation.user1_id==currentUser.id?{id:d.conversation.user2_id,name:d.conversation.user2_name,avatar:d.conversation.user2_avatar}:{id:d.conversation.user1_id,name:d.conversation.user1_name,avatar:d.conversation.user1_avatar};
      setHeader(other);
      if(d.conversation.listing_id)await loadListingBanner(d.conversation.listing_id);
      renderMessages(d.messages||[]);
    }
  }catch(e){}
}

async function loadMessages(){
  if(!convId)return;
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/messages.php?conv_id='+convId,{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(d.ok)renderMessages(d.messages||[]);
  }catch(e){}
}

function setHeader(u){
  var av=document.getElementById('chatAv');
  if(u.avatar)av.innerHTML='<img src="'+esc(u.avatar)+'" alt=""/>';
  else av.textContent=initials(u.name||u.username||u.first_name||'?');
  document.getElementById('chatUserName').textContent=u.name||u.username||(u.first_name+' '+(u.last_name||'')).trim()||'User';
}

async function loadListingBanner(lid){
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/listings.php?id='+lid);
    var d=await r.json();
    if(d.ok&&d.listing){
      var l=d.listing;
      var img=l.primary_image?'<div class="cl-thumb"><img src="'+esc(l.primary_image)+'" alt=""/></div>':'';
      document.getElementById('listingBanner').innerHTML='<a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+l.id+'" class="chat-listing-link">'
        +img+'<div><div class="cl-info">'+esc(l.title||'Listing')+'</div>'
        +'<div class="cl-price">'+(l.price!=null?'JD '+parseFloat(l.price).toFixed(2):(l.type==='swap'?'Swap':'—'))+'</div></div></a>';
    }
  }catch(e){}
}

function renderMessages(msgs){
  if(!msgs.length){document.getElementById('messagesArea').innerHTML='<div class="empty-chat">No messages yet. Say hello! 👋</div>';return;}
  var html='';var lastDate='';
  msgs.forEach(function(m){
    var d=new Date(m.created_at),dateStr=d.toLocaleDateString();
    if(dateStr!==lastDate){html+='<div class="date-divider"><span>'+dateStr+'</span></div>';lastDate=dateStr;}
    var isMine=currentUser&&m.sender_id==currentUser.id;
    html+='<div class="msg-bubble-wrap '+(isMine?'mine':'theirs')+'">'
      +'<div class="msg-bubble-av">'+initials(m.sender_name||'?')+'</div>'
      +'<div class="msg-bubble '+(isMine?'mine':'theirs')+'">'+esc(m.body||m.content||m.message||'')+
        '<div class="msg-time">'+d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'})+'</div>'
      +'</div></div>';
  });
  var area=document.getElementById('messagesArea');area.innerHTML=html;
  area.scrollTop=area.scrollHeight;
}

async function send(){
  var input=document.getElementById('chatInput');
  var text=input.value.trim();if(!text)return;
  input.value='';input.style.height='';
  var body={body:text};
  if(convId)body.conv_id=convId;
  if(otherUserId)body.receiver_id=otherUserId;
  if(listingId)body.listing_id=listingId;
  if(!body.conv_id&&!body.receiver_id){alert('Missing recipient. Open chat from a listing or messages.');return;}
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/messages.php',{method:'POST',headers:{'Content-Type':'application/json',Authorization:'Bearer '+token},body:JSON.stringify(body)});
    var d=await r.json();
    if(d.ok){if(!convId&&d.conv_id){convId=d.conv_id;}loadMessages();}
    else alert(d.error||'Send failed.');
  }catch(e){alert('Network error.');}
}

function handleKey(e){if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();send();}}
function initials(n){return (n||'?').split(' ').map(function(p){return p[0]||'';}).join('').slice(0,2).toUpperCase();}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
