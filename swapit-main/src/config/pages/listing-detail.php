<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Listing';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.detail-wrap{max-width:1100px;margin:0 auto;padding:32px}
.back-btn{display:inline-flex;align-items:center;gap:6px;font-size:13.5px;color:var(--gray);margin-bottom:22px;cursor:pointer;border:none;background:none;padding:0}
.back-btn:hover{color:var(--dark)}
.detail-grid{display:grid;grid-template-columns:1fr 380px;gap:28px}
.gallery{background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden}
.main-img{height:380px;background:var(--cream);display:flex;align-items:center;justify-content:center;font-size:72px;overflow:hidden}
.main-img img{width:100%;height:100%;object-fit:cover}
.thumbs{display:flex;gap:8px;padding:12px;overflow-x:auto}
.thumb{width:70px;height:70px;border:2px solid var(--border);border-radius:9px;overflow:hidden;cursor:pointer;flex-shrink:0;transition:.15s}
.thumb.active,.thumb:hover{border-color:var(--green)}
.thumb img{width:100%;height:100%;object-fit:cover}
.sidebar{}
.info-card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:22px;margin-bottom:16px}
.listing-type-badge{display:inline-block;padding:4px 12px;border-radius:99px;font-size:11px;font-weight:700;margin-bottom:12px}
.type-sale{background:var(--green);color:#fff}.type-swap{background:var(--orange);color:#fff}.type-auction{background:#7C3AED;color:#fff}
.listing-title{font-size:22px;font-weight:800;margin-bottom:10px;line-height:1.3}
.listing-price{font-family:var(--font-display);font-size:28px;font-weight:800;color:var(--green);margin-bottom:16px}
.listing-meta{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px}
.meta-item{font-size:13px;color:var(--gray)}
.meta-item strong{color:var(--dark);display:block;font-size:13.5px}
.btn-primary{width:100%;padding:13px;background:var(--green);color:#fff;border:none;border-radius:11px;font-family:var(--font-display);font-size:15px;font-weight:700;cursor:pointer;transition:.15s;margin-bottom:8px;display:flex;align-items:center;justify-content:center;gap:8px}
.btn-primary:hover{background:var(--green2)}
.btn-secondary{width:100%;padding:11px;background:none;border:1.5px solid var(--border);border-radius:11px;font-size:14px;font-weight:600;cursor:pointer;transition:.15s;margin-bottom:8px}
.btn-secondary:hover{border-color:var(--green);color:var(--green)}
.btn-danger{width:100%;padding:11px;background:rgba(239,68,68,.08);border:1.5px solid rgba(239,68,68,.2);color:var(--red);border-radius:11px;font-size:14px;font-weight:600;cursor:pointer}
.seller-card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:18px;display:flex;align-items:center;gap:14px;text-decoration:none;color:var(--dark);transition:.15s;margin-bottom:16px}
.seller-card:hover{border-color:var(--green)}
.seller-av{width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green2));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:18px;flex-shrink:0;overflow:hidden}
.seller-av img{width:100%;height:100%;object-fit:cover}
.seller-name{font-weight:700;font-size:15px}
.seller-rating{font-size:13px;color:var(--gray)}
.desc-card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:22px;margin-bottom:16px}
.desc-title{font-size:15px;font-weight:700;margin-bottom:12px}
.desc-text{font-size:14px;color:var(--gray);line-height:1.7;white-space:pre-wrap}
.bid-section{background:linear-gradient(135deg,#EDE9FE,#DDD6FE);border:1px solid #C4B5FD;border-radius:16px;padding:20px;margin-bottom:16px}
.bid-section h3{font-size:16px;font-weight:800;color:#5B21B6;margin-bottom:12px}
.bid-current{font-size:24px;font-weight:800;color:#5B21B6;margin-bottom:4px}
.bid-count{font-size:13px;color:#7C3AED;margin-bottom:16px}
.bid-timer{font-size:13px;color:#6D28D9;margin-bottom:14px;font-weight:600}
.bid-input-row{display:flex;gap:8px}
.bid-input{flex:1;padding:10px 12px;border:2px solid #C4B5FD;border-radius:9px;font-size:15px;color:var(--dark);outline:none}
.bid-input:focus{border-color:#7C3AED}
.bid-btn{padding:10px 20px;background:#7C3AED;color:#fff;border:none;border-radius:9px;font-weight:700;font-size:14px;cursor:pointer;white-space:nowrap}
.bid-btn:hover{background:#6D28D9}
.bid-history{margin-top:14px;font-size:13px}
.bid-row{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #DDD6FE;color:#5B21B6}
.wishlist-btn{background:none;border:none;font-size:22px;cursor:pointer;padding:0;transition:.15s}
.wishlist-btn:hover{transform:scale(1.15)}
.action-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.swap-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center}
.swap-modal.open{display:flex}
.modal-card{background:#fff;border-radius:20px;padding:28px;width:100%;max-width:480px;margin:20px}
.modal-title{font-size:18px;font-weight:800;margin-bottom:16px}
.modal-close{float:right;background:none;border:none;font-size:20px;cursor:pointer;color:var(--gray)}
.modal-input{width:100%;padding:10px 13px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;outline:none;margin-bottom:10px}
.modal-input:focus{border-color:var(--green)}
.modal-btn{width:100%;padding:12px;background:var(--orange);color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer}
.modal-btn:hover{background:#dc6903}
.report-modal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1001;align-items:center;justify-content:center;padding:20px}
.report-modal.open{display:flex}
.report-card{width:100%;max-width:480px;background:#fff;border-radius:18px;overflow:hidden;box-shadow:var(--shadow-lg)}
.report-hd{background:var(--red);color:#fff;padding:18px 20px;display:flex;align-items:center;justify-content:space-between}
.report-hd h3{font-size:17px;font-weight:800}
.report-close{background:rgba(255,255,255,.2);border:none;color:#fff;width:30px;height:30px;border-radius:50%;cursor:pointer}
.report-body{padding:20px}
.report-reasons{display:flex;flex-direction:column;gap:8px;margin:14px 0}
.report-reason{display:flex;align-items:flex-start;gap:10px;padding:11px 12px;border:1.5px solid var(--border);border-radius:10px;cursor:pointer}
.report-reason.on{border-color:var(--red);background:rgba(220,38,38,.05)}
.report-reason input{margin-top:2px}
.report-text{width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:13.5px;min-height:80px;resize:vertical;outline:none;font-family:var(--font-body)}
.report-text:focus{border-color:var(--red)}
.report-actions{display:flex;gap:10px;padding:0 20px 20px}
.report-actions button{flex:1;padding:11px;border-radius:10px;font-weight:700;cursor:pointer}
.btn-report-cancel{background:#fff;border:1.5px solid var(--border)}
.btn-report-submit{flex:2;background:var(--red);color:#fff;border:none}
@media(max-width:900px){.detail-grid{grid-template-columns:1fr}.detail-wrap{padding:20px}}
</style>

<div class="detail-wrap">
  <button class="back-btn" onclick="history.back()">← Back</button>
  <div id="detailContent">
    <div style="text-align:center;padding:60px;color:var(--gray)">Loading…</div>
  </div>
</div>

<div class="swap-modal" id="swapModal">
  <div class="modal-card">
    <button class="modal-close" onclick="closeSwap()">✕</button>
    <div class="modal-title">🔄 Propose a Swap</div>
    <p style="font-size:14px;color:var(--gray);margin-bottom:16px">Describe what you're offering in exchange.</p>
    <textarea class="modal-input" id="swapNote" rows="3" placeholder="e.g. I'll swap my iPhone 12 for this…" style="resize:vertical"></textarea>
    <button class="modal-btn" onclick="submitSwap()">Send Swap Offer</button>
  </div>
</div>

<div class="report-modal" id="reportModal" onclick="if(event.target===this)closeReport()">
  <div class="report-card">
    <div class="report-hd">
      <h3>🚩 Report Listing</h3>
      <button class="report-close" onclick="closeReport()">✕</button>
    </div>
    <div class="report-body">
      <p style="font-size:13px;color:var(--gray);margin-bottom:10px">Select a reason:</p>
      <div class="report-reasons" id="reportReasons">
        <label class="report-reason" data-type="fake_item"><input type="radio" name="reportReason" value="fake_item"/> Fake or misleading item</label>
        <label class="report-reason" data-type="fraud"><input type="radio" name="reportReason" value="fraud"/> Scam / Fraud</label>
        <label class="report-reason" data-type="spam"><input type="radio" name="reportReason" value="spam"/> Spam / Duplicate</label>
        <label class="report-reason" data-type="harassment"><input type="radio" name="reportReason" value="harassment"/> Harassment</label>
        <label class="report-reason" data-type="other"><input type="radio" name="reportReason" value="other"/> Other</label>
      </div>
      <textarea class="report-text" id="reportDesc" placeholder="Additional details (optional)"></textarea>
    </div>
    <div class="report-actions">
      <button class="btn-report-cancel" onclick="closeReport()">Cancel</button>
      <button class="btn-report-submit" onclick="submitReport()">Submit Report</button>
    </div>
  </div>
</div>

<script>
var listingId=new URLSearchParams(location.search).get('id');
var currentUser=null;
try{currentUser=JSON.parse(localStorage.getItem('swapit_user'));}catch(e){}
var listingData=null;

async function load(){
  if(!listingId){document.getElementById('detailContent').innerHTML='<div style="text-align:center;padding:60px">Invalid listing.</div>';return;}
  try{
    var token=localStorage.getItem('swapit_token')||'';
    var headers=token?{'Authorization':'Bearer '+token}:{};
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/listings.php?id='+listingId,{headers:headers});
    var d=await r.json();
    if(!d.ok||!d.listing){document.getElementById('detailContent').innerHTML='<div style="text-align:center;padding:60px">Listing not found.</div>';return;}
    listingData=d.listing;
    render(listingData);
  }catch(ex){document.getElementById('detailContent').innerHTML='<div style="text-align:center;padding:60px">Error loading listing.</div>';}
}

function render(l){
  var seller  = l.seller  || {};
  var auction = l.auction || {};
  var cat     = l.category|| {};
  var sellerId   = l.user_id || l.seller_id;
  var sellerName = seller.username || 'Unknown Seller';
  var currentBid = auction.current_bid || null;
  var auctionEnd = auction.ends_at     || null;

  var imgPaths = (l.images||[]).map(function(img){ return img.path || img; });
  var mainImg  = imgPaths.length
    ? '<img src="'+esc(imgPaths[0])+'" id="mainImgEl" alt="'+esc(l.title)+'" style="width:100%;height:100%;object-fit:contain;padding:12px;background:#f5f5f0"/>'
    : '<div style="display:flex;align-items:center;justify-content:center;height:100%;font-size:64px">📦</div>';
  var thumbsHtml = imgPaths.length>1 ? imgPaths.map(function(p,i){
    return '<div class="thumb'+(i===0?' active':'')+'" onclick="setImg(\''+esc(p)+'\',this)"><img src="'+esc(p)+'" alt="" style="width:100%;height:100%;object-fit:contain;padding:4px"/></div>';
  }).join('') : '';

  var typeLabel={sale:'For Sale',swap:'Swap',auction:'Auction'};
  var typeCls={sale:'type-sale',swap:'type-swap',auction:'type-auction'};
  var isOwner    = currentUser && currentUser.id == sellerId;
  var isLoggedIn = !!currentUser;
  var priceHtml  = '';
  if(l.type==='auction'){
    var cb = currentBid != null ? currentBid : (auction.start_price || 0);
    priceHtml = '<div class="listing-price">Current Bid: JD '+parseFloat(cb).toFixed(2)+'</div>';
  } else if(l.price != null){
    priceHtml = '<div class="listing-price">JD '+parseFloat(l.price).toFixed(2)+'</div>';
  } else {
    priceHtml = '<div class="listing-price" style="color:var(--orange)">Open to Swap</div>';
  }

  var auctionSection='';
  if(l.type==='auction'){
    var displayBid = currentBid != null ? currentBid : (auction.start_price || 0);
    auctionSection='<div class="bid-section"><h3>🔨 Live Auction</h3>'
      +'<div class="bid-current">JD '+parseFloat(displayBid).toFixed(2)+'</div>'
      +'<div class="bid-count" id="bidCount">Loading bids…</div>'
      +'<div class="bid-timer" id="bidTimer">Loading…</div>'
      +(isLoggedIn&&!isOwner?'<div class="bid-input-row"><input type="number" class="bid-input" id="bidAmt" step="0.5" min="0" placeholder="Your bid (JD)"/><button class="bid-btn" onclick="placeBid()">Bid Now</button></div>':'')
      +'<div class="bid-history" id="bidHistory"></div></div>';
  }

  var actions='';
  if(isOwner){
    actions='<a href="<?= BASE_PATH ?>/src/config/pages/edit-listing.php?id='+l.id+'" class="btn-secondary">✏️ Edit Listing</a>';
  }else if(isLoggedIn){
    if(l.type==='sale')  actions='<button class="btn-primary" onclick="contactSeller()">💬 Contact Seller</button>';
    if(l.type==='swap')  actions='<button class="btn-primary" onclick="openSwap()">🔄 Propose Swap</button><button class="btn-secondary" onclick="contactSeller()">💬 Message</button>';
    if(l.type==='auction')actions='<button class="btn-secondary" onclick="contactSeller()">💬 Message Seller</button>';
    actions+='<button class="btn-danger" onclick="openReport()">🚩 Report</button>';
  }else{
    actions='<a href="<?= BASE_PATH ?>/src/config/pages/login.php?next='+encodeURIComponent(location.href)+'" class="btn-primary">Log in to Contact</a>';
  }

  var wBtn='<button class="wishlist-btn" id="wBtn" onclick="toggleWishlist()" title="Wishlist">🤍</button>';

  document.getElementById('detailContent').innerHTML=
    '<div class="detail-grid">'
      +'<div>'
        +'<div class="gallery">'
          +'<div class="main-img">'+mainImg+'</div>'
          +(thumbsHtml?'<div class="thumbs">'+thumbsHtml+'</div>':'')
        +'</div>'
        +'<div class="desc-card" style="margin-top:16px"><div class="desc-title">Description</div><div class="desc-text">'+esc(l.description||'No description provided.')+'</div></div>'
      +'</div>'
      +'<div class="sidebar">'
        +'<div class="info-card">'
          +'<div class="action-row"><div class="listing-type-badge '+(typeCls[l.type]||'type-sale')+'">'+(typeLabel[l.type]||l.type)+'</div>'+wBtn+'</div>'
          +'<div class="listing-title">'+esc(l.title)+'</div>'
          +priceHtml
          +'<div class="listing-meta">'
            +'<div class="meta-item"><strong>'+esc(l.condition||'—')+'</strong>Condition</div>'
            +'<div class="meta-item"><strong>📍 '+esc(l.city||'Jordan')+'</strong>Location</div>'
            +'<div class="meta-item"><strong>'+esc(cat.name||'—')+'</strong>Category</div>'
            +'<div class="meta-item"><strong>'+relTime(l.created_at)+'</strong>Posted</div>'
          +'</div>'
          +actions
        +'</div>'
        +auctionSection
        +'<a href="<?= BASE_PATH ?>/src/config/pages/profile.php?id='+sellerId+'" class="seller-card">'
          +'<div class="seller-av" id="sellerAv">'+initials(sellerName)+'</div>'
          +'<div><div class="seller-name">'+esc(sellerName)+'</div>'
          +'<div class="seller-rating">⭐ '+parseFloat(seller.rating||0).toFixed(1)+' · '+esc(seller.city||'')+'</div></div>'
        +'</a>'
      +'</div>'
    +'</div>';

  if(seller.avatar){var av=document.getElementById('sellerAv');if(av)av.innerHTML='<img src="'+esc(seller.avatar)+'" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%"/>';}
  if(l.type==='auction'){loadBids();if(auctionEnd){updateTimer(auctionEnd);setInterval(function(){updateTimer(auctionEnd);},1000);}}
  checkWishlist(l.id);
}

function setImg(src,el){
  var main=document.getElementById('mainImgEl');
  if(main)main.src=src;
  document.querySelectorAll('.thumb').forEach(function(t){t.classList.remove('active');});
  el.classList.add('active');
}

async function loadBids(){
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/bids.php?listing_id='+listingId);
    var d=await r.json();
    if(d.ok){
      document.getElementById('bidCount').textContent=d.bids.length+' bid'+(d.bids.length!==1?'s':'');
      var hist=d.bids.slice(0,5).map(function(b){
        var name=(b.bidder&&b.bidder.username)||b.bidder_name||'Anonymous';
        return '<div class="bid-row"><span>'+esc(name)+'</span><span>JD '+parseFloat(b.amount).toFixed(2)+'</span></div>';
      }).join('');
      document.getElementById('bidHistory').innerHTML=hist||'<div style="color:#7C3AED;font-size:13px">No bids yet. Be first!</div>';
    }
  }catch(e){}
}

function updateTimer(endStr){
  var end=new Date(endStr),now=new Date(),diff=end-now;
  var el=document.getElementById('bidTimer');if(!el)return;
  if(diff<=0){el.textContent='Auction ended';return;}
  var h=Math.floor(diff/3600000),m=Math.floor((diff%3600000)/60000),s=Math.floor((diff%60000)/1000);
  el.textContent='Ends in: '+pad(h)+'h '+pad(m)+'m '+pad(s)+'s';
}
function pad(n){return n<10?'0'+n:n;}

async function placeBid(){
  var amt=parseFloat(document.getElementById('bidAmt').value);
  if(!amt||amt<=0)return alert('Enter a valid bid amount.');
  var token=localStorage.getItem('swapit_token');
  if(!token)return window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/bids.php',{method:'POST',headers:{'Content-Type':'application/json','Authorization':'Bearer '+token},body:JSON.stringify({listing_id:parseInt(listingId),amount:amt})});
    var d=await r.json();
    if(d.ok){alert('Bid placed successfully!');loadBids();}else{alert(d.error||'Could not place bid.');}
  }catch(e){alert('Network error.');}
}

async function checkWishlist(id){
  var token=localStorage.getItem('swapit_token');if(!token)return;
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/wishlist.php',{headers:{'Authorization':'Bearer '+token}});
    var d=await r.json();
    if(d.ok){
      var inList=d.wishlist.some(function(w){return w.listing_id==id;});
      var btn=document.getElementById('wBtn');
      if(btn)btn.textContent=inList?'❤️':'🤍';
    }
  }catch(e){}
}

async function toggleWishlist(){
  var token=localStorage.getItem('swapit_token');
  if(!token)return window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';
  var btn=document.getElementById('wBtn');
  var inList=btn&&btn.textContent==='❤️';
  try{
    var r;
    if(inList){r=await fetch('<?= BASE_PATH ?>/src/config/api/wishlist.php?listing_id='+listingId,{method:'DELETE',headers:{'Authorization':'Bearer '+token}});}
    else{r=await fetch('<?= BASE_PATH ?>/src/config/api/wishlist.php',{method:'POST',headers:{'Content-Type':'application/json','Authorization':'Bearer '+token},body:JSON.stringify({listing_id:parseInt(listingId)})});}
    var d=await r.json();
    if(d.ok&&btn)btn.textContent=inList?'🤍':'❤️';
  }catch(e){}
}

function contactSeller(){
  var token=localStorage.getItem('swapit_token');
  if(!token)return window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';
  var sellerId=listingData&&(listingData.user_id||listingData.seller_id||(listingData.seller&&listingData.seller.id));
  if(!sellerId){alert('Could not find seller for this listing.');return;}
  window.location.href='<?= BASE_PATH ?>/src/config/pages/chat.php?listing_id='+listingId+'&user_id='+sellerId;
}

function openSwap(){document.getElementById('swapModal').classList.add('open');}
function closeSwap(){document.getElementById('swapModal').classList.remove('open');}

async function submitSwap(){
  var token=localStorage.getItem('swapit_token');
  if(!token)return window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';
  var note=document.getElementById('swapNote').value.trim();
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/swaps.php',{method:'POST',headers:{'Content-Type':'application/json','Authorization':'Bearer '+token},body:JSON.stringify({listing_id:parseInt(listingId),message:note})});
    var d=await r.json();
    if(d.ok){alert('Swap offer sent!');closeSwap();}else{alert(d.error||'Could not send offer.');}
  }catch(e){alert('Network error.');}
}

document.querySelectorAll('.report-reason').forEach(function(el){
  el.addEventListener('click',function(){
    document.querySelectorAll('.report-reason').forEach(function(r){r.classList.remove('on');});
    el.classList.add('on');
    var inp=el.querySelector('input');if(inp)inp.checked=true;
  });
});

function openReport(){
  var token=localStorage.getItem('swapit_token');
  if(!token)return window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php?next='+encodeURIComponent(location.href);
  document.getElementById('reportModal').classList.add('open');
}
function closeReport(){document.getElementById('reportModal').classList.remove('open');}

async function submitReport(){
  var token=localStorage.getItem('swapit_token');
  if(!token)return window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';
  var selected=document.querySelector('input[name="reportReason"]:checked');
  if(!selected){alert('Please select a reason.');return;}
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/reports.php',{method:'POST',headers:{'Content-Type':'application/json',Authorization:'Bearer '+token},body:JSON.stringify({
      target_type:'listing',
      target_id:parseInt(listingId),
      type:selected.value,
      description:document.getElementById('reportDesc').value.trim()
    })});
    var d=await r.json();
    if(d.ok){alert('Report submitted. Thank you for helping keep SwapIt safe.');closeReport();}
    else{alert(d.error||(d.errors&&d.errors[0])||'Could not submit report.');}
  }catch(e){alert('Network error.');}
}

function initials(n){return n.split(' ').map(function(p){return p[0]||'';}).join('').slice(0,2).toUpperCase();}
function relTime(dt){var d=new Date(dt),now=new Date(),diff=(now-d)/1000;
  if(diff<60)return 'just now';if(diff<3600)return Math.floor(diff/60)+'m ago';
  if(diff<86400)return Math.floor(diff/3600)+'h ago';return Math.floor(diff/86400)+'d ago';}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}

load();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
