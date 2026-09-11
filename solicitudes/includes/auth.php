<?php
declare(strict_types=1);

const SOLICITUDES_SESSION_NAME = 'tecma_solicitudes';
const SOLICITUDES_HTTPS_ORIGIN = 'https://www.tecmasanjuan.com.ar';
const SOLICITUDES_MAX_INTENTOS_FALLIDOS = 5;
const SOLICITUDES_BLOQUEO_SEGUNDOS = 900;

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
    session_cache_limiter('');
    session_start();
}

function solicitudes_no_indexar(): void
{
    header('X-Robots-Tag: noindex, nofollow');
}

function solicitudes_evitar_cache(): void
{
    header('Cache-Control: no-store, private, max-age=0');
}

function solicitudes_requiere_https(): void
{
    if (solicitudes_es_https()) {
        return;
    }

    solicitudes_evitar_cache();
    solicitudes_no_indexar();
    header('Location: ' . SOLICITUDES_HTTPS_ORIGIN . '/solicitudes/', true, 302);
    exit;
}

function solicitudes_directorio_seguro(): ?string
{
    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if ($documentRoot === '') {
        error_log('Módulo solicitudes sin DOCUMENT_ROOT para acceder al directorio seguro.');
        return null;
    }

    return dirname($documentRoot) . '/secure';
}

function solicitudes_configuracion(): array
{
    static $configuracionCacheada = null;
    if (is_array($configuracionCacheada)) {
        return $configuracionCacheada;
    }

    $configuracion = [];
    $directorioSeguro = solicitudes_directorio_seguro();
    $archivoExterno = $directorioSeguro !== null
        ? $directorioSeguro . '/tecma-solicitudes.php'
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

function solicitudes_archivo_rate_limit(): ?string
{
    $directorioSeguro = solicitudes_directorio_seguro();
    if ($directorioSeguro === null) {
        return null;
    }

    $directorioRateLimit = $directorioSeguro . '/tecma-solicitudes-rate-limit';
    if (!is_dir($directorioRateLimit) && !mkdir($directorioRateLimit, 0700, true) && !is_dir($directorioRateLimit)) {
        error_log('No se pudo crear el directorio de rate limiting de solicitudes.');
        return null;
    }
    if (!is_writable($directorioRateLimit)) {
        error_log('El directorio de rate limiting de solicitudes no es escribible.');
        return null;
    }

    $direccionCliente = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    if ($direccionCliente === '') {
        error_log('Módulo solicitudes sin REMOTE_ADDR para aplicar rate limiting.');
        return null;
    }

    return $directorioRateLimit . '/' . hash('sha256', $direccionCliente) . '.json';
}

function solicitudes_abrir_estado_rate_limit(string $rutaEstado): ?array
{
    $archivo = @fopen($rutaEstado, 'x+');
    if ($archivo !== false) {
        return [$archivo, true];
    }

    $archivo = @fopen($rutaEstado, 'r+');
    if ($archivo === false) {
        error_log('No se pudo abrir el estado de rate limiting de solicitudes.');
        return null;
    }

    return [$archivo, false];
}

function solicitudes_leer_estado_rate_limit($archivo, bool $archivoNuevo): ?array
{
    if ($archivoNuevo) {
        return [];
    }

    if (!rewind($archivo)) {
        return null;
    }

    $contenido = stream_get_contents($archivo);
    if ($contenido === false || $contenido === '') {
        return null;
    }

    $estado = json_decode($contenido, true);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($estado)) {
        return null;
    }

    return $estado;
}

function solicitudes_guardar_estado_rate_limit($archivo, array $estado): bool
{
    $contenido = json_encode($estado);
    if ($contenido === false || !ftruncate($archivo, 0)) {
        return false;
    }

    if (!rewind($archivo) || fwrite($archivo, $contenido) !== strlen($contenido) || !fflush($archivo)) {
        // Un fallo posterior al truncado no debe dejar un estado válido que pueda reutilizarse.
        if (ftruncate($archivo, 0)) {
            fflush($archivo);
        }
        return false;
    }

    return true;
}

function solicitudes_autenticar(string $clave): string
{
    $hash = solicitudes_hash_de_acceso();
    if ($hash === null) {
        return 'no_disponible';
    }

    $rutaEstado = solicitudes_archivo_rate_limit();
    if ($rutaEstado === null) {
        return 'no_disponible';
    }

    $estadoAbierto = solicitudes_abrir_estado_rate_limit($rutaEstado);
    if ($estadoAbierto === null) {
        return 'no_disponible';
    }

    [$archivo, $archivoNuevo] = $estadoAbierto;
    if ($archivo === false || !flock($archivo, LOCK_EX)) {
        error_log('No se pudo bloquear el estado de rate limiting de solicitudes.');
        if (is_resource($archivo)) {
            fclose($archivo);
        }
        return 'no_disponible';
    }

    @chmod($rutaEstado, 0600);

    try {
        $estado = solicitudes_leer_estado_rate_limit($archivo, $archivoNuevo);
        if ($estado === null) {
            error_log('Estado de rate limiting de solicitudes corrupto o ilegible.');
            return 'no_disponible';
        }

        $ahora = time();
        $bloqueadoHasta = (int) ($estado['bloqueado_hasta'] ?? 0);

        if ($bloqueadoHasta > $ahora) {
            return 'bloqueada';
        }
        if ($bloqueadoHasta !== 0) {
            $estado = [];
        }

        if (password_verify($clave, $hash)) {
            if (!solicitudes_guardar_estado_rate_limit($archivo, [])) {
                error_log('No se pudo restablecer el rate limiting de solicitudes.');
                return 'no_disponible';
            }

            solicitudes_iniciar_sesion();
            session_regenerate_id(true);
            $_SESSION['solicitudes_autenticada'] = true;
            return 'autenticada';
        }

        $intentos = (int) ($estado['intentos'] ?? 0) + 1;
        $nuevoEstado = ['intentos' => $intentos];
        if ($intentos >= SOLICITUDES_MAX_INTENTOS_FALLIDOS) {
            $nuevoEstado['bloqueado_hasta'] = $ahora + SOLICITUDES_BLOQUEO_SEGUNDOS;
        }
        if (!solicitudes_guardar_estado_rate_limit($archivo, $nuevoEstado)) {
            error_log('No se pudo registrar un intento fallido de solicitudes.');
            return 'no_disponible';
        }

        return $intentos >= SOLICITUDES_MAX_INTENTOS_FALLIDOS ? 'bloqueada' : 'rechazada';
    } finally {
        flock($archivo, LOCK_UN);
        fclose($archivo);
    }
}

function solicitudes_tiene_sesion_valida(): bool
{
    solicitudes_iniciar_sesion();
    return ($_SESSION['solicitudes_autenticada'] ?? false) === true;
}

function solicitudes_requiere_autenticacion(): void
{
    solicitudes_requiere_https();

    if (solicitudes_tiene_sesion_valida()) {
        return;
    }

    solicitudes_no_indexar();
    header('Location: /solicitudes/');
    exit;
}
