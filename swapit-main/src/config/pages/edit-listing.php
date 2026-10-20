<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Edit Listing';
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
.img-upload-area{border:2px dashed var(--border);border-radius:12px;padding:22px;text-align:center;cursor:pointer;transition:.15s}
.img-upload-area:hover{border-color:var(--green);background:rgba(44,110,73,.04)}
.img-preview-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:10px;margin-top:14px}
.img-prev{position:relative;height:90px;border-radius:9px;overflow:hidden;background:var(--cream)}
.img-prev img{width:100%;height:100%;object-fit:cover}
.img-rm{position:absolute;top:4px;right:4px;width:20px;height:20px;background:rgba(0,0,0,.5);color:#fff;border:none;border-radius:50%;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center}
.submit-btn{padding:13px 36px;background:var(--green);color:#fff;border:none;border-radius:12px;font-family:var(--font-display);font-size:15px;font-weight:700;cursor:pointer;transition:.15s}
.submit-btn:hover{background:var(--green2)}
.submit-btn:disabled{opacity:.6;cursor:not-allowed}
.del-btn{padding:11px 24px;background:rgba(239,68,68,.08);border:1.5px solid rgba(239,68,68,.2);color:var(--red);border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;margin-left:10px}
.form-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--red);border-radius:9px;padding:10px 14px;font-size:13.5px;margin-bottom:16px;display:none}
.status-select option[value="active"]{color:var(--green)}
.status-select option[value="inactive"]{color:var(--gray)}
.status-select option[value="sold"]{color:var(--red)}
</style>

<div class="cl-wrap">
  <h1 class="cl-title">Edit Listing</h1>
  <div class="form-error" id="editError"></div>
  <div id="editContent"><div style="text-align:center;padding:60px;color:var(--gray)">Loading…</div></div>
</div>

<script>
var listingId=new URLSearchParams(location.search).get('id');
var uploadedImages=[];
var token=localStorage.getItem('swapit_token');
if(!token)window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';

async function load(){
  if(!listingId){document.getElementById('editContent').innerHTML='<p>No listing ID.</p>';return;}
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/listings.php?id='+listingId,{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(!d.ok||!d.listing){document.getElementById('editContent').innerHTML='<p>Listing not found.</p>';return;}
    var l=d.listing;
    uploadedImages=l.images||[];
    renderForm(l);
  }catch(e){document.getElementById('editContent').innerHTML='<p>Error loading.</p>';}
}

