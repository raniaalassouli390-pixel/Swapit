<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Create Listing';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.cl-wrap{max-width:760px;margin:0 auto;padding:32px}
.cl-title{font-size:26px;font-weight:800;margin-bottom:28px}
.form-card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:28px;margin-bottom:20px}
.fc-title{font-size:15px;font-weight:700;margin-bottom:18px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:13px;font-weight:600;color:var(--dark);margin-bottom:6px}
.form-input,.form-select,.form-textarea{width:100%;padding:10px 13px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;color:var(--dark);outline:none;font-family:var(--font-body);background:#fff}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(44,110,73,.08)}
.form-textarea{resize:vertical;min-height:110px}
.type-pills{display:flex;gap:10px;flex-wrap:wrap}
.type-pill{padding:8px 18px;border:1.5px solid var(--border);border-radius:99px;font-size:13.5px;font-weight:600;cursor:pointer;transition:.15s;color:var(--dark);background:#fff}
.type-pill.active{border-color:var(--green);background:var(--green);color:#fff}
.img-upload-area{border:2px dashed var(--border);border-radius:12px;padding:28px;text-align:center;cursor:pointer;transition:.15s}
.img-upload-area:hover{border-color:var(--green);background:rgba(44,110,73,.04)}
.img-preview-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:10px;margin-top:14px}
.img-prev{position:relative;height:90px;border-radius:9px;overflow:hidden;background:var(--cream)}
.img-prev img{width:100%;height:100%;object-fit:cover}
.img-rm{position:absolute;top:4px;right:4px;width:20px;height:20px;background:rgba(0,0,0,.5);color:#fff;border:none;border-radius:50%;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center}
.cond-pills{display:flex;gap:8px;flex-wrap:wrap}
.cond-pill{padding:6px 13px;border:1.5px solid var(--border);border-radius:99px;font-size:13px;cursor:pointer;transition:.15s;color:var(--gray)}
.cond-pill.active{border-color:var(--green);color:var(--green);background:rgba(44,110,73,.07)}
.submit-btn{width:100%;padding:14px;background:var(--green);color:#fff;border:none;border-radius:12px;font-family:var(--font-display);font-size:16px;font-weight:700;cursor:pointer;transition:.15s;margin-top:4px}
.submit-btn:hover{background:var(--green2)}
.submit-btn:disabled{opacity:.6;cursor:not-allowed}
.form-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--red);border-radius:9px;padding:10px 14px;font-size:13.5px;margin-bottom:16px;display:none}
.auction-fields{display:none}
.auction-fields.show{display:block}
</style>

<div class="cl-wrap">
  <h1 class="cl-title">Create a New Listing</h1>
  <div class="form-error" id="clError"></div>

  <div class="form-card">
    <div class="fc-title">Listing Type</div>
    <div class="type-pills">
      <div class="type-pill active" data-type="sale" onclick="setType('sale')">🏷️ For Sale</div>
      <div class="type-pill" data-type="swap" onclick="setType('swap')">🔄 Swap</div>
      <div class="type-pill" data-type="auction" onclick="setType('auction')">🔨 Auction</div>
    </div>
  </div>

  <div class="form-card">
    <div class="fc-title">Item Details</div>
    <div class="form-group">
      <label class="form-label">Title *</label>
      <input type="text" class="form-input" id="fTitle" placeholder="e.g. iPhone 13 Pro 256GB" maxlength="120"/>
    </div>
    <div class="form-group">
      <label class="form-label">Description *</label>
      <textarea class="form-textarea" id="fDesc" placeholder="Describe your item — condition, features, reason for selling…"></textarea>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Category *</label>
        <select class="form-select" id="fCat">
          <option value="">Select category…</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Condition *</label>
        <select class="form-select" id="fCond">
          <option value="">Select…</option>
          <option value="new">New</option>
          <option value="like_new">Like New</option>
          <option value="good">Good</option>
          <option value="fair">Fair</option>
          <option value="for_parts">For Parts</option>
        </select>
      </div>
    </div>
  </div>

  <div class="form-card" id="priceCard">
    <div class="fc-title">Pricing</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Price (JD) *</label>
        <input type="number" class="form-input" id="fPrice" placeholder="0.00" min="0" step="0.5"/>
      </div>
      <div class="form-group">
        <label class="form-label">Negotiable?</label>
        <select class="form-select" id="fNeg">
          <option value="1">Yes, negotiable</option>
          <option value="0">Fixed price</option>
        </select>
      </div>
    </div>
    <div class="auction-fields" id="auctionFields">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Starting Bid (JD) *</label>
          <input type="number" class="form-input" id="fStartBid" placeholder="1.00" min="0" step="0.5"/>
        </div>
        <div class="form-group">
          <label class="form-label">Bid Increment (JD)</label>
          <input type="number" class="form-input" id="fBidInc" placeholder="1.00" min="0.5" step="0.5" value="1"/>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Auction End Date &amp; Time *</label>
        <input type="datetime-local" class="form-input" id="fEndDate"/>
      </div>
    </div>
  </div>

  <div class="form-card">
    <div class="fc-title">Location</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">City *</label>
        <select class="form-select" id="fCity">
          <option value="">Select city…</option>
          <option>Amman</option><option>Zarqa</option><option>Irbid</option>
          <option>Aqaba</option><option>Salt</option><option>Madaba</option>
          <option>Karak</option><option>Ajloun</option><option>Jerash</option>
          <option>Mafraq</option><option>Tafilah</option><option>Ma'an</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Area / Neighborhood</label>
        <input type="text" class="form-input" id="fArea" placeholder="e.g. Abdoun, Sweifieh…"/>
      </div>
    </div>
  </div>

  <div class="form-card">
    <div class="fc-title">Photos (up to 8)</div>
    <div class="img-upload-area" onclick="document.getElementById('imgInput').click()">
      <div style="font-size:36px">📷</div>
      <div style="font-size:14px;font-weight:600;margin-top:8px">Click to upload images</div>
      <div style="font-size:12.5px;color:var(--gray);margin-top:4px">JPG, PNG, WebP — max 5MB each</div>
    </div>
    <input type="file" id="imgInput" accept="image/*" multiple style="display:none" onchange="handleImages(event)"/>
    <div class="img-preview-grid" id="imgPreview"></div>
  </div>

  <button class="submit-btn" id="submitBtn" onclick="submitListing()">Publish Listing</button>
</div>

<script>
var selectedType = 'sale';
var pickedFiles  = [];
var previewUrls  = [];

(function(){
  var token = localStorage.getItem('swapit_token');
  if (!token) window.location.href = '<?= BASE_PATH ?>/src/config/pages/login.php?next=' + encodeURIComponent(location.href);
})();

function setType(t){
  selectedType = t;
  document.querySelectorAll('.type-pill').forEach(function(p){ p.classList.toggle('active', p.dataset.type === t); });
  document.getElementById('priceCard').style.display = t === 'swap' ? 'none' : 'block';
  document.getElementById('auctionFields').classList.toggle('show', t === 'auction');
}

async function loadCats(){
  try {
    var r = await fetch('<?= BASE_PATH ?>/src/config/api/categories.php');
    var d = await r.json();
    if (d.ok) {
      var sel = document.getElementById('fCat');
      d.categories.forEach(function(c){
        var o = document.createElement('option'); o.value = c.id; o.textContent = c.name; sel.appendChild(o);
      });
    }
  } catch(e) {}
}

function handleImages(e){
  var files = Array.from(e.target.files);
  var room  = 8 - pickedFiles.length;
  files.slice(0, room).forEach(function(f){
    pickedFiles.push(f);
    previewUrls.push(URL.createObjectURL(f));
  });
  renderPreviews();
  e.target.value = '';
}

function renderPreviews(){
  var grid = document.getElementById('imgPreview');
  grid.innerHTML = previewUrls.map(function(url, i){
    return '<div class="img-prev"><img src="' + url + '" alt=""/><button class="img-rm" onclick="removeImg(' + i + ')">✕</button></div>';
  }).join('');
}

function removeImg(i){
  URL.revokeObjectURL(previewUrls[i]);
  pickedFiles.splice(i, 1);
  previewUrls.splice(i, 1);
  renderPreviews();
}

function toMysqlDt(s){
  if (!s) return '';
  return s.replace('T', ' ') + (s.length === 16 ? ':00' : '');
}
function nowMysqlDt(){
  var d = new Date();
  var pad = function(n){ return String(n).padStart(2, '0'); };
  return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()) + ' ' +
         pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());
}

