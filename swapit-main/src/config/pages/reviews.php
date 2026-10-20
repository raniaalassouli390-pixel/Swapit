<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Leave a Review';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.rev-wrap{max-width:600px;margin:0 auto;padding:40px 32px}
.rev-title{font-size:24px;font-weight:800;margin-bottom:24px}
.form-card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:26px;margin-bottom:18px}
.fc-title{font-size:14px;font-weight:700;margin-bottom:16px}
.user-preview{display:flex;align-items:center;gap:14px;margin-bottom:20px}
.user-av{width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green2));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:18px;overflow:hidden}
.user-av img{width:100%;height:100%;object-fit:cover}
.user-name{font-size:16px;font-weight:700}
.user-sub{font-size:13px;color:var(--gray)}
.star-picker{display:flex;gap:6px;font-size:36px;margin-bottom:20px;cursor:pointer}
.star-picker span{transition:.1s;opacity:.4}
.star-picker span.active,.star-picker span:hover{opacity:1}
.star-labels{display:flex;gap:6px;font-size:11.5px;color:var(--gray);margin-top:-14px;margin-bottom:18px;padding-left:4px}
.form-group{margin-bottom:14px}
.form-label{display:block;font-size:13px;font-weight:600;color:var(--dark);margin-bottom:6px}
.form-textarea{width:100%;padding:11px 13px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;color:var(--dark);outline:none;font-family:var(--font-body);resize:vertical;min-height:100px}
.form-textarea:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(44,110,73,.08)}
.submit-btn{width:100%;padding:13px;background:var(--green);color:#fff;border:none;border-radius:11px;font-family:var(--font-display);font-size:15px;font-weight:700;cursor:pointer;transition:.15s}
.submit-btn:hover{background:var(--green2)}
.submit-btn:disabled{opacity:.6;cursor:not-allowed}
.form-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--red);border-radius:9px;padding:10px 14px;font-size:13.5px;margin-bottom:14px;display:none}
.form-success{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:#15803d;border-radius:14px;padding:22px;text-align:center;display:none}
</style>

<div class="rev-wrap">
  <h1 class="rev-title">⭐ Leave a Review</h1>
  <div class="form-error" id="revErr"></div>
  <div class="form-success" id="revSuccess">
    <div style="font-size:40px;margin-bottom:10px">✅</div>
    <div style="font-size:17px;font-weight:700;margin-bottom:6px">Review submitted!</div>
    <div style="font-size:14px;color:var(--gray)">Thank you for sharing your feedback.</div>
    <a href="javascript:history.back()" style="display:inline-block;margin-top:16px;color:var(--green);font-weight:600">← Go back</a>
  </div>
  <div id="revContent"></div>
</div>

<script>
var token=localStorage.getItem('swapit_token');
if(!token)window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';
var params=new URLSearchParams(location.search);
var revieweeId=params.get('user_id');
var listingId=params.get('listing_id');
var selectedRating=0;

async function load(){
  if(!revieweeId){document.getElementById('revContent').innerHTML='<p>Missing user_id parameter.</p>';return;}
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/users.php?id='+revieweeId,{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(!d.ok){document.getElementById('revContent').innerHTML='<p>User not found.</p>';return;}
    var u=d.user;
    var avHtml=u.avatar?'<img src="'+esc(u.avatar)+'" alt=""/>':initials(u.first_name||u.username||'?');
    document.getElementById('revContent').innerHTML='<div class="form-card">'
      +'<div class="user-preview"><div class="user-av">'+avHtml+'</div><div><div class="user-name">'+esc((u.first_name||'')+' '+(u.last_name||'')).trim()||esc(u.username)+'</div><div class="user-sub">@'+esc(u.username)+'</div></div></div>'
      +'<div class="fc-title">Your rating</div>'
      +'<div class="star-picker" id="stars">'+[1,2,3,4,5].map(function(i){return '<span data-r="'+i+'" onclick="setRating('+i+')">★</span>';}).join('')+'</div>'
      +'<div class="star-labels"><span>Terrible</span><span>Poor</span><span>OK</span><span>Good</span><span>Excellent</span></div>'
      +'<div class="form-group"><label class="form-label">Comment (optional)</label><textarea class="form-textarea" id="revComment" placeholder="Share your experience with this user…"></textarea></div>'
      +'<button class="submit-btn" onclick="submit()">Submit Review</button>'
    +'</div>';
  }catch(e){document.getElementById('revContent').innerHTML='<p>Error loading.</p>';}
}

function setRating(r){
  selectedRating=r;
  document.querySelectorAll('#stars span').forEach(function(s,i){s.classList.toggle('active',i<r);});
}

async function submit(){
  var err=document.getElementById('revErr');err.style.display='none';
  if(!selectedRating){err.textContent='Please select a star rating.';err.style.display='block';return;}
  var body={reviewee_id:parseInt(revieweeId),rating:selectedRating,comment:document.getElementById('revComment').value.trim()};
  if(listingId)body.listing_id=parseInt(listingId);
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/reviews.php',{method:'POST',headers:{'Content-Type':'application/json',Authorization:'Bearer '+token},body:JSON.stringify(body)});
    var d=await r.json();
    if(d.ok){document.getElementById('revContent').style.display='none';document.getElementById('revSuccess').style.display='block';}
    else{err.textContent=d.error||'Review failed.';err.style.display='block';}
  }catch(e){err.textContent='Network error.';err.style.display='block';}
}

function initials(n){return n.split(' ').map(function(p){return p[0]||'';}).join('').slice(0,2).toUpperCase();}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
