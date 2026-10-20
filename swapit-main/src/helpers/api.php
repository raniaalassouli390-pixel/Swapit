<?php


if (!function_exists('jsonResponse')) {
    
    function jsonResponse(array $data, int $code = 200): void
    {
        http_response_code($code);
        echo json_encode($data);
        exit;
    }
}

if (!function_exists('jsonError')) {
    
    function jsonError(string $message, int $code = 400): void
    {
        jsonResponse(['ok' => false, 'error' => $message], $code);
    }
}

if (!function_exists('getBearerToken')) {
    
    function getBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (preg_match('/^Bearer\s+(.+)$/i', trim($header), $m)) {
            return $m[1];
        }

        
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
            if (preg_match('/^Bearer\s+(.+)$/i', trim($auth), $m)) {
                return $m[1];
            }
        }

        return null;
    }
}

if (!function_exists('requireAuth')) {
    
    function requireAuth(): object
    {
        
        if (!function_exists('verifyJWT')) {
            require_once __DIR__ . '/../config/jwt.php';
        }

        $token = getBearerToken();
        if (!$token) {
            jsonError('Authentication required. Provide a Bearer token.', 401);
        }

        $payload = verifyJWT($token);
        if (!$payload || !isset($payload->user)) {
            jsonError('Invalid or expired token.', 401);
        }

        return $payload->user;
    }
}

if (!function_exists('requireAdmin')) {
    
    function requireAdmin(): object
    {
        $authUser = requireAuth();

        if (($authUser->role ?? '') !== 'admin') {
            jsonError('Forbidden. Admin access required.', 403);
        }

        return $authUser;
    }
}


if (!function_exists('applySecurityHeaders')) {
    function applySecurityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Content-Security-Policy: default-src 'none'");
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }
}


if (!function_exists('requireOwnerOrAdmin')) {
    
    function requireOwnerOrAdmin(int $resourceOwnerId): object
    {
        $user = requireAuth();
        if ((int) $user->id !== $resourceOwnerId && ($user->role ?? '') !== 'admin') {
            jsonError('Forbidden: you do not have permission to perform this action', 403);
        }
        return $user;
    }
}

if (!function_exists('userCan')) {
    
    function userCan(object $user, string $action, int $resourceOwnerId = 0): bool
    {
        return match ($action) {
            'admin' => ($user->role ?? '') === 'admin',
            'owner' => (int) $user->id === $resourceOwnerId,
            'owner_or_admin' => (int) $user->id === $resourceOwnerId || ($user->role ?? '') === 'admin',
            default => false,
        };
    }
}


applySecurityHeaders();

if (!function_exists('validateInput')) {
    function validateInput(array $data, array $rules): array
    {
        $clean = [];

        foreach ($rules as $field => $fieldRules) {
            $value   = $data[$field] ?? null;
            $isEmpty = ($value === null || $value === '');

            if (!empty($fieldRules['required']) && $isEmpty) {
                jsonError("Field '{$field}' is required.", 422);
            }

            if ($isEmpty) {
                $clean[$field] = null;
                continue;
            }

            
            if (!empty($fieldRules['sanitize'])) {
                $value = strip_tags(trim((string) $value));
            }

            if (isset($fieldRules['type'])) {
                switch ($fieldRules['type']) {
                    case 'int':
                        if (!filter_var($value, FILTER_VALIDATE_INT)) {
                            jsonError("Field '{$field}' must be an integer.", 422);
                        }
                        $value = (int) $value;
                        break;
                    case 'float':
                        if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
                            jsonError("Field '{$field}' must be a number.", 422);
                        }
                        $value = (float) $value;
                        break;
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            jsonError("Field '{$field}' must be a valid email.", 422);
                        }
                        $value = strtolower(trim($value));
                        break;
                    case 'url':
                        if (!filter_var($value, FILTER_VALIDATE_URL)) {
                            jsonError("Field '{$field}' must be a valid URL.", 422);
                        }
                        break;
                    case 'string':
                    default:
                        $value = (string) $value;
                        break;
                }
            }

            if (isset($fieldRules['min'])) {
                $check = is_string($value) ? mb_strlen($value) : $value;
                if ($check < $fieldRules['min']) {
                    jsonError("Field '{$field}' must be at least {$fieldRules['min']}.", 422);
                }
            }

            if (isset($fieldRules['max'])) {
                $check = is_string($value) ? mb_strlen($value) : $value;
                if ($check > $fieldRules['max']) {
                    jsonError("Field '{$field}' must be no more than {$fieldRules['max']}.", 422);
                }
            }

            if (isset($fieldRules['regex']) && !preg_match($fieldRules['regex'], (string) $value)) {
                jsonError("Field '{$field}' has an invalid format.", 422);
            }

            if (isset($fieldRules['in']) && !in_array($value, $fieldRules['in'], true)) {
                jsonError("Field '{$field}' must be one of: " . implode(', ', $fieldRules['in']) . '.', 422);
            }

            $clean[$field] = $value;
        }

        return $clean;
    }
}

if (!function_exists('autoValidate')) {
    function autoValidate(array $data): array
    {
        $rules    = require __DIR__ . '/../config/api/validation_rules.php';
        $endpoint = basename($_SERVER['SCRIPT_FILENAME'], '.php');
        $method   = $_SERVER['REQUEST_METHOD'];

        if (isset($rules[$endpoint][$method])) {
            return validateInput($data, $rules[$endpoint][$method]);
        }

        return $data;
    }
}