async function submitListing(){
  var err = document.getElementById('clError'); err.style.display = 'none';
  var title = document.getElementById('fTitle').value.trim();
  var desc  = document.getElementById('fDesc').value.trim();
  var catId = document.getElementById('fCat').value;
  var cond  = document.getElementById('fCond').value;
  var city  = document.getElementById('fCity').value;

  if (!title || !desc || !catId || !cond || !city) {
    err.textContent = 'Please fill in all required fields.'; err.style.display = 'block'; return;
  }

  var body = {
    type: selectedType,
    title: title,
    description: desc,
    category_id: parseInt(catId),
    condition: cond,
    city: city
  };

  if (selectedType === 'sale') {
    var price = parseFloat(document.getElementById('fPrice').value);
    if (!price || price <= 0) { err.textContent = 'Enter a valid price.'; err.style.display = 'block'; return; }
    body.price = price;
  }
  if (selectedType === 'auction') {
    var sb = parseFloat(document.getElementById('fStartBid').value);
    var ed = document.getElementById('fEndDate').value;
    if (!sb || sb <= 0) { err.textContent = 'Enter a valid starting bid.'; err.style.display = 'block'; return; }
    if (!ed)            { err.textContent = 'Enter an auction end date.'; err.style.display = 'block'; return; }
    body.start_price = sb;
    body.starts_at   = nowMysqlDt();
    body.ends_at     = toMysqlDt(ed);
    if (new Date(body.ends_at) <= new Date(body.starts_at)) {
      err.textContent = 'Auction end must be in the future.'; err.style.display = 'block'; return;
    }
  }

  var btn = document.getElementById('submitBtn');
  btn.disabled = true; btn.textContent = 'Publishing…';
  var token = localStorage.getItem('swapit_token');

  var listingId;
  try {
    var r = await fetch('<?= BASE_PATH ?>/src/config/api/listings.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', Authorization: 'Bearer ' + token },
      body:    JSON.stringify(body)
    });
    var d = await r.json();
    if (!d.ok || !d.id) {
      err.textContent = d.error || 'Could not publish listing.'; err.style.display = 'block';
      btn.disabled = false; btn.textContent = 'Publish Listing'; return;
    }
    listingId = d.id;
  } catch (ex) {
    err.textContent = 'Network error.'; err.style.display = 'block';
    btn.disabled = false; btn.textContent = 'Publish Listing'; return;
  }

  if (pickedFiles.length > 0) {
    btn.textContent = 'Uploading images…';
    var fd = new FormData();
    fd.append('listing_id', listingId);
    fd.append('is_primary', '1');
    pickedFiles.forEach(function(f){ fd.append('images[]', f); });
    try {
      var ur = await fetch('<?= BASE_PATH ?>/src/config/api/upload.php', {
        method:  'POST',
        headers: { Authorization: 'Bearer ' + token },
        body:    fd
      });
      var ud = await ur.json();
      if (!ud.ok) alert('Listing was created, but image upload failed: ' + (ud.error || 'unknown'));
    } catch (ex) {
      alert('Listing was created, but image upload failed (network error).');
    }
  }

  window.location.href = '<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id=' + listingId;
}

function esc(s){ var d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
loadCats();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
