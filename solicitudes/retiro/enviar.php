<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/auth.php';
require dirname(__DIR__) . '/includes/retiro.php';

solicitudes_evitar_cache();
solicitudes_no_indexar();
solicitudes_requiere_https();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !str_starts_with((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'multipart/form-data')) {
    retiro_responder_json(405, ['error' => 'Método no permitido.']);
}
if (!solicitudes_tiene_sesion_valida()) {
    retiro_responder_json(401, ['error' => 'Tu sesión venció. Ingresá nuevamente para continuar.']);
}
$archivoTemporal = $_FILES['orden_compra_pdf']['tmp_name'] ?? null;
try {
    $formulario = $_SESSION['retiro_formulario'] ?? [];
    if (!is_array($formulario) || !is_string($formulario['token'] ?? null) || !hash_equals($formulario['token'], (string) ($_POST['token'] ?? '')) || !is_string($formulario['numero'] ?? null) || (int) ($formulario['creado'] ?? 0) < time() - 3600) {
        throw new InvalidArgumentException('El formulario venció. Recargá la página para generar una nueva solicitud.');
    }
    $datos = retiro_validar_solicitud($_POST);
    $adjunto = retiro_adjunto_oc($_FILES['orden_compra_pdf'] ?? [], $datos['oc']);
    $configuracion = retiro_configuracion();
    foreach (['RESEND_API_KEY', 'SOLICITUDES_RETIRO_TO_EMAIL', 'SOLICITUDES_RETIRO_FROM_EMAIL'] as $clave) if (!is_string($configuracion[$clave] ?? null) || $configuracion[$clave] === '') throw new RuntimeException('El envío de solicitudes no está configurado.');
    $numero = $formulario['numero']; $fecha = is_string($formulario['fecha'] ?? null) ? $formulario['fecha'] : '';
    if ($fecha === '') throw new InvalidArgumentException('El formulario venció. Recargá la página para generar una nueva solicitud.');
    $interno = ['from'=>$configuracion['SOLICITUDES_RETIRO_FROM_EMAIL'], 'to'=>[$configuracion['SOLICITUDES_RETIRO_TO_EMAIL']], 'reply_to'=>$datos['contacto']['email'], 'subject'=>'Solicitud de retiro '.$numero, 'html'=>retiro_html_email($datos, $numero, $fecha, true)];
    if ($adjunto !== null) $interno['attachments'] = [$adjunto];
    if (!retiro_enviar_resend($configuracion, $interno, 'solicitud-retiro-interna/'.$numero)) throw new RuntimeException('No pudimos registrar la solicitud. Intentá nuevamente más tarde.');
    $cliente = ['from'=>$configuracion['SOLICITUDES_RETIRO_FROM_EMAIL'], 'to'=>[$datos['contacto']['email']], 'subject'=>'Recibimos tu solicitud de retiro '.$numero, 'html'=>retiro_html_email($datos, $numero, $fecha, false)];
    $copiaEnviada = retiro_enviar_resend($configuracion, $cliente, 'solicitud-retiro-cliente/'.$numero);
    unset($_SESSION['retiro_formulario']);
    retiro_responder_json(200, ['numero'=>$numero, 'copia_enviada'=>$copiaEnviada, 'manifiesto_pendiente'=>$datos['manifiesto']==='no']);
} catch (InvalidArgumentException $error) {
    retiro_responder_json(422, ['error' => $error->getMessage()]);
} catch (Throwable $error) {
    error_log('No se pudo enviar una solicitud de retiro: ' . $error->getMessage());
    retiro_responder_json(500, ['error' => 'No pudimos enviar la solicitud. Intentá nuevamente más tarde.']);
} finally {
    if (is_string($archivoTemporal) && $archivoTemporal !== '' && is_uploaded_file($archivoTemporal) && file_exists($archivoTemporal)) @unlink($archivoTemporal);
}
