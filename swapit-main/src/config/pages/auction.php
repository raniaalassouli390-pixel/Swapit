<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Auctions';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.auc-wrap{max-width:1060px;margin:0 auto;padding:32px}
.auc-hero{background:linear-gradient(135deg,#4C1D95,#7C3AED);color:#fff;border-radius:18px;padding:36px;margin-bottom:28px;text-align:center}
.auc-hero h1{font-size:32px;font-weight:800;margin-bottom:8px}
.auc-hero p{font-size:15px;opacity:.8}
.auc-stats{display:flex;justify-content:center;gap:40px;margin-top:20px}
.auc-stat-num{font-family:var(--font-display);font-size:22px;font-weight:800}
.auc-stat-lbl{font-size:12px;opacity:.7}
.section-title{font-size:20px;font-weight:800;margin-bottom:18px}
.auctions-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;margin-bottom:36px}
.auc-card{background:#fff;border:1px solid var(--border);border-radius:16px;overflow:hidden;transition:.18s;text-decoration:none;color:var(--dark);display:flex;flex-direction:column}
.auc-card:hover{transform:translateY(-3px);box-shadow:var(--shadow)}
.auc-img{height:185px;background:var(--cream);overflow:hidden;position:relative}
.auc-img img{width:100%;height:100%;object-fit:cover}
.auc-img-ph{display:flex;align-items:center;justify-content:center;height:100%;font-size:48px;color:var(--border)}
.auc-badge{position:absolute;top:10px;left:10px;padding:4px 12px;border-radius:99px;background:#7C3AED;color:#fff;font-size:12px;font-weight:700}
.auc-timer{position:absolute;top:10px;right:10px;background:rgba(0,0,0,.65);color:#fff;padding:4px 10px;border-radius:99px;font-size:11.5px;font-weight:600}
.auc-body{padding:14px;flex:1}
.auc-title{font-size:14px;font-weight:700;margin-bottom:8px;line-height:1.4}
.auc-bids-row{display:flex;justify-content:space-between;align-items:center}
.auc-current{font-family:var(--font-display);font-size:18px;font-weight:800;color:#7C3AED}
.auc-bids-count{font-size:12px;color:var(--gray)}
.auc-location{font-size:12px;color:var(--gray);margin-top:5px}
.ended-section{margin-top:36px}
.ended-section .section-title{color:var(--gray)}
.empty-state{text-align:center;padding:70px 20px;color:var(--gray)}
</style>

<div class="auc-wrap">
  <div class="auc-hero">
    <div style="font-size:48px;margin-bottom:12px">🔨</div>
    <h1>Live Auctions</h1>
    <p>Bid on unique items — auctions close when time runs out</p>
    <div class="auc-stats">
      <div><div class="auc-stat-num" id="statLive">—</div><div class="auc-stat-lbl">Live Auctions</div></div>
      <div><div class="auc-stat-num" id="statBidsToday">—</div><div class="auc-stat-lbl">Bids Today</div></div>
    </div>
  </div>

  <div class="section-title">🔴 Live Now</div>
  <div class="auctions-grid" id="liveGrid">
    <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--gray)">Loading…</div>
  </div>

  <div class="ended-section">
    <div class="section-title">⏹ Recently Ended</div>
    <div class="auctions-grid" id="endedGrid"></div>
  </div>
</div>

<script>
var timers={};

async function load(){
  try{
    var [liveRes,endedRes]=await Promise.all([
      fetch('<?= BASE_PATH ?>/src/config/api/listings.php?type=auction&status=active&sort=newest'),
      fetch('<?= BASE_PATH ?>/src/config/api/listings.php?type=auction&status=inactive&sort=newest&per_page=4')
    ]);
    var liveData=await liveRes.json(),endedData=await endedRes.json();
    var liveGrid=document.getElementById('liveGrid');
    if(liveData.ok&&liveData.listings.length){
      document.getElementById('statLive').textContent=liveData.listings.length;
      liveGrid.innerHTML=liveData.listings.map(renderCard).join('');
      liveData.listings.forEach(function(l){if(l.auction_end)startTimer(l.id,l.auction_end);});
    }else{
      liveGrid.innerHTML='<div class="empty-state" style="grid-column:1/-1"><p>No live auctions right now.</p><a href="<?= BASE_PATH ?>/src/config/pages/create-listing.php" style="color:#7C3AED;font-weight:600">Start an auction →</a></div>';
    }
    if(endedData.ok&&endedData.listings.length){
      document.getElementById('endedGrid').innerHTML=endedData.listings.map(renderCard).join('');
    }
  }catch(e){console.error(e);}
}

function renderCard(l){
  var img=l.primary_image?'<img src="'+esc(l.primary_image)+'" alt="" loading="lazy"/>':'<div class="auc-img-ph">🔨</div>';
  var ended=l.auction_end&&new Date(l.auction_end)<new Date();
  return '<a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+l.id+'" class="auc-card">'
    +'<div class="auc-img">'+img
    +'<div class="auc-badge">'+(ended?'Ended':'🔴 Live')+'</div>'
    +(l.auction_end&&!ended?'<div class="auc-timer" id="timer-'+l.id+'">…</div>':'')
    +'</div>'
    +'<div class="auc-body">'
      +'<div class="auc-title">'+esc(l.title||'Auction')+'</div>'
      +'<div class="auc-bids-row">'
        +'<div class="auc-current">JD '+(l.current_bid||l.starting_bid||l.price||0)+'</div>'
        +'<div class="auc-bids-count">'+(l.bids_count||0)+' bids</div>'
      +'</div>'
      +'<div class="auc-location">📍 '+esc(l.city||'Jordan')+'</div>'
    +'</div></a>';
}

function startTimer(id,endStr){
  if(timers[id])clearInterval(timers[id]);
  timers[id]=setInterval(function(){
    var el=document.getElementById('timer-'+id);if(!el){clearInterval(timers[id]);return;}
    var end=new Date(endStr),now=new Date(),diff=end-now;
    if(diff<=0){el.textContent='Ended';clearInterval(timers[id]);return;}
    var h=Math.floor(diff/3600000),m=Math.floor((diff%3600000)/60000),s=Math.floor((diff%60000)/1000);
    el.textContent=(h?h+'h ':'')+pad(m)+'m '+pad(s)+'s';
  },1000);
}

function pad(n){return n<10?'0'+n:n;}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
