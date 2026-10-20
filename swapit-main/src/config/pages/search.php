<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Search';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.search-wrap{max-width:1060px;margin:0 auto;padding:32px}
.search-bar-big{display:flex;gap:0;background:#fff;border:2px solid var(--green);border-radius:14px;overflow:hidden;margin-bottom:28px;box-shadow:0 4px 20px rgba(44,110,73,.1)}
.search-bar-big input{flex:1;border:none;padding:16px 20px;font-size:16px;color:var(--dark);outline:none;font-family:var(--font-body)}
.search-bar-big button{padding:14px 28px;background:var(--green);color:#fff;border:none;font-family:var(--font-display);font-weight:700;font-size:15px;cursor:pointer;transition:.15s}
.search-bar-big button:hover{background:var(--green2)}
.search-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px}
.search-info{font-size:15px;font-weight:700;color:var(--dark)}
.search-info span{color:var(--green)}
.sort-select{padding:8px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:13.5px;color:var(--dark);outline:none;background:#fff}
.sort-select:focus{border-color:var(--green)}
.filter-chips{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px}
.fchip{padding:6px 14px;border:1.5px solid var(--border);border-radius:99px;font-size:13px;cursor:pointer;transition:.15s;color:var(--gray);background:#fff}
.fchip.active,.fchip:hover{border-color:var(--green);color:var(--green);background:rgba(44,110,73,.05)}
.listings-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px}
.listing-card{background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:.18s;text-decoration:none;color:var(--dark);display:flex;flex-direction:column}
.listing-card:hover{transform:translateY(-3px);box-shadow:var(--shadow)}
.lc-img{height:160px;background:var(--cream);position:relative;overflow:hidden}
.lc-img img{width:100%;height:100%;object-fit:cover}
.lc-img-ph{display:flex;align-items:center;justify-content:center;height:100%;font-size:40px;color:var(--border)}
.lc-type{position:absolute;top:8px;left:8px;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:700}
.type-sale{background:var(--green);color:#fff}.type-swap{background:var(--orange);color:#fff}.type-auction{background:#7C3AED;color:#fff}
.lc-body{padding:12px 14px;flex:1;display:flex;flex-direction:column}
.lc-title{font-size:13.5px;font-weight:600;margin-bottom:5px;line-height:1.4;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.lc-meta{font-size:11.5px;color:var(--gray);margin-bottom:6px}
.lc-price{font-family:var(--font-display);font-size:15px;font-weight:800;color:var(--green);margin-top:auto}
.empty-state{text-align:center;padding:80px 20px;color:var(--gray)}
.empty-icon{font-size:52px;margin-bottom:16px}
.load-more-wrap{text-align:center;margin-top:28px}
.load-more-btn{padding:11px 32px;border:1.5px solid var(--green);color:var(--green);background:#fff;border-radius:99px;font-size:14px;font-weight:700;cursor:pointer;transition:.15s}
.load-more-btn:hover{background:var(--green);color:#fff}
.suggestion-grid{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:28px}
.suggestion-chip{padding:8px 16px;background:#fff;border:1px solid var(--border);border-radius:99px;font-size:13px;cursor:pointer;color:var(--gray);transition:.15s;text-decoration:none}
.suggestion-chip:hover{border-color:var(--green);color:var(--green)}
</style>

<div class="search-wrap">
  <div class="search-bar-big">
    <input type="text" id="searchInput" placeholder="Search listings…" onkeydown="if(event.key==='Enter')doSearch()"/>
    <button onclick="doSearch()">🔍 Search</button>
  </div>
  <div class="filter-chips">
    <div class="fchip active" data-type="" onclick="setType('',this)">All</div>
    <div class="fchip" data-type="sale" onclick="setType('sale',this)">🏷️ For Sale</div>
    <div class="fchip" data-type="swap" onclick="setType('swap',this)">🔄 Swap</div>
    <div class="fchip" data-type="auction" onclick="setType('auction',this)">🔨 Auction</div>
  </div>
  <div id="searchHd"></div>
  <div id="searchContent"></div>
</div>

<script>
var params=new URLSearchParams(location.search);
var query=params.get('q')||'';
var typeFilter='';
var page=1;var hasMore=false;var loading=false;

document.getElementById('searchInput').value=query;

function setType(t,el){
  typeFilter=t;
  document.querySelectorAll('.fchip').forEach(function(c){c.classList.toggle('active',c.dataset.type===t);});
  page=1;fetch_();
}

function doSearch(){
  query=document.getElementById('searchInput').value.trim();
  if(query)history.pushState({},'',location.pathname+'?q='+encodeURIComponent(query));
  page=1;fetch_();
}

async function fetch_(reset){
  if(loading)return;loading=true;
  if(reset===undefined)reset=true;
  if(reset)document.getElementById('searchContent').innerHTML='<div style="text-align:center;padding:40px;color:var(--gray)">Searching…</div>';
  var qp=new URLSearchParams({per_page:16,page:page});
  if(query)qp.set('search',query);
  if(typeFilter)qp.set('type',typeFilter);
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/listings.php?'+qp.toString());
    var d=await r.json();
    if(d.ok){
      var hd=document.getElementById('searchHd');
      hd.innerHTML=query?'<div class="search-hd"><div class="search-info">Results for <span>"'+esc(query)+'"</span> ('+d.total+')</div>'
        +'<select class="sort-select" onchange="sortChange(this.value)"><option value="newest">Newest</option><option value="price_asc">Price ↑</option><option value="price_desc">Price ↓</option></select></div>':'';
      var grid=reset?'':'';
      if(!d.listings.length&&reset){
        document.getElementById('searchContent').innerHTML='<div class="empty-state"><div class="empty-icon">🔍</div><p>No results found for "'+esc(query)+'"</p>'
          +'<div class="suggestion-grid" style="margin-top:20px;justify-content:center">'
          +'<a href="<?= BASE_PATH ?>/src/config/pages/browse.php" class="suggestion-chip">Browse All</a>'
          +'<a href="<?= BASE_PATH ?>/src/config/pages/browse.php?type=auction" class="suggestion-chip">🔨 Auctions</a>'
          +'<a href="<?= BASE_PATH ?>/src/config/pages/browse.php?type=swap" class="suggestion-chip">🔄 Swaps</a>'
          +'</div></div>';
      }else{
        var el=document.getElementById('searchContent');
        if(reset)el.innerHTML='<div class="listings-grid" id="resGrid">'+d.listings.map(renderCard).join('')+'</div>';
        else document.getElementById('resGrid').innerHTML+=d.listings.map(renderCard).join('');
        hasMore=d.page<d.total_pages;
        var lmEl=document.getElementById('loadMoreWrap');
        if(!lmEl){el.innerHTML+='<div class="load-more-wrap" id="loadMoreWrap"></div>';lmEl=document.getElementById('loadMoreWrap');}
        lmEl.innerHTML=hasMore?'<button class="load-more-btn" onclick="loadMore()">Load More</button>':'';
      }
    }
  }catch(ex){console.error(ex);}
  loading=false;
}

function loadMore(){page++;fetch_(false);}
function sortChange(s){document.getElementById('searchContent').innerHTML='';page=1;fetch_();}

function renderCard(l){
  var typeLabel={sale:'For Sale',swap:'Swap',auction:'Auction'};
  var typeCls={sale:'type-sale',swap:'type-swap',auction:'type-auction'};
  var img=l.primary_image?'<img src="'+esc(l.primary_image)+'" alt="" loading="lazy"/>':'<div class="lc-img-ph">📦</div>';
  var price=l.price!=null?'<div class="lc-price">JD '+parseFloat(l.price).toFixed(2)+'</div>':(l.type==='swap'?'<div class="lc-price" style="color:var(--orange)">Swap</div>':'');
  return '<a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+l.id+'" class="listing-card">'
    +'<div class="lc-img">'+img+'<div class="lc-type '+(typeCls[l.type]||'type-sale')+'">'+(typeLabel[l.type]||l.type)+'</div></div>'
    +'<div class="lc-body"><div class="lc-title">'+esc(l.title||'Untitled')+'</div>'
    +'<div class="lc-meta">📍 '+esc(l.city||'Jordan')+'</div>'
    +price+'</div></a>';
}

function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
if(query)fetch_();
else{
  document.getElementById('searchHd').innerHTML='<div class="search-hd"><div class="search-info">Popular searches:</div></div>';
  document.getElementById('searchContent').innerHTML='<div class="suggestion-grid">'
    +['iPhone','Laptop','PlayStation','Car','Furniture','Books','Camera','Clothes'].map(function(t){
      return '<a href="?q='+encodeURIComponent(t)+'" class="suggestion-chip">'+t+'</a>';
    }).join('')+'</div>';
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
