<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Browse Listings';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.browse-wrap{display:flex;gap:24px;padding:28px 32px;max-width:1280px;margin:0 auto}
.filters-panel{width:250px;flex-shrink:0}
.filter-card{background:#fff;border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:16px}
.filter-title{font-size:13px;font-weight:700;color:var(--dark);margin-bottom:14px;text-transform:uppercase;letter-spacing:.04em}
.filter-group{margin-bottom:8px}
.filter-label{display:flex;align-items:center;gap:8px;font-size:13.5px;color:var(--dark);cursor:pointer;padding:4px 0}
.filter-label input{accent-color:var(--green)}
.filter-input{width:100%;padding:8px 11px;border:1.5px solid var(--border);border-radius:8px;font-size:13.5px;color:var(--dark);outline:none}
.filter-input:focus{border-color:var(--green)}
.price-row{display:flex;gap:8px;align-items:center;font-size:13px;color:var(--gray)}
.price-row input{width:80px}
.filter-btn{width:100%;padding:10px;background:var(--green);color:#fff;border:none;border-radius:9px;font-size:13.5px;font-weight:700;cursor:pointer;margin-top:10px}
.filter-btn:hover{background:var(--green2)}
.filter-clear{width:100%;padding:8px;background:none;border:1.5px solid var(--border);border-radius:9px;font-size:13px;cursor:pointer;margin-top:6px;color:var(--gray)}
.main-content{flex:1;min-width:0}
.browse-hd{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:gap}
.browse-title{font-size:20px;font-weight:800}
.browse-count{font-size:13px;color:var(--gray)}
.sort-select{padding:8px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:13.5px;color:var(--dark);outline:none;background:#fff}
.sort-select:focus{border-color:var(--green)}
.listings-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:18px}
.listing-card{background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden;transition:.18s;text-decoration:none;color:var(--dark);display:flex;flex-direction:column}
.listing-card:hover{transform:translateY(-3px);box-shadow:var(--shadow)}
.lc-img{height:170px;background:var(--cream);position:relative;overflow:hidden}
.lc-img img{width:100%;height:100%;object-fit:cover}
.lc-img-ph{display:flex;align-items:center;justify-content:center;height:100%;font-size:44px;color:var(--border)}
.lc-type{position:absolute;top:8px;left:8px;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:700}
.type-sale{background:var(--green);color:#fff}.type-swap{background:var(--orange);color:#fff}.type-auction{background:#7C3AED;color:#fff}
.lc-body{padding:12px 14px;flex:1;display:flex;flex-direction:column}
.lc-title{font-size:13.5px;font-weight:600;margin-bottom:5px;line-height:1.4;overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical}
.lc-meta{font-size:11.5px;color:var(--gray);margin-bottom:7px}
.lc-price{font-family:var(--font-display);font-size:15px;font-weight:800;color:var(--green);margin-top:auto}
.lc-price-swap{color:var(--orange)}
.load-more-wrap{text-align:center;margin-top:32px}
.load-more-btn{padding:11px 32px;border:1.5px solid var(--green);color:var(--green);background:#fff;border-radius:99px;font-size:14px;font-weight:700;cursor:pointer;transition:.15s}
.load-more-btn:hover{background:var(--green);color:#fff}
.empty-state{text-align:center;padding:80px 20px;color:var(--gray)}
.empty-icon{font-size:52px;margin-bottom:16px}
.active-filters{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px}
.filter-chip{display:inline-flex;align-items:center;gap:6px;padding:4px 11px;background:var(--cream);border:1px solid var(--border);border-radius:99px;font-size:12.5px}
.filter-chip button{background:none;border:none;cursor:pointer;font-size:14px;color:var(--gray);padding:0;line-height:1}
@media(max-width:900px){.filters-panel{display:none}.browse-wrap{padding:20px}}
</style>

<div class="browse-wrap">
  <aside class="filters-panel">
    <div class="filter-card">
      <div class="filter-title">Listing Type</div>
      <label class="filter-label"><input type="checkbox" name="type" value="sale" id="fSale"/> For Sale</label>
      <label class="filter-label"><input type="checkbox" name="type" value="swap" id="fSwap"/> Swap</label>
      <label class="filter-label"><input type="checkbox" name="type" value="auction" id="fAuction"/> Auction</label>
    </div>
    <div class="filter-card">
      <div class="filter-title">Category</div>
      <div id="catFilters"><div style="color:var(--gray);font-size:13px">Loading…</div></div>
    </div>
    <div class="filter-card">
      <div class="filter-title">Price (JD)</div>
      <div class="price-row">
        <input type="number" class="filter-input" id="fMinPrice" placeholder="Min" min="0"/>
        <span>—</span>
        <input type="number" class="filter-input" id="fMaxPrice" placeholder="Max" min="0"/>
      </div>
    </div>
    <div class="filter-card">
      <div class="filter-title">Condition</div>
      <label class="filter-label"><input type="checkbox" name="cond" value="new"/> New</label>
      <label class="filter-label"><input type="checkbox" name="cond" value="like_new"/> Like New</label>
      <label class="filter-label"><input type="checkbox" name="cond" value="good"/> Good</label>
      <label class="filter-label"><input type="checkbox" name="cond" value="fair"/> Fair</label>
      <label class="filter-label"><input type="checkbox" name="cond" value="poor"/> Poor</label>
    </div>
    <button class="filter-btn" onclick="applyFilters()">Apply Filters</button>
    <button class="filter-clear" onclick="clearFilters()">Clear All</button>
  </aside>

  <div class="main-content">
    <div class="browse-hd">
      <div>
        <div class="browse-title" id="browseTitle">All Listings</div>
        <div class="browse-count" id="browseCount"></div>
      </div>
      <select class="sort-select" id="sortSelect" onchange="applyFilters()">
        <option value="newest">Newest First</option>
        <option value="oldest">Oldest First</option>
        <option value="price_asc">Price: Low → High</option>
        <option value="price_desc">Price: High → Low</option>
      </select>
    </div>
    <div class="active-filters" id="activeFilters"></div>
    <div class="listings-grid" id="browseGrid"></div>
    <div class="load-more-wrap" id="loadMoreWrap" style="display:none">
      <button class="load-more-btn" onclick="loadMore()">Load More</button>
    </div>
  </div>
</div>

<script>
var page=1,loading=false,hasMore=false,currentParams={};
var params=new URLSearchParams(location.search);

function init(){
  var type=params.get('type');var cat=params.get('category');
  if(type){document.getElementById('f'+type.charAt(0).toUpperCase()+type.slice(1)).checked=true;}
  loadCategories();
  applyFilters();
}

async function loadCategories(){
  try{var r=await fetch('<?= BASE_PATH ?>/src/config/api/categories.php');var d=await r.json();
  if(d.ok){document.getElementById('catFilters').innerHTML=d.categories.map(function(c){
    var checked=params.get('category')===c.slug?'checked':'';
    return '<label class="filter-label"><input type="checkbox" name="cat" value="'+esc(c.slug)+'" '+checked+'/> '+esc(c.name)+'</label>';
  }).join('');}
  }catch(e){}
}

function getFilterParams(){
  var p={sort:document.getElementById('sortSelect').value};
  var types=Array.from(document.querySelectorAll('input[name="type"]:checked')).map(function(i){return i.value;});
  var cats=Array.from(document.querySelectorAll('input[name="cat"]:checked')).map(function(i){return i.value;});
  var conds=Array.from(document.querySelectorAll('input[name="cond"]:checked')).map(function(i){return i.value;});
  if(types.length===1)p.type=types[0];
  if(cats.length===1)p.category=cats[0];
  if(conds.length===1)p.condition=conds[0];
  var minP=document.getElementById('fMinPrice').value;
  var maxP=document.getElementById('fMaxPrice').value;
  if(minP)p.min_price=minP;
  if(maxP)p.max_price=maxP;
  var q=params.get('q');if(q)p.search=q;
  return p;
}

function applyFilters(){page=1;currentParams=getFilterParams();fetchListings(true);}

function clearFilters(){
  document.querySelectorAll('input[type=checkbox]').forEach(function(i){i.checked=false;});
  document.getElementById('fMinPrice').value='';
  document.getElementById('fMaxPrice').value='';
  applyFilters();
}

async function fetchListings(reset){
  if(loading)return;loading=true;
  if(reset)document.getElementById('browseGrid').innerHTML='<div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--gray)">Loading…</div>';
  try{
    var qp=new URLSearchParams(Object.assign({},currentParams,{page:page,per_page:16}));
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/listings.php?'+qp.toString());
    var d=await r.json();
    if(d.ok){
      var grid=document.getElementById('browseGrid');
      if(reset)grid.innerHTML='';
      if(!d.listings.length&&reset){
        grid.innerHTML='<div class="empty-state" style="grid-column:1/-1"><div class="empty-icon">🔍</div><p>No listings found. Try different filters.</p></div>';
      }else{
        d.listings.forEach(function(l){grid.innerHTML+=renderCard(l);});
      }
      document.getElementById('browseCount').textContent=d.total+' listings';
      hasMore=d.page<d.total_pages;
      document.getElementById('loadMoreWrap').style.display=hasMore?'block':'none';
    }
  }catch(ex){console.error(ex);}
  loading=false;
}

function loadMore(){page++;fetchListings(false);}

function renderCard(l){
  var typeLabel={sale:'For Sale',swap:'Swap',auction:'Auction'};
  var typeCls={sale:'type-sale',swap:'type-swap',auction:'type-auction'};
  var img=l.primary_image?'<img src="'+esc(l.primary_image)+'" alt="" loading="lazy"/>':'<div class="lc-img-ph">📦</div>';
  var price=l.price!=null?'<div class="lc-price">JD '+parseFloat(l.price).toFixed(2)+'</div>':(l.type==='swap'?'<div class="lc-price lc-price-swap">Swap</div>':'');
  return '<a href="<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+l.id+'" class="listing-card">'
    +'<div class="lc-img">'+img+'<div class="lc-type '+(typeCls[l.type]||'type-sale')+'">'+(typeLabel[l.type]||l.type)+'</div></div>'
    +'<div class="lc-body"><div class="lc-title">'+esc(l.title||'Untitled')+'</div>'
    +'<div class="lc-meta">📍 '+esc(l.city||'Jordan')+'</div>'
    +price+'</div></a>';
}

function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
init();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
