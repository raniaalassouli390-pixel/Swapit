<?php
require_once __DIR__ . '/../config.php';
$pageTitle = 'Edit Profile';
require_once __DIR__ . '/../includes/header.php';
?>
<style>
.ep-wrap{max-width:640px;margin:0 auto;padding:32px}
.ep-title{font-size:24px;font-weight:800;margin-bottom:24px}
.form-card{background:#fff;border:1px solid var(--border);border-radius:16px;padding:26px;margin-bottom:18px}
.fc-title{font-size:14px;font-weight:700;margin-bottom:16px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.form-group{margin-bottom:14px}
.form-label{display:block;font-size:12.5px;font-weight:600;color:var(--dark);margin-bottom:5px}
.form-input,.form-select,.form-textarea{width:100%;padding:10px 13px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;color:var(--dark);outline:none;font-family:var(--font-body);background:#fff}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(44,110,73,.08)}
.form-textarea{resize:vertical;min-height:90px}
.av-section{display:flex;align-items:center;gap:18px;margin-bottom:20px}
.av-preview{width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--green2));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:28px;overflow:hidden;flex-shrink:0}
.av-preview img{width:100%;height:100%;object-fit:cover}
.av-btns{display:flex;flex-direction:column;gap:8px}
.av-upload-btn{padding:8px 14px;background:#fff;border:1.5px solid var(--border);border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;transition:.15s}
.av-upload-btn:hover{border-color:var(--green)}
.save-btn{padding:12px 36px;background:var(--green);color:#fff;border:none;border-radius:11px;font-family:var(--font-display);font-size:15px;font-weight:700;cursor:pointer;transition:.15s}
.save-btn:hover{background:var(--green2)}
.save-btn:disabled{opacity:.6;cursor:not-allowed}
.form-error{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--red);border-radius:9px;padding:10px 14px;font-size:13.5px;margin-bottom:14px;display:none}
.form-success{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:#15803d;border-radius:9px;padding:10px 14px;font-size:13.5px;margin-bottom:14px;display:none}
.pw-section{display:none}
.pw-section.open{display:block}
.toggle-link{font-size:13px;color:var(--green);font-weight:600;cursor:pointer;display:inline-block;margin-bottom:14px}
</style>

<div class="ep-wrap">
  <h1 class="ep-title">Edit Profile</h1>
  <div class="form-error" id="epError"></div>
  <div class="form-success" id="epSuccess"></div>

  <div class="form-card">
    <div class="fc-title">Profile Photo</div>
    <div class="av-section">
      <div class="av-preview" id="avPreview">?</div>
      <div class="av-btns">
        <button class="av-upload-btn" onclick="document.getElementById('avInput').click()">📷 Upload Photo</button>
        <input type="file" id="avInput" accept="image/*" style="display:none" onchange="uploadAvatar(event)"/>
        <div style="font-size:12px;color:var(--gray)">JPG, PNG — max 2MB</div>
      </div>
    </div>
  </div>

  <div class="form-card">
    <div class="fc-title">Personal Info</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">First Name</label>
        <input type="text" class="form-input" id="fFirst" placeholder="Ahmad"/>
      </div>
      <div class="form-group">
        <label class="form-label">Last Name</label>
        <input type="text" class="form-input" id="fLast" placeholder="Hassan"/>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Username</label>
      <input type="text" class="form-input" id="fUsername" placeholder="ahmad_h"/>
    </div>
    <div class="form-group">
      <label class="form-label">Bio</label>
      <textarea class="form-textarea" id="fBio" placeholder="Tell buyers a little about yourself…"></textarea>
    </div>
    <div class="form-group">
      <label class="form-label">Phone</label>
      <input type="tel" class="form-input" id="fPhone" placeholder="+962 7X XXX XXXX"/>
    </div>
    <div class="form-group">
      <label class="form-label">City</label>
      <select class="form-select" id="fCity">
        <option value="">Select city…</option>
        <?php foreach(['Amman','Zarqa','Irbid','Aqaba','Salt','Madaba','Karak','Ajloun','Jerash','Mafraq','Tafilah',"Ma'an"] as $c): ?>
        <option><?= htmlspecialchars($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="form-card">
    <div class="fc-title">Password</div>
    <span class="toggle-link" onclick="togglePw()">Change Password</span>
    <div class="pw-section" id="pwSection">
      <div class="form-group">
        <label class="form-label">Current Password</label>
        <input type="password" class="form-input" id="fOldPw" placeholder="Current password"/>
      </div>
      <div class="form-group">
        <label class="form-label">New Password</label>
        <input type="password" class="form-input" id="fNewPw" placeholder="New password (min 8 chars)"/>
      </div>
    </div>
  </div>

  <button class="save-btn" id="saveBtn" onclick="saveProfile()">Save Changes</button>
</div>

<script>
var token=localStorage.getItem('swapit_token');
var avatarUrl='';
if(!token)window.location.href='<?= BASE_PATH ?>/src/config/pages/login.php';

async function load(){
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/users.php?me=1',{headers:{Authorization:'Bearer '+token}});
    var d=await r.json();
    if(!d.ok)return;
    var u=d.user;
    document.getElementById('fFirst').value=u.first_name||'';
    document.getElementById('fLast').value=u.last_name||'';
    document.getElementById('fUsername').value=u.username||'';
    document.getElementById('fBio').value=u.bio||'';
    document.getElementById('fPhone').value=u.phone||'';
    document.getElementById('fCity').value=u.city||'';
    avatarUrl=u.avatar||'';
    var av=document.getElementById('avPreview');
    if(u.avatar)av.innerHTML='<img src="'+esc(u.avatar)+'" alt=""/>';
    else av.textContent=initials(u.first_name||u.username||'?');
  }catch(e){}
}

async function uploadAvatar(e){
  var file=e.target.files[0];if(!file)return;
  var fd=new FormData();fd.append('image',file);
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/upload.php',{method:'POST',headers:{Authorization:'Bearer '+token},body:fd});
    var d=await r.json();
    if(d.ok){avatarUrl=d.url;document.getElementById('avPreview').innerHTML='<img src="'+esc(d.url)+'" alt=""/>';}
    else alert(d.error||'Upload failed.');
  }catch(e){alert('Upload error.');}
}

function togglePw(){document.getElementById('pwSection').classList.toggle('open');}

async function saveProfile(){
  var err=document.getElementById('epError'),ok=document.getElementById('epSuccess');
  err.style.display='none';ok.style.display='none';
  var btn=document.getElementById('saveBtn');btn.disabled=true;btn.textContent='Saving…';
  var body={first_name:document.getElementById('fFirst').value.trim(),last_name:document.getElementById('fLast').value.trim(),username:document.getElementById('fUsername').value.trim(),bio:document.getElementById('fBio').value.trim(),phone:document.getElementById('fPhone').value.trim(),city:document.getElementById('fCity').value};
  if(avatarUrl)body.avatar=avatarUrl;
  var oldPw=document.getElementById('fOldPw').value,newPw=document.getElementById('fNewPw').value;
  if(oldPw&&newPw){body.old_password=oldPw;body.new_password=newPw;}
  try{
    var r=await fetch('<?= BASE_PATH ?>/src/config/api/users.php',{method:'PUT',headers:{'Content-Type':'application/json',Authorization:'Bearer '+token},body:JSON.stringify(body)});
    var d=await r.json();
    if(d.ok){
      ok.textContent='Profile saved!';ok.style.display='block';
      var stored=JSON.parse(localStorage.getItem('swapit_user')||'{}');
      stored.name=body.username;stored.avatar=body.avatar||stored.avatar;
      localStorage.setItem('swapit_user',JSON.stringify(stored));
    }else{err.textContent=d.error||'Save failed.';err.style.display='block';}
  }catch(e){err.textContent='Network error.';err.style.display='block';}
  btn.disabled=false;btn.textContent='Save Changes';
}

function initials(n){return n.split(' ').map(function(p){return p[0]||'';}).join('').slice(0,2).toUpperCase();}
function esc(s){var d=document.createElement('div');d.textContent=s||'';return d.innerHTML;}
load();
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
