<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!defined('JWT_SECRET')) {
    require_once __DIR__ . '/config.php';
}

function createJWT(array $user): string
{
    $secret = defined('JWT_SECRET') ? JWT_SECRET : 'swapit_super_secret_key';
    $name = $user['name'] ?? trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    $permissions = $user['permissions'] ?? ($user['role'] === 'admin' ? ['admin'] : []);

    $payload = [
        'iss' => 'swapit',
        'iat' => time(),
        'exp' => time() + 3600,
        'user' => [
            'id' => (int) $user['id'],
            'name' => $name,
            'email' => $user['email'],
            'role' => $user['role'],
            'permissions' => $permissions,
        ],
    ];

    return JWT::encode($payload, $secret, 'HS256');
}

function verifyJWT(string $token)
{
    $secret = defined('JWT_SECRET') ? JWT_SECRET : 'swapit_super_secret_key';
    try {
        return JWT::decode($token, new Key($secret, 'HS256'));
    } catch (Exception $e) {
        return false;
    }
}
