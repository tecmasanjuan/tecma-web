<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';

solicitudes_requiere_https();
solicitudes_evitar_cache();
solicitudes_iniciar_sesion();
solicitudes_no_indexar();

$_SESSION = [];
$cookie = session_get_cookie_params();
setcookie(session_name(), '', [
    'expires' => time() - 3600,
    'path' => $cookie['path'] ?? '/',
    'domain' => $cookie['domain'] ?? '',
    'secure' => $cookie['secure'] ?? false,
    'httponly' => $cookie['httponly'] ?? true,
    'samesite' => $cookie['samesite'] ?? 'Lax',
]);
session_destroy();

header('Location: /solicitudes/');
exit;
