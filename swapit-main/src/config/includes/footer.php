<?php

?>
<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="footer-logo">🔄 SwapIt</div>
      <p>Jordan's marketplace for buying, selling, swapping &amp; auctioning.</p>
    </div>
    <div class="footer-links">
      <div>
        <h4>Explore</h4>
        <a href="<?= BASE_PATH ?>/src/config/pages/browse.php">Browse</a>
        <a href="<?= BASE_PATH ?>/src/config/pages/search.php">Search</a>
        <a href="<?= BASE_PATH ?>/src/config/pages/auction.php">Auctions</a>
      </div>
      <div>
        <h4>My Account</h4>
        <a href="<?= BASE_PATH ?>/src/config/pages/profile.php">Profile</a>
        <a href="<?= BASE_PATH ?>/src/config/pages/my-listings.php">My Listings</a>
        <a href="<?= BASE_PATH ?>/src/config/pages/swap-offers.php">Swap Offers</a>
      </div>
      <div>
        <h4>Help</h4>
        <a href="#">About</a>
        <a href="#">Contact</a>
        <a href="#">Terms</a>
      </div>
    </div>
  </div>
  <div class="footer-bottom">&copy; <?= date('Y') ?> SwapIt. All rights reserved.</div>
</footer>
<style>
.site-footer{background:var(--dark);color:rgba(255,255,255,.55);padding:48px 32px 24px;margin-top:60px}
.footer-inner{max-width:1100px;margin:0 auto 32px;display:grid;grid-template-columns:1fr 2fr;gap:40px}
.footer-brand .footer-logo{font-family:var(--font-display);font-weight:800;font-size:20px;color:#fff;margin-bottom:10px}
.footer-brand p{font-size:13.5px;line-height:1.7;max-width:260px}
.footer-links{display:grid;grid-template-columns:repeat(3,1fr);gap:24px}
.footer-links h4{font-family:var(--font-display);font-size:13px;font-weight:700;color:#fff;margin-bottom:12px;text-transform:uppercase;letter-spacing:.04em}
.footer-links a{display:block;font-size:13px;margin-bottom:7px;transition:.15s}
.footer-links a:hover{color:#fff}
.footer-bottom{max-width:1100px;margin:0 auto;padding-top:20px;border-top:1px solid rgba(255,255,255,.08);font-size:12.5px;text-align:center}
@media(max-width:700px){.footer-inner{grid-template-columns:1fr}.footer-links{grid-template-columns:1fr 1fr}}
</style>
<?php if (!empty($extraJs)): foreach ($extraJs as $js): ?>
<script src="<?= htmlspecialchars($js) ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
