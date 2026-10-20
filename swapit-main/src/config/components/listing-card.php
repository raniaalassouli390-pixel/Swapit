<?php
require_once __DIR__ . '/../config.php';

function renderListingCard(array $listing): string {
    $id       = (int)($listing['id'] ?? 0);
    $title    = htmlspecialchars($listing['title'] ?? 'Untitled', ENT_QUOTES);
    $price    = $listing['price'] ?? '';
    $type     = $listing['type'] ?? 'sell';
    $city     = htmlspecialchars($listing['city'] ?? '');
    $img      = htmlspecialchars(asset_url($listing['main_image'] ?? ''));
    $condition= htmlspecialchars($listing['condition'] ?? '');
    $typeLabel = ['sell'=>'For Sale','swap'=>'Swap','auction'=>'Auction'][$type] ?? ucfirst($type);
    $typeClass = ['sell'=>'type-sell','swap'=>'type-swap','auction'=>'type-auction'][$type] ?? 'type-sell';
    $imgHtml = $img
        ? '<img src="'.htmlspecialchars($img).'" alt="'.htmlspecialchars($listing['title']??'').'" loading="lazy"/>'
        : '<div class="lc-img-placeholder">📦</div>';
    $priceHtml = $price
        ? '<span class="lc-price">JD '.number_format((float)$price, 2).'</span>'
        : ($type === 'swap' ? '<span class="lc-price lc-price-swap">Swap</span>' : '');
    return '<a href="' . BASE_PATH . '/src/config/pages/listing-detail.php?id='.$id.'" class="listing-card"><div class="lc-img">'.$imgHtml.'<div class="lc-type '.$typeClass.'">'.$typeLabel.'</div></div><div class="lc-body"><div class="lc-title">'.$title.'</div><div class="lc-meta"><span>📍 '.$city.'</span><span>'.$condition.'</span></div>'.$priceHtml.'</div></a>';
}
