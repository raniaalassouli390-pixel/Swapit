<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Home';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.hero{background:linear-gradient(135deg,var(--dark) 0%,#16213E 60%,#0F3460 100%);color:#fff;padding:70px 32px;text-align:center;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 70% 50%,rgba(44,110,73,.35) 0%,transparent 70%)}
.hero-inner{position:relative;max-width:720px;margin:0 auto}
.hero-tag{display:inline-block;background:rgba(44,110,73,.25);color:#B7E4C7;border:1px solid rgba(44,110,73,.4);padding:5px 14px;border-radius:99px;font-size:13px;font-weight:600;margin-bottom:20px}
.hero h1{font-size:48px;font-weight:800;line-height:1.1;margin-bottom:16px}
.hero h1 span{color:#52B788}
.hero p{font-size:18px;color:rgba(255,255,255,.7);margin-bottom:36px}
.hero-search{display:flex;gap:0;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.25);max-width:580px;margin:0 auto}
.hero-search input{flex:1;border:none;padding:16px 20px;font-size:15px;color:var(--dark);outline:none;font-family:var(--font-body)}
.hero-search button{padding:14px 28px;background:var(--green);color:#fff;border:none;font-family:var(--font-display);font-weight:700;font-size:15px;cursor:pointer;transition:.15s}
.hero-search button:hover{background:var(--green2)}
.stats-bar{display:flex;justify-content:center;gap:48px;padding:28px 32px;background:#fff;border-bottom:1px solid var(--border)}
.stat-item{text-align:center}
.stat-num{font-family:var(--font-display);font-size:22px;font-weight:800;color:var(--green)}
.stat-lbl{font-size:12.5px;color:var(--gray)}
.section{padding:56px 32px}
.section-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:28px}
.section-title{font-size:24px;font-weight:800}
.section-link{font-size:13.5px;color:var(--green);font-weight:600}
.cats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:14px}
.cat-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:20px 14px;text-align:center;cursor:pointer;transition:.18s;text-decoration:none;color:var(--dark)}
.cat-card:hover{border-color:var(--green);transform:translateY(-2px);box-shadow:var(--shadow)}
.cat-icon{font-size:32px;margin-bottom:10px}
.cat-name{font-size:13px;font-weight:600}
.cat-count{font-size:11.5px;color:var(--gray);margin-top:3px}
.listings-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:20px}
.listing-card{background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:.18s;text-decoration:none;color:var(--dark);display:flex;flex-direction:column}
.listing-card:hover{transform:translateY(-3px);box-shadow:var(--shadow)}
.lc-img{height:180px;background:var(--cream);position:relative;overflow:hidden}
.lc-img img{width:100%;height:100%;object-fit:cover}
.lc-img-placeholder{display:flex;align-items:center;justify-content:center;height:100%;font-size:48px;color:var(--border)}
.lc-type{position:absolute;top:10px;left:10px;padding:4px 10px;border-radius:99px;font-size:11px;font-weight:700;font-family:var(--font-display)}
.type-sale{background:var(--green);color:#fff}.type-swap{background:var(--orange);color:#fff}.type-auction{background:#7C3AED;color:#fff}
.lc-body{padding:14px;flex:1;display:flex;flex-direction:column}
.lc-title{font-size:14px;font-weight:600;margin-bottom:6px;line-height:1.4;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.lc-meta{font-size:12px;color:var(--gray);display:flex;gap:10px;margin-bottom:8px}
.lc-price{font-family:var(--font-display);font-size:16px;font-weight:800;color:var(--green);margin-top:auto}
.lc-price-swap{color:var(--orange)}
.skeleton{background:linear-gradient(90deg,var(--border) 25%,#f0ece6 50%,var(--border) 75%);background-size:200% 100%;animation:shimmer 1.2s infinite}
.skeleton-card{background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden}
.sk-img{height:180px}.sk-body{padding:14px}
.sk-line{height:14px;border-radius:6px;margin-bottom:8px}
.sk-line-sm{height:12px;border-radius:6px;width:60%}
.hero-type-pills{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:24px}
.type-pill{padding:8px 18px;border-radius:99px;border:1px solid rgba(255,255,255,.25);color:rgba(255,255,255,.8);font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:.15s}
.type-pill:hover{background:rgba(255,255,255,.15);color:#fff}
@media(max-width:768px){.hero h1{font-size:32px}.stats-bar{gap:28px}.section{padding:40px 20px}}
</style>

<div class="hero">
  <div class="hero-inner">
    <div class="hero-tag">🇯🇴 Jordan's #1 Marketplace</div>
    <h1>Buy, Sell &amp; <span>Swap</span><br/>Anything in Jordan</h1>
    <p>Thousands of listings updated daily. Find great deals or turn your items into cash.</p>
    <div class="hero-search">
      <input type="text" id="heroSearch" placeholder="Search for phones, clothes, cars…" onkeydown="if(event.key==='Enter')heroSearchGo()"/>
      <button onclick="heroSearchGo()">Search</button>
    </div>
    <div class="hero-type-pills">
      <a href="<?= BASE_PATH ?>/src/config/pages/browse.php?type=sale" class="type-pill">🏷️ For Sale</a>
      <a href="<?= BASE_PATH ?>/src/config/pages/browse.php?type=swap" class="type-pill">🔄 Swap</a>
      <a href="<?= BASE_PATH ?>/src/config/pages/browse.php?type=auction" class="type-pill">🔨 Auction</a>
    </div>
  </div>
</div>

<div class="stats-bar" id="statsBar">
  <div class="stat-item"><div class="stat-num" id="statListings">—</div><div class="stat-lbl">Active Listings</div></div>
  <div class="stat-item"><div class="stat-num" id="statSwaps">—</div><div class="stat-lbl">Swaps Completed</div></div>
  <div class="stat-item"><div class="stat-num" id="statBids">—</div><div class="stat-lbl">Bids Placed</div></div>
  <div class="stat-item"><div class="stat-num" id="statUsers">—</div><div class="stat-lbl">Members</div></div>
</div>

<div class="section">
  <div class="container">
    <div class="section-hd">
      <h2 class="section-title">Browse Categories</h2>
      <a href="<?= BASE_PATH ?>/src/config/pages/browse.php" class="section-link">All categories →</a>
    </div>
    <div class="cats-grid" id="catsGrid">
      <div class="cat-card skeleton-card" style="height:110px"></div>
      <div class="cat-card skeleton-card" style="height:110px"></div>
      <div class="cat-card skeleton-card" style="height:110px"></div>
      <div class="cat-card skeleton-card" style="height:110px"></div>
      <div class="cat-card skeleton-card" style="height:110px"></div>
      <div class="cat-card skeleton-card" style="height:110px"></div>
    </div>
  </div>
</div>

<div class="section" style="background:#fff;padding-top:48px;padding-bottom:48px">
  <div class="container">
    <div class="section-hd">
      <h2 class="section-title">Latest Listings</h2>
      <a href="<?= BASE_PATH ?>/src/config/pages/browse.php" class="section-link">View all →</a>
    </div>
    <div class="listings-grid" id="featuredGrid">
      <?php for($i=0;$i<8;$i++): ?>
      <div class="skeleton-card">
        <div class="sk-img skeleton"></div>
        <div class="sk-body">
          <div class="sk-line skeleton" style="width:80%"></div>
          <div class="sk-line skeleton" style="width:55%"></div>
          <div class="sk-line-sm skeleton"></div>
        </div>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</div>

<div class="section">
  <div class="container">
    <div class="section-hd">
      <h2 class="section-title">🔨 Live Auctions</h2>
      <a href="<?= BASE_PATH ?>/src/config/pages/browse.php?type=auction" class="section-link">View all →</a>
    </div>
    <div class="listings-grid" id="auctionGrid">
      <?php for($i=0;$i<4;$i++): ?>
      <div class="skeleton-card">
        <div class="sk-img skeleton"></div>
        <div class="sk-body">
          <div class="sk-line skeleton" style="width:80%"></div>
          <div class="sk-line skeleton" style="width:55%"></div>
          <div class="sk-line-sm skeleton"></div>
        </div>
      </div>
      <?php endfor; ?>
    </div>
  </div>
</div>

<script>
function heroSearchGo(){var q=document.getElementById('heroSearch').value.trim();if(q)window.location.href='<?= BASE_PATH ?>/src/config/pages/search.php?q='+encodeURIComponent(q);}

const catIcons={'Electronics':'📱','Clothing':'👕','Vehicles':'🚗','Furniture':'🪑','Books':'📚','Sports':'⚽','Home':'🏠','Toys':'🧸','Others':'📦'};

async function loadHome(){
  try{
    var [listRes,catRes,statsRes]=await Promise.all([
      fetch('<?= BASE_PATH ?>/src/config/api/listings.php?sort=newest&per_page=8'),
      fetch('<?= BASE_PATH ?>/src/config/api/categories.php'),
      fetch('<?= BASE_PATH ?>/src/config/api/admin.php?action=stats').catch(function(){return {ok:false};})
    ]);
    var listData=await listRes.json();
    var catData=await catRes.json();

    if(catData.ok){
      var catsEl=document.getElementById('catsGrid');
      catsEl.innerHTML=catData.categories.map(function(c){
        return '<a href="<?= BASE_PATH ?>/src/config/pages/browse.php?category='+encodeURIComponent(c.slug)+'" class="cat-card">'
          +'<div class="cat-icon">'+(catIcons[c.name]||'📦')+'</div>'
          +'<div class="cat-name">'+esc(c.name)+'</div>'
          +'</a>';
      }).join('');
    }

    if(listData.ok){
      var grid=document.getElementById('featuredGrid');
      grid.innerHTML=listData.listings.map(renderCard).join('');
    }

    var aucRes=await fetch('<?= BASE_PATH ?>/src/config/api/listings.php?type=auction&sort=newest');
    var aucData=await aucRes.json();
    if(aucData.ok){
      var ag=document.getElementById('auctionGrid');
      ag.innerHTML=aucData.listings.length?aucData.listings.slice(0,4).map(renderCard).join(''):'<p style="color:var(--gray);grid-column:1/-1">No live auctions right now.</p>';
    }
  }catch(ex){console.error(ex);}
}

function renderCard(l){
  var typeLabel={sale:'For Sale',swap:'Swap',auction:'Auction'};
  var typeCls={sale:'type-sale',swap:'type-swap',auction:'type-auction'};
  var img=l.primary_image?'<img src="'+esc(l.primary_image)+'" alt="'+esc(l.title||'')+'" loading="lazy"/>':'<div class="lc-img-placeholder">📦</div>';
  var price=l.price!=null?'<div class="lc-price">JD '+parseFloat(l.price).toFixed(2)+'</div>':(l.type==='swap'?'<div class="lc-price lc-price-swap">Swap</div>':'');
  return '<a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+l.id+'" class="listing-card">'
    +'<div class="lc-img">'+img+'<div class="lc-type '+(typeCls[l.type]||'type-sale')+'">'+(typeLabel[l.type]||l.type)+'</div></div>'
    +'<div class="lc-body"><div class="lc-title">'+esc(l.title||'Untitled')+'</div>'
    +'<div class="lc-meta"><span>📍 '+esc(l.city||'')+'</span></div>'
    +price+'</div></a>';
}

function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}

loadHome();

fetch('<?= BASE_PATH ?>/src/config/api/admin.php?action=stats').then(function(r){return r.json();}).then(function(d){
  if(d.ok){
    document.getElementById('statListings').textContent=d.stats.total_listings.toLocaleString();
    document.getElementById('statSwaps').textContent=d.stats.total_swaps.toLocaleString();
    document.getElementById('statBids').textContent=d.stats.total_bids.toLocaleString();
    document.getElementById('statUsers').textContent=d.stats.total_users.toLocaleString();
  }
}).catch(function(){});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
