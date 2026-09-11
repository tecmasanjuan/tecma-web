<?php
declare(strict_types=1);

const SOLICITUDES_SESSION_NAME = 'tecma_solicitudes';
const SOLICITUDES_HTTPS_ORIGIN = 'https://www.tecmasanjuan.com.ar';
const SOLICITUDES_MAX_INTENTOS_FALLIDOS = 5;
const SOLICITUDES_BLOQUEO_SEGUNDOS = 900;
const SOLICITUDES_RATE_LIMIT_TTL_SEGUNDOS = 86400;
const SOLICITUDES_RATE_LIMIT_LIMPIEZA_MAX_ARCHIVOS = 20;
const SOLICITUDES_RATE_LIMIT_REINTENTOS_APERTURA = 3;

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

function solicitudes_directorio_rate_limit(): ?string
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
    if (is_link($directorioRateLimit) || !is_writable($directorioRateLimit)) {
        error_log('El directorio de rate limiting de solicitudes no es escribible.');
        return null;
    }

    return $directorioRateLimit;
}

function solicitudes_archivo_rate_limit(): ?string
{
    $directorioRateLimit = solicitudes_directorio_rate_limit();
    if ($directorioRateLimit === null) {
        return null;
    }

    $direccionCliente = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    if ($direccionCliente === '') {
        error_log('Módulo solicitudes sin REMOTE_ADDR para aplicar rate limiting.');
        return null;
    }

    return $directorioRateLimit . '/' . hash('sha256', $direccionCliente) . '.json';
}

function solicitudes_es_archivo_regular(array $estadoArchivo): bool
{
    return (($estadoArchivo['mode'] ?? 0) & 0170000) === 0100000;
}

function solicitudes_archivo_rate_limit_coincide_con_ruta($archivo, string $rutaEstado): bool
{
    clearstatcache(true, $rutaEstado);
    $estadoRuta = @lstat($rutaEstado);
    $estadoArchivo = fstat($archivo);

    return !is_link($rutaEstado)
        && is_array($estadoRuta)
        && is_array($estadoArchivo)
        && solicitudes_es_archivo_regular($estadoRuta)
        && solicitudes_es_archivo_regular($estadoArchivo)
        && ($estadoRuta['dev'] ?? null) === ($estadoArchivo['dev'] ?? null)
        && ($estadoRuta['ino'] ?? null) === ($estadoArchivo['ino'] ?? null);
}

function solicitudes_cursor_limpieza_rate_limit(string $directorioRateLimit): string
{
    return $directorioRateLimit . '/.cleanup-cursor';
}

function solicitudes_abrir_cursor_limpieza_rate_limit(string $directorioRateLimit): ?array
{
    $rutaCursor = solicitudes_cursor_limpieza_rate_limit($directorioRateLimit);
    if (is_link($rutaCursor)) {
        return null;
    }

    $archivo = @fopen($rutaCursor, 'c+');
    if ($archivo === false || !flock($archivo, LOCK_EX | LOCK_NB)) {
        if (is_resource($archivo)) {
            fclose($archivo);
        }
        return null;
    }

    if (!solicitudes_archivo_rate_limit_coincide_con_ruta($archivo, $rutaCursor)) {
        flock($archivo, LOCK_UN);
        fclose($archivo);
        return null;
    }

    @chmod($rutaCursor, 0600);
    return [$archivo, $rutaCursor];
}

function solicitudes_leer_cursor_limpieza_rate_limit($archivo): int
{
    $estadoArchivo = fstat($archivo);
    if (!is_array($estadoArchivo) || ($estadoArchivo['size'] ?? 0) > 20 || !rewind($archivo)) {
        return 0;
    }

    $contenido = stream_get_contents($archivo);
    if ($contenido === false || !preg_match('/\A[0-9]+\z/D', $contenido)) {
        return 0;
    }

    return (int) $contenido;
}

function solicitudes_guardar_cursor_limpieza_rate_limit($archivo, int $posicion): void
{
    $contenido = (string) max(0, $posicion);
    if (!ftruncate($archivo, 0) || !rewind($archivo) || fwrite($archivo, $contenido) !== strlen($contenido) || !fflush($archivo)) {
        error_log('No se pudo actualizar el cursor de limpieza de rate limiting de solicitudes.');
    }
}

