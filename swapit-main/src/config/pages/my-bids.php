<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'My Bids';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.mb-wrap{max-width:900px;margin:0 auto;padding:32px}
.mb-title{font-size:24px;font-weight:800;margin-bottom:24px}
.bids-list{display:flex;flex-direction:column;gap:14px}
.bid-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:18px;display:flex;gap:16px;align-items:center;transition:.15s}
.bid-card:hover{box-shadow:var(--shadow)}
.bid-img{width:70px;height:70px;border-radius:10px;background:var(--cream);overflow:hidden;flex-shrink:0}
.bid-img img{width:100%;height:100%;object-fit:cover}
.bid-img-ph{display:flex;align-items:center;justify-content:center;height:100%;font-size:28px;color:var(--border)}
.bid-info{flex:1;min-width:0}
.bid-title{font-size:14.5px;font-weight:700;margin-bottom:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bid-title a{color:var(--dark);text-decoration:none}
.bid-title a:hover{color:var(--green)}
.bid-sub{font-size:12.5px;color:var(--gray)}
.bid-amounts{text-align:right;flex-shrink:0}
.bid-my{font-family:var(--font-display);font-size:17px;font-weight:800;color:#7C3AED}
.bid-current{font-size:12.5px;color:var(--gray);margin-top:3px}
.bid-status{display:inline-block;padding:4px 11px;border-radius:99px;font-size:12px;font-weight:600;margin-top:6px}
.bid-winning{background:rgba(34,197,94,.1);color:#15803d}
.bid-outbid{background:rgba(239,68,68,.1);color:var(--red)}
.bid-ended{background:rgba(107,114,128,.1);color:var(--gray)}
.empty-state{text-align:center;padding:80px 20px;color:var(--gray)}
.empty-icon{font-size:52px;margin-bottom:16px}
</style>

<div class="mb-wrap">
  <h1 class="mb-title">My Bids</h1>
  <div id="bidsContent"><div style="text-align:center;padding:60px;color:var(--gray)">Loading…</div></div>
</div>

<script>
var token=localStorage.getItem('swapit_token');
if(!token)window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';

async function load(){
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/bids.php?my=1',{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    var el=document.getElementById('bidsContent');
    if(d.ok&&d.bids&&d.bids.length){
      el.innerHTML='<div class="bids-list">'+d.bids.map(renderBid).join('')+'</div>';
    }else{
      el.innerHTML='<div class="empty-state"><div class="empty-icon">🔨</div><p>You haven\'t placed any bids yet.</p><a href="<?= BASE_PATH ?>/src/config/pages/browse.php?type=auction" style="color:var(--green);font-weight:600">Browse auctions →</a></div>';
    }
  }catch(e){document.getElementById('bidsContent').innerHTML='<div class="empty-state"><p>Error loading bids.</p></div>';}
}

function renderBid(b){
  var img=b.listing_image?'<img src="'+esc(b.listing_image)+'" alt="" loading="lazy"/>':'<div class="bid-img-ph">🔨</div>';
  var now=new Date(),ended=b.auction_end&&new Date(b.auction_end)<now;
  var statusHtml='';
  if(ended)statusHtml='<div class="bid-status bid-ended">Auction Ended</div>';
  else if(b.is_winning)statusHtml='<div class="bid-status bid-winning">🏆 Winning</div>';
  else statusHtml='<div class="bid-status bid-outbid">Outbid</div>';
  return '<div class="bid-card">'
    +'<div class="bid-img">'+img+'</div>'
    +'<div class="bid-info">'
      +'<div class="bid-title"><a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+b.listing_id+'">'+esc(b.listing_title||'Auction Listing')+'</a></div>'
      +'<div class="bid-sub">Bid placed '+relTime(b.created_at)+'</div>'
    +'</div>'
    +'<div class="bid-amounts">'
      +'<div class="bid-my">JD '+parseFloat(b.amount).toFixed(2)+'</div>'
      +'<div class="bid-current">Current: JD '+(b.current_bid?parseFloat(b.current_bid).toFixed(2):'—')+'</div>'
      +statusHtml
    +'</div>'
  +'</div>';
}

function relTime(dt){var d=new Date(dt),now=new Date(),diff=(now-d)/1000;if(diff<3600)return Math.floor(diff/60)+'m ago';if(diff<86400)return Math.floor(diff/3600)+'h ago';return Math.floor(diff/86400)+'d ago';}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