async function renderForm(l){
  var cats=await fetch('<?= BASE_PATH ?>/src/config/api/categories.php').then(function(r){return r.json();}).catch(function(){return {ok:false,categories:[]};});
  var catOpts=(cats.categories||[]).map(function(c){return '<option value="'+c.id+'"'+(c.id==l.category_id?' selected':'')+'>'+esc(c.name)+'</option>';}).join('');
  document.getElementById('editContent').innerHTML=
    '<div class="form-card"><div class="fc-title">Item Details</div>'
      +'<div class="form-group"><label class="form-label">Title *</label><input type="text" class="form-input" id="fTitle" value="'+esc(l.title||'')+'" maxlength="120"/></div>'
      +'<div class="form-group"><label class="form-label">Description *</label><textarea class="form-textarea" id="fDesc">'+esc(l.description||'')+'</textarea></div>'
      +'<div class="form-row">'
        +'<div class="form-group"><label class="form-label">Category *</label><select class="form-select" id="fCat"><option value="">Select…</option>'+catOpts+'</select></div>'
        +'<div class="form-group"><label class="form-label">Condition</label><select class="form-select" id="fCond"><option value="new"'+(l.condition==='new'?' selected':'')+'>New</option><option value="like_new"'+(l.condition==='like_new'?' selected':'')+'>Like New</option><option value="good"'+(l.condition==='good'?' selected':'')+'>Good</option><option value="fair"'+(l.condition==='fair'?' selected':'')+'>Fair</option><option value="poor"'+(l.condition==='poor'?' selected':'')+'>Poor</option></select></div>'
      +'</div>'
    +'</div>'
    +'<div class="form-card"><div class="fc-title">Price &amp; Status</div><div class="form-row">'
      +'<div class="form-group"><label class="form-label">Price (JD)</label><input type="number" class="form-input" id="fPrice" value="'+(l.price||'')+'" min="0" step="0.5"/></div>'
      +'<div class="form-group"><label class="form-label">Status</label><select class="form-select status-select" id="fStatus"><option value="active"'+(l.status==='active'?' selected':'')+'>Active</option><option value="inactive"'+(l.status==='inactive'?' selected':'')+'>Inactive</option><option value="sold"'+(l.status==='sold'?' selected':'')+'>Sold</option></select></div>'
    +'</div></div>'
    +'<div class="form-card"><div class="fc-title">Location</div><div class="form-row">'
      +'<div class="form-group"><label class="form-label">City</label><select class="form-select" id="fCity"><option value="">Select…</option>'
        +['Amman','Zarqa','Irbid','Aqaba','Salt','Madaba','Karak','Ajloun','Jerash','Mafraq','Tafilah',"Ma'an"].map(function(c){return '<option'+(c===l.city?' selected':'')+'>'+c+'</option>';}).join('')
      +'</select></div>'
      +'<div class="form-group"><label class="form-label">Area</label><input type="text" class="form-input" id="fArea" value="'+esc(l.area||'')+'" placeholder="e.g. Abdoun…"/></div>'
    +'</div></div>'
    +'<div class="form-card"><div class="fc-title">Photos</div>'
      +'<div class="img-upload-area" onclick="document.getElementById(\'imgInput\').click()"><div style="font-size:28px">📷</div><div style="font-size:13.5px;font-weight:600;margin-top:6px">Add more photos</div></div>'
      +'<input type="file" id="imgInput" accept="image/*" multiple style="display:none" onchange="handleImages(event)"/>'
      +'<div class="img-preview-grid" id="imgPreview"></div>'
    +'</div>'
    +'<div style="display:flex;gap:0">'
      +'<button class="submit-btn" onclick="save()">Save Changes</button>'
      +'<button class="del-btn" onclick="deleteListing()">Delete Listing</button>'
    +'</div>';
  renderPreviews();
}

function handleImages(e){Array.from(e.target.files).slice(0,8-uploadedImages.length).forEach(uploadImg);}

async function uploadImg(file){
  var fd=new FormData();fd.append('image',file);
  try{var r=await fetch('<?= BASE_PATH ?>/src/config/api/upload.php',{method:'POST',headers:{Authorization:'Bearer '+token},body:fd});var d=await r.json();if(d.ok)uploadedImages.push(d.url),renderPreviews();}catch(e){}
}

function renderPreviews(){
  document.getElementById('imgPreview').innerHTML=uploadedImages.map(function(url,i){
    return '<div class="img-prev"><img src="'+esc(url)+'" alt=""/><button class="img-rm" onclick="removeImg('+i+')">✕</button></div>';
  }).join('');
}

function removeImg(i){uploadedImages.splice(i,1);renderPreviews();}

async function save(){
  var err=document.getElementById('editError');err.style.display='none';
  var body={title:document.getElementById('fTitle').value.trim(),description:document.getElementById('fDesc').value.trim(),category_id:parseInt(document.getElementById('fCat').value),condition:document.getElementById('fCond').value,city:document.getElementById('fCity').value,area:document.getElementById('fArea').value.trim(),status:document.getElementById('fStatus').value,images:uploadedImages};
  var price=parseFloat(document.getElementById('fPrice').value);if(price>=0)body.price=price;
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/listings.php?id='+listingId,{method:'PUT',headers:{'Content-Type':'application/json',Authorization:'Bearer '+token},body:JSON.stringify(body)});
    var d=await r.json();
    if(d.ok){window.location.href='<?= BASE_PATH ?>/src/config/pages/listing-detail.php?id='+listingId;}
    else{err.textContent=d.error||'Save failed.';err.style.display='block';}
  }catch(e){err.textContent='Network error.';err.style.display='block';}
}

async function deleteListing(){
  if(!confirm('Delete this listing? This cannot be undone.'))return;
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/listings.php?id='+listingId,{method:'DELETE',headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(d.ok){window.location.href='<?= BASE_PATH ?>/src/config/pages/my-listings.php';}
    else alert(d.error||'Delete failed.');
  }catch(e){alert('Network error.');}
}

function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
