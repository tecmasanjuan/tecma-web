<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/auth.php';
require dirname(__DIR__) . '/includes/retiro.php';

solicitudes_evitar_cache();
solicitudes_no_indexar();
solicitudes_requiere_https();
$archivoTemporal = $_FILES['orden_compra_pdf']['tmp_name'] ?? null;
$estadoRespuesta = 500;
$payloadRespuesta = ['error' => 'No pudimos enviar la solicitud. Intentá nuevamente más tarde.'];
try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !str_starts_with((string) ($_SERVER['CONTENT_TYPE'] ?? ''), 'multipart/form-data')) {
        $estadoRespuesta = 405;
        $payloadRespuesta = ['error' => 'Método no permitido.'];
    } elseif (!solicitudes_tiene_sesion_valida()) {
        $estadoRespuesta = 401;
        $payloadRespuesta = ['error' => 'Tu sesión venció. Ingresá nuevamente para continuar.'];
    } else {
        $tokenFormulario = is_string($_POST['token'] ?? null) ? trim($_POST['token']) : '';
        if (!preg_match('/\A[a-f0-9]{64}\z/D', $tokenFormulario)) {
            throw new InvalidArgumentException('El formulario venció. Recargá la página para generar una nueva solicitud.');
        }
        $formulario = retiro_formulario_pendiente($tokenFormulario);
        if ($formulario === null) throw new InvalidArgumentException('El formulario venció. Recargá la página para generar una nueva solicitud.');
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
        unset($_SESSION['retiro_formularios'][$tokenFormulario]);
        $estadoRespuesta = 200;
        $payloadRespuesta = ['numero'=>$numero, 'copia_enviada'=>$copiaEnviada, 'manifiesto_pendiente'=>$datos['manifiesto']==='no'];
    }
} catch (InvalidArgumentException $error) {
    $estadoRespuesta = 422;
    $payloadRespuesta = ['error' => $error->getMessage()];
} catch (Throwable $error) {
    error_log('No se pudo enviar una solicitud de retiro: ' . $error->getMessage());
    $estadoRespuesta = 500;
    $payloadRespuesta = ['error' => 'No pudimos enviar la solicitud. Intentá nuevamente más tarde.'];
} finally {
    if (is_string($archivoTemporal) && $archivoTemporal !== '' && is_uploaded_file($archivoTemporal) && file_exists($archivoTemporal)) @unlink($archivoTemporal);
}
retiro_responder_json($estadoRespuesta, $payloadRespuesta);