function solicitudes_recolectar_estados_rate_limit(string $directorioRateLimit, string $rutaPropia): void
{
    $cursorAbierto = solicitudes_abrir_cursor_limpieza_rate_limit($directorioRateLimit);
    if ($cursorAbierto === null) {
        return;
    }

    [$cursor, $rutaCursor] = $cursorAbierto;
    $ahora = time();
    $expiraAntesDe = $ahora - SOLICITUDES_RATE_LIMIT_TTL_SEGUNDOS;

    try {
        $posicion = solicitudes_leer_cursor_limpieza_rate_limit($cursor);
        try {
            $entradas = new FilesystemIterator(
                $directorioRateLimit,
                FilesystemIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_PATHNAME
            );
            $entradas->seek($posicion);
        } catch (Exception $error) {
            solicitudes_guardar_cursor_limpieza_rate_limit($cursor, 0);
            return;
        }

        $procesadas = 0;
        while ($procesadas < SOLICITUDES_RATE_LIMIT_LIMPIEZA_MAX_ARCHIVOS && $entradas->valid()) {
            $rutaEstado = $entradas->current();
            $nombre = $entradas->getFilename();
            $procesadas++;
            $entradas->next();

            if ($rutaEstado === $rutaCursor) {
                continue;
            }
            if (!preg_match('/\A[a-f0-9]{64}\.json\z/D', $nombre)) {
                continue;
            }

            if ($rutaEstado === $rutaPropia || is_link($rutaEstado)) {
                continue;
            }

            clearstatcache(true, $rutaEstado);
            $estadoArchivo = @lstat($rutaEstado);
            if (!is_array($estadoArchivo)
                || !solicitudes_es_archivo_regular($estadoArchivo)
                || ($estadoArchivo['mtime'] ?? 0) > $expiraAntesDe) {
                continue;
            }

            $archivo = @fopen($rutaEstado, 'r+');
            if ($archivo === false || !flock($archivo, LOCK_EX | LOCK_NB)) {
                if (is_resource($archivo)) {
                    fclose($archivo);
                }
                continue;
            }

            try {
                clearstatcache(true, $rutaEstado);
                $estadoActual = @lstat($rutaEstado);
                $estadoAbierto = fstat($archivo);
                if (!is_array($estadoActual)
                    || !is_array($estadoAbierto)
                    || !solicitudes_es_archivo_regular($estadoActual)
                    || !solicitudes_es_archivo_regular($estadoAbierto)
                    || ($estadoActual['dev'] ?? null) !== ($estadoAbierto['dev'] ?? null)
                    || ($estadoActual['ino'] ?? null) !== ($estadoAbierto['ino'] ?? null)
                    || ($estadoActual['mtime'] ?? 0) > $expiraAntesDe
                    || !rewind($archivo)) {
                    continue;
                }

                $contenido = stream_get_contents($archivo);
                $estado = $contenido === false ? null : json_decode($contenido, true);
                $bloqueadoHasta = is_array($estado) ? (int) ($estado['bloqueado_hasta'] ?? 0) : 0;
                if ($bloqueadoHasta > $ahora) {
                    continue;
                }

                clearstatcache(true, $rutaEstado);
                $estadoAntesDeEliminar = @lstat($rutaEstado);
                if (!is_array($estadoAntesDeEliminar)
                    || !solicitudes_es_archivo_regular($estadoAntesDeEliminar)
                    || ($estadoAntesDeEliminar['dev'] ?? null) !== ($estadoAbierto['dev'] ?? null)
                    || ($estadoAntesDeEliminar['ino'] ?? null) !== ($estadoAbierto['ino'] ?? null)) {
                    continue;
                }

                if (!@unlink($rutaEstado)) {
                    error_log('No se pudo eliminar un estado obsoleto de rate limiting de solicitudes.');
                }
            } finally {
                flock($archivo, LOCK_UN);
                fclose($archivo);
            }
        }

        solicitudes_guardar_cursor_limpieza_rate_limit(
            $cursor,
            $entradas->valid() ? $posicion + $procesadas : 0
        );
    } finally {
        flock($cursor, LOCK_UN);
        fclose($cursor);
    }
}

function solicitudes_abrir_estado_rate_limit(string $rutaEstado): ?array
{
    for ($intento = 0; $intento < SOLICITUDES_RATE_LIMIT_REINTENTOS_APERTURA; $intento++) {
        $archivo = @fopen($rutaEstado, 'x+');
        $archivoNuevo = $archivo !== false;
        if ($archivo === false) {
            $archivo = @fopen($rutaEstado, 'r+');
            $archivoNuevo = false;
        }

        if ($archivo === false) {
            continue;
        }
        if (!flock($archivo, LOCK_EX)) {
            fclose($archivo);
            continue;
        }
        if (solicitudes_archivo_rate_limit_coincide_con_ruta($archivo, $rutaEstado)) {
            return [$archivo, $archivoNuevo];
        }

        flock($archivo, LOCK_UN);
        fclose($archivo);
    }

    error_log('No se pudo abrir un estado de rate limiting de solicitudes válido.');
    return null;
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

    solicitudes_recolectar_estados_rate_limit(dirname($rutaEstado), $rutaEstado);

    $estadoAbierto = solicitudes_abrir_estado_rate_limit($rutaEstado);
    if ($estadoAbierto === null) {
        return 'no_disponible';
    }

    [$archivo, $archivoNuevo] = $estadoAbierto;

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
