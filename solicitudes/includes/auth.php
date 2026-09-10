<?php
declare(strict_types=1);

const SOLICITUDES_SESSION_NAME = 'tecma_solicitudes';

function solicitudes_es_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
}

function solicitudes_iniciar_sesion(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_name(SOLICITUDES_SESSION_NAME);
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => solicitudes_es_https(),
    ]);
    session_start();
}

function solicitudes_no_indexar(): void
{
    header('X-Robots-Tag: noindex, nofollow');
}

function solicitudes_configuracion(): array
{
    static $configuracionCacheada = null;
    if (is_array($configuracionCacheada)) {
        return $configuracionCacheada;
    }

    $configuracion = [];
    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $archivoExterno = $documentRoot !== ''
        ? dirname($documentRoot) . '/secure/tecma-solicitudes.php'
        : '';

    if ($archivoExterno !== '' && is_readable($archivoExterno)) {
        $cargada = require $archivoExterno;
        if (is_array($cargada)) {
            $configuracion = $cargada;
        }
    }

    $hash = getenv('SOLICITUDES_ACCESS_PASSWORD_HASH');
    if ($hash !== false && $hash !== '') {
        $configuracion['SOLICITUDES_ACCESS_PASSWORD_HASH'] = $hash;
    }

    $configuracionCacheada = $configuracion;
    return $configuracionCacheada;
}

function solicitudes_hash_de_acceso(): ?string
{
    $configuracion = solicitudes_configuracion();
    $hash = $configuracion['SOLICITUDES_ACCESS_PASSWORD_HASH'] ?? null;

    if (!is_string($hash) || $hash === '') {
        error_log('Módulo solicitudes sin SOLICITUDES_ACCESS_PASSWORD_HASH configurado.');
        return null;
    }

    return $hash;
}

function solicitudes_autenticar(string $clave): bool
{
    $hash = solicitudes_hash_de_acceso();
    if ($hash === null || !password_verify($clave, $hash)) {
        return false;
    }

    solicitudes_iniciar_sesion();
    session_regenerate_id(true);
    $_SESSION['solicitudes_autenticada'] = true;

    return true;
}

function solicitudes_tiene_sesion_valida(): bool
{
    solicitudes_iniciar_sesion();
    return ($_SESSION['solicitudes_autenticada'] ?? false) === true;
}

function solicitudes_requiere_autenticacion(): void
{
    if (solicitudes_tiene_sesion_valida()) {
        return;
    }

    solicitudes_no_indexar();
    header('Location: /solicitudes/');
    exit;
}
