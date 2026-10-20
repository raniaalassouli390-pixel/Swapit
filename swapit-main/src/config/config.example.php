<?php



define('DB_HOST',    'localhost');
define('DB_NAME',    'swapit');
define('DB_CHARSET', 'utf8mb4');
define('DB_USER',    'root');
define('DB_PASS',    '');          





define('APP_URL', 'http://localhost/swapit');
define('BASE_PATH', rtrim(parse_url(APP_URL, PHP_URL_PATH) ?? '', '/')); 
define('DEBUG',   true);                        



define('JWT_SECRET', 'change-this-to-a-long-random-secret-key-in-production');


function asset_url(?string $path): string {
    if ($path === null || $path === '') return '';
    if (preg_match('~^https?://~i', $path)) return $path;
    if ($path[0] !== '/') $path = '/' . $path;
    return BASE_PATH . $path;
}
