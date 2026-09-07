<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function responder(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function longitud(string $valor): int
{
    return function_exists('mb_strlen') ? mb_strlen($valor, 'UTF-8') : strlen($valor);
}

function configuracion(): array
{
    $config = [];
    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $archivoExterno = $documentRoot !== ''
        ? dirname($documentRoot) . '/secure/tecma-contacto.php'
        : '';

    if ($archivoExterno !== '' && is_readable($archivoExterno)) {
        $cargada = require $archivoExterno;
        if (is_array($cargada)) {
            $config = $cargada;
        }
    }

    foreach (['SUPABASE_URL', 'SUPABASE_SERVICE_ROLE_KEY', 'RESEND_API_KEY', 'CONTACT_FORM_TO_EMAIL'] as $clave) {
        $valor = getenv($clave);
        if ($valor !== false && $valor !== '') {
            $config[$clave] = $valor;
        }
    }

    return $config;
}

function limpiar(string $valor, bool $preservarSaltos = false): string
{
    if ($preservarSaltos) {
        $valor = str_replace(["\r\n", "\r"], "\n", $valor);
        return trim(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $valor) ?? '');
    }

    return trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $valor) ?? '');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responder(405, ['ok' => false, 'message' => 'Método no permitido.']);
}

$contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
if (strpos($contentType, 'application/json') !== 0) {
    responder(415, ['ok' => false, 'message' => 'El formato de envío no es válido.']);
}

$body = file_get_contents('php://input');
$datos = json_decode($body ?: '', true);
if (!is_array($datos)) {
    responder(400, ['ok' => false, 'message' => 'La consulta no tiene un formato válido.']);
}

if (limpiar((string) ($datos['sitio_web'] ?? '')) !== '') {
    responder(400, ['ok' => false, 'message' => 'No pudimos procesar la consulta.']);
}

$nombre = limpiar((string) ($datos['nombre'] ?? ''));
$email = strtolower(limpiar((string) ($datos['email'] ?? '')));
$telefono = limpiar((string) ($datos['telefono'] ?? ''));
$mensaje = limpiar((string) ($datos['mensaje'] ?? ''), true);

if ($nombre === '' || $email === '' || $mensaje === '') {
    responder(422, ['ok' => false, 'message' => 'Completá nombre, email y mensaje.']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(422, ['ok' => false, 'message' => 'Ingresá un email válido.']);
}
if (longitud($nombre) > 120 || longitud($email) > 254 || longitud($telefono) > 40 || longitud($mensaje) > 5000) {
    responder(422, ['ok' => false, 'message' => 'Alguno de los datos supera el límite permitido.']);
}

$config = configuracion();
$requeridas = ['SUPABASE_URL', 'SUPABASE_SERVICE_ROLE_KEY', 'RESEND_API_KEY', 'CONTACT_FORM_TO_EMAIL'];
foreach ($requeridas as $clave) {
    if (empty($config[$clave])) {
        error_log('Formulario de contacto sin configuración requerida: ' . $clave);
        responder(500, ['ok' => false, 'message' => 'El formulario no está disponible en este momento.']);
    }
}
if (!filter_var($config['CONTACT_FORM_TO_EMAIL'], FILTER_VALIDATE_EMAIL)) {
    error_log('Formulario de contacto con destinatario inválido.');
    responder(500, ['ok' => false, 'message' => 'El formulario no está disponible en este momento.']);
}

function solicitud_http(string $url, array $headers, string $metodo, ?string $payload = null): array
{
    $curl = curl_init($url);
    curl_setopt_array($curl, [
        CURLOPT_CUSTOMREQUEST => $metodo,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 20,
    ]);
    if ($payload !== null) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
    }
    $respuesta = curl_exec($curl);
    $error = curl_error($curl);
    $codigo = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return [$codigo, $respuesta, $error];
}

$supabaseBase = rtrim((string) $config['SUPABASE_URL'], '/');
$supabaseHeaders = [
    'apikey: ' . $config['SUPABASE_SERVICE_ROLE_KEY'],
    'Authorization: Bearer ' . $config['SUPABASE_SERVICE_ROLE_KEY'],
    'Content-Type: application/json',
    'Prefer: return=representation',
];
$registro = json_encode([
    'nombre' => $nombre,
    'email' => $email,
    'telefono' => $telefono !== '' ? $telefono : null,
    'mensaje' => $mensaje,
    'estado' => 'pendiente',
    'email_enviado' => false,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

[$codigoSupabase, $respuestaSupabase, $errorSupabase] = solicitud_http(
    $supabaseBase . '/rest/v1/consultas_web',
    $supabaseHeaders,
    'POST',
    $registro
);
if ($errorSupabase !== '' || $codigoSupabase < 200 || $codigoSupabase >= 300) {
    error_log('No se pudo guardar una consulta en Supabase: ' . $codigoSupabase);
    responder(502, ['ok' => false, 'message' => 'No pudimos recibir tu consulta. Intentá nuevamente.']);
}

$filas = json_decode($respuestaSupabase ?: '', true);
$id = is_array($filas) && isset($filas[0]['id']) ? $filas[0]['id'] : null;
if ($id === null) {
    error_log('Supabase no devolvió el identificador de la consulta.');
    responder(502, ['ok' => false, 'message' => 'No pudimos confirmar tu consulta. Intentá nuevamente.']);
}

$html = '<h2>Nueva consulta desde la web de TeCMA</h2>'
    . '<p><strong>Nombre:</strong> ' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</p>'
    . '<p><strong>Email:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</p>'
    . ($telefono !== '' ? '<p><strong>Teléfono:</strong> ' . htmlspecialchars($telefono, ENT_QUOTES, 'UTF-8') . '</p>' : '')
    . '<p><strong>Mensaje:</strong></p><p>' . nl2br(htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8')) . '</p>';
$emailPayload = json_encode([
    'from' => 'TeCMA San Juan <web@mail.tecmasanjuan.com.ar>',
    'to' => [$config['CONTACT_FORM_TO_EMAIL']],
    'reply_to' => $email,
    'subject' => 'Nueva consulta desde la web de TeCMA',
    'html' => $html,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

[$codigoResend, , $errorResend] = solicitud_http(
    'https://api.resend.com/emails',
    ['Authorization: Bearer ' . $config['RESEND_API_KEY'], 'Content-Type: application/json'],
    'POST',
    $emailPayload
);
if ($errorResend === '' && $codigoResend >= 200 && $codigoResend < 300) {
    [$codigoActualizacion, , $errorActualizacion] = solicitud_http(
        $supabaseBase . '/rest/v1/consultas_web?id=eq.' . rawurlencode((string) $id),
        $supabaseHeaders,
        'PATCH',
        json_encode(['email_enviado' => true])
    );
    if ($errorActualizacion === '' && $codigoActualizacion >= 200 && $codigoActualizacion < 300) {
        responder(200, ['ok' => true, 'message' => 'Recibimos tu consulta. Te responderemos a la brevedad.']);
    }
    error_log('Consulta guardada y email enviado, pero no se pudo actualizar email_enviado.');
    responder(200, ['ok' => true, 'message' => 'Recibimos tu consulta. Te responderemos a la brevedad.']);
}

error_log('Consulta guardada, pero Resend no pudo enviar la notificación: ' . $codigoResend);
responder(202, ['ok' => true, 'message' => 'Recibimos tu consulta. Te responderemos a la brevedad.']);
