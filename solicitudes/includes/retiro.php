<?php
declare(strict_types=1);

require_once __DIR__ . '/catalogo_rrpp.php';

const RETIRO_MAX_BLOQUES = 20;
const RETIRO_MAX_FORMULARIOS_PENDIENTES = 20;
const RETIRO_FORMULARIO_TTL = 3600;

function retiro_escape(string $valor): string { return htmlspecialchars($valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function retiro_texto(mixed $valor, int $max = 300): string {
    $valor = is_string($valor) ? trim($valor) : '';
    return function_exists('mb_substr') ? mb_substr($valor, 0, $max) : substr($valor, 0, $max);
}
function retiro_requiere(array $datos, string $clave, string $etiqueta, int $max = 300): string {
    $valor = retiro_texto($datos[$clave] ?? '', $max);
    if ($valor === '') { throw new InvalidArgumentException("Completá {$etiqueta}."); }
    return $valor;
}
function retiro_decimal(mixed $valor, string $etiqueta): float {
    $valor = str_replace(',', '.', retiro_texto($valor, 30));
    if (!preg_match('/\A(?:0|[1-9][0-9]*)(?:\.[0-9]+)?\z/', $valor) || (float) $valor <= 0) {
        throw new InvalidArgumentException("Indicá {$etiqueta} con un valor mayor a cero.");
    }
    return (float) $valor;
}
function retiro_decimal_no_negativo(mixed $valor, string $etiqueta): string {
    $valor = str_replace(',', '.', retiro_texto($valor, 30));
    if ($valor === '' || !preg_match('/\A(?:0|[1-9][0-9]*)(?:\.[0-9]+)?\z/', $valor) || (float) $valor < 0) {
        throw new InvalidArgumentException("Indicá {$etiqueta} con un valor decimal mayor o igual a cero.");
    }
    return $valor;
}
function retiro_configuracion(): array {
    $configuracion = solicitudes_configuracion();
    foreach (['RESEND_API_KEY', 'SOLICITUDES_RETIRO_TO_EMAIL', 'SOLICITUDES_RETIRO_FROM_EMAIL'] as $clave) {
        $entorno = getenv($clave);
        if ($entorno !== false && $entorno !== '') { $configuracion[$clave] = $entorno; }
    }
    return $configuracion;
}
function retiro_fecha_y_numero(): array {
    $fecha = new DateTimeImmutable('now', new DateTimeZone('America/Argentina/San_Juan'));
    return [$fecha->format('d/m/Y'), 'TEC-' . $fecha->format('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)))];
}
function retiro_limpiar_formularios_pendientes(): void {
    $formularios = $_SESSION['retiro_formularios'] ?? [];
    if (!is_array($formularios)) {
        $_SESSION['retiro_formularios'] = [];
        return;
    }

    $ahora = time();
    foreach ($formularios as $token => $formulario) {
        if (!is_array($formulario) || !is_int($formulario['creado'] ?? null) || $formulario['creado'] < $ahora - RETIRO_FORMULARIO_TTL) {
            unset($formularios[$token]);
        }
    }

    if (count($formularios) > RETIRO_MAX_FORMULARIOS_PENDIENTES) {
        uasort($formularios, static fn(array $a, array $b): int => $a['creado'] <=> $b['creado']);
        $formularios = array_slice($formularios, -RETIRO_MAX_FORMULARIOS_PENDIENTES, null, true);
    }

    $_SESSION['retiro_formularios'] = $formularios;
}
function retiro_token_formulario(string $numero, string $fecha): string {
    retiro_limpiar_formularios_pendientes();
    do {
        $token = bin2hex(random_bytes(32));
    } while (isset($_SESSION['retiro_formularios'][$token]));
    $_SESSION['retiro_formularios'][$token] = ['numero' => $numero, 'fecha' => $fecha, 'creado' => time()];
    retiro_limpiar_formularios_pendientes();
    return $token;
}
function retiro_formulario_pendiente(string $token): ?array {
    retiro_limpiar_formularios_pendientes();
    $formulario = $_SESSION['retiro_formularios'][$token] ?? null;
    if (!is_array($formulario) || !is_string($formulario['numero'] ?? null) || !preg_match('/\ATEC-[0-9]{8}-[A-F0-9]{6}\z/D', $formulario['numero']) || !is_string($formulario['fecha'] ?? null) || !preg_match('/\A[0-9]{2}\/[0-9]{2}\/[0-9]{4}\z/D', $formulario['fecha']) || !is_int($formulario['creado'] ?? null) || $formulario['creado'] < time() - RETIRO_FORMULARIO_TTL) {
        unset($_SESSION['retiro_formularios'][$token]);
        return null;
    }
    return $formulario;
}
function retiro_responder_json(int $estado, array $datos): never {
    http_response_code($estado); header('Content-Type: application/json; charset=utf-8'); echo json_encode($datos); exit;
}
function retiro_validar_url_maps(string $url): string {
    if ($url === '') { return ''; }
    $partes = filter_var($url, FILTER_VALIDATE_URL) ? parse_url($url) : false;
    if (!is_array($partes) || !in_array(strtolower((string) ($partes['scheme'] ?? '')), ['http', 'https'], true)) {
        throw new InvalidArgumentException('La URL de Google Maps debe ser HTTP o HTTPS válida.');
    }
    return $url;
}
function retiro_validar_solicitud(array $post): array {
    $generadorCodigo = strtoupper(retiro_requiere($post, 'generador_codigo', 'el código del generador', 4));
    if (!preg_match('/\A[A-Z][0-9]{3}\z/', $generadorCodigo)) { throw new InvalidArgumentException('El código del generador debe tener una letra y tres números.'); }
    $contacto = ['nombre' => retiro_requiere($post, 'contacto_nombre', 'nombre y apellido de contacto'), 'telefono' => retiro_requiere($post, 'contacto_telefono', 'teléfono de contacto'), 'email' => retiro_requiere($post, 'contacto_email', 'email de contacto')];
    if (!filter_var($contacto['email'], FILTER_VALIDATE_EMAIL)) { throw new InvalidArgumentException('Indicá un email de contacto válido.'); }
    $mismaPersona = ($post['hys_misma_persona'] ?? '') === '1';
    $hys = $mismaPersona ? $contacto : ['nombre' => retiro_requiere($post, 'hys_nombre', 'nombre y apellido de HyS'), 'telefono' => retiro_requiere($post, 'hys_telefono', 'teléfono de HyS'), 'email' => retiro_requiere($post, 'hys_email', 'email de HyS')];
    if (!filter_var($hys['email'], FILTER_VALIDATE_EMAIL)) { throw new InvalidArgumentException('Indicá un email de HyS válido.'); }
    $horariosCrudos = $post['horarios'] ?? []; if (!is_array($horariosCrudos) || count($horariosCrudos) < 1 || count($horariosCrudos) > RETIRO_MAX_BLOQUES) throw new InvalidArgumentException('Indicá entre uno y veinte horarios.');
    $horarios = [];
    foreach ($horariosCrudos as $horario) { if (!is_array($horario)) throw new InvalidArgumentException('El horario no es válido.'); $dia = retiro_requiere($horario, 'dia', 'día o rango de días', 100); $desde = retiro_requiere($horario, 'desde', 'hora desde', 5); $hasta = retiro_requiere($horario, 'hasta', 'hora hasta', 5); if (!preg_match('/\A(?:[01][0-9]|2[0-3]):[0-5][0-9]\z/', $desde) || !preg_match('/\A(?:[01][0-9]|2[0-3]):[0-5][0-9]\z/', $hasta) || $hasta <= $desde) throw new InvalidArgumentException('Cada horario debe tener una hora hasta posterior a desde.'); $horarios[] = compact('dia', 'desde', 'hasta'); }
    $domicilio = ['direccion' => retiro_requiere($post, 'domicilio_direccion', 'calle, ruta o establecimiento'), 'numero' => retiro_requiere($post, 'domicilio_numero', 'número, lote o km'), 'localidad' => retiro_requiere($post, 'domicilio_localidad', 'localidad o departamento'), 'provincia' => retiro_requiere($post, 'domicilio_provincia', 'provincia'), 'cp' => retiro_texto($post['domicilio_cp'] ?? '', 20), 'referencias' => retiro_texto($post['domicilio_referencias'] ?? '', 500), 'maps' => retiro_validar_url_maps(retiro_texto($post['domicilio_maps'] ?? '', 1000))];
    $manifiesto = retiro_requiere($post, 'manifiesto', 'si posee manifiesto'); if (!in_array($manifiesto, ['si', 'no'], true)) throw new InvalidArgumentException('Seleccioná el estado del manifiesto.');
    $numeroManifiesto = $manifiesto === 'si' ? retiro_texto($post['numero_manifiesto'] ?? '', 100) : '';
    if ($manifiesto === 'no' && ($post['manifiesto_confirmado'] ?? '') !== '1') throw new InvalidArgumentException('Debés confirmar la condición del manifiesto pendiente.');
    $residuosCrudos = $post['residuos'] ?? []; if (!is_array($residuosCrudos) || count($residuosCrudos) < 1 || count($residuosCrudos) > RETIRO_MAX_BLOQUES) throw new InvalidArgumentException('Indicá entre uno y veinte residuos.');
    $catalogo = solicitudes_catalogo_rrpp(); $residuos = []; $totales = ['m³' => 0.0, 'kg' => 0.0, 'L' => 0.0, 'tn' => 0.0];
    foreach ($residuosCrudos as $residuo) { if (!is_array($residuo)) throw new InvalidArgumentException('El residuo no es válido.'); $categoria = retiro_requiere($residuo, 'categoria', 'categoría RRPP', 20); if (!isset($catalogo[$categoria])) throw new InvalidArgumentException('La categoría RRPP no es válida.'); $contenedor = retiro_requiere($residuo, 'contenedor', 'tipo de contenedor', 30); $unidad = retiro_requiere($residuo, 'unidad', 'unidad', 5); $estado = retiro_requiere($residuo, 'estado', 'estado', 20); if (!in_array($contenedor, ['Contenedor','Bolsas','Tambores','Bidones','Bandeja','Bin'], true) || !in_array($unidad, array_keys($totales), true) || !in_array($estado, ['Sólido','Líquido','Semisólido'], true)) throw new InvalidArgumentException('Los datos del residuo no son válidos.'); $contenedores = filter_var($residuo['cantidad_contenedores'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]); if ($contenedores === false) throw new InvalidArgumentException('La cantidad de contenedores debe ser un entero mayor a cero.'); $cantidad = retiro_decimal($residuo['cantidad'] ?? '', 'la cantidad aproximada'); $totales[$unidad] += $cantidad; $residuos[] = ['categoria'=>$categoria,'descripcion'=>$catalogo[$categoria],'contenedor'=>$contenedor,'cantidad_contenedores'=>$contenedores,'cantidad'=>$cantidad,'unidad'=>$unidad,'estado'=>$estado,'detalle'=>retiro_texto($residuo['detalle'] ?? '', 1000)]; }
    $condiciones = ['auto_elevador'=>retiro_texto($post['auto_elevador'] ?? '', 10), 'personal_tecma'=>retiro_texto($post['personal_tecma'] ?? '', 10), 'personal_cantidad'=>'', 'electricidad'=>retiro_texto($post['electricidad'] ?? '', 10), 'electricidad_tipo'=>'', 'electricidad_distancia'=>'', 'observaciones'=>retiro_texto($post['condiciones_observaciones'] ?? '', 1000)];
    foreach (['auto_elevador','personal_tecma','electricidad'] as $campo) if ($condiciones[$campo] !== '' && !in_array($condiciones[$campo], ['si','no','no_se'], true)) throw new InvalidArgumentException('Las condiciones operativas no son válidas.');
    if ($condiciones['personal_tecma'] === 'si') {
        $cantidadPersonal = retiro_texto($post['personal_cantidad'] ?? '', 20);
        if ($cantidadPersonal !== '' && (!ctype_digit($cantidadPersonal) || (int) $cantidadPersonal < 1)) throw new InvalidArgumentException('La cantidad de personal debe ser un entero mayor o igual a uno.');
        $condiciones['personal_cantidad'] = $cantidadPersonal;
    }
    if ($condiciones['electricidad'] === 'si') {
        $tipoElectricidad = retiro_texto($post['electricidad_tipo'] ?? '', 20);
        if ($tipoElectricidad !== '' && !in_array($tipoElectricidad, ['Monofásica', 'Trifásica', 'No sé'], true)) throw new InvalidArgumentException('El tipo de electricidad no es válido.');
        $condiciones['electricidad_tipo'] = $tipoElectricidad;
        $distanciaElectricidad = retiro_texto($post['electricidad_distancia'] ?? '', 20);
        $condiciones['electricidad_distancia'] = $distanciaElectricidad === '' ? '' : retiro_decimal_no_negativo($distanciaElectricidad, 'la distancia eléctrica');
    }
    $recambio = retiro_requiere($post, 'recambio', 'si necesita recambio'); if (!in_array($recambio, ['si','no'], true)) throw new InvalidArgumentException('Seleccioná si necesita recambio.'); $recambios = []; foreach (['contenedor'=>'Contenedor','tambor'=>'Tambor metálico de 200 L','bin'=>'Bin'] as $clave=>$nombre) { $cantidad = retiro_texto($post['recambio_'.$clave] ?? '', 10); if ($cantidad !== '') { if (!ctype_digit($cantidad) || (int)$cantidad < 1) throw new InvalidArgumentException('Las cantidades de recambio deben ser enteros mayores a cero.'); $recambios[$nombre]=(int)$cantidad; } } if ($recambio === 'si' && !$recambios) throw new InvalidArgumentException('Indicá al menos un recambio.');
    $oc = retiro_requiere($post, 'orden_compra', 'si posee orden de compra'); if (!in_array($oc, ['si','no'], true)) throw new InvalidArgumentException('Seleccioná si posee orden de compra.'); if (($post['confirmacion'] ?? '') !== '1') throw new InvalidArgumentException('Debés declarar que la información es correcta.');
    return compact('generadorCodigo','contacto','hys','mismaPersona','horarios','domicilio','manifiesto','numeroManifiesto','residuos','totales','condiciones','recambio','recambios','oc') + ['generadorNombre'=>retiro_requiere($post, 'generador_nombre', 'nombre o razón social'), 'observaciones'=>retiro_texto($post['observaciones'] ?? '', 2000)];
}
function retiro_adjunto_oc(array $archivo, string $oc): ?array { if ($oc === 'no') return null; $temporal = $archivo['tmp_name'] ?? null; $tamano = $archivo['size'] ?? null; if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_string($temporal) || !is_int($tamano) || !is_uploaded_file($temporal) || $tamano > 5 * 1024 * 1024) throw new InvalidArgumentException('Adjuntá una orden de compra PDF de hasta 5 MB.'); $finfo = new finfo(FILEINFO_MIME_TYPE); $contenido = file_get_contents($temporal); if ($contenido === false || $finfo->file($temporal) !== 'application/pdf' || !str_starts_with($contenido, '%PDF-')) throw new InvalidArgumentException('La orden de compra debe ser un PDF válido.'); return ['filename'=>'orden-de-compra.pdf','content'=>base64_encode($contenido)]; }
function retiro_fila(string $etiqueta, string $valor): string { return '<tr><th>' . retiro_escape($etiqueta) . '</th><td>' . nl2br(retiro_escape($valor)) . '</td></tr>'; }
function retiro_condicion(string $etiqueta, string $valor): string { return '<p><strong>' . retiro_escape($etiqueta) . ':</strong> ' . retiro_escape($valor) . '</p>'; }
function retiro_html_email(array $d, string $numero, string $fecha, bool $interno): string {
    $residuos = '';
    foreach ($d['residuos'] as $r) {
        $residuos .= '<li><strong>' . retiro_escape($r['categoria']) . '</strong>: ' . retiro_escape($r['descripcion'])
            . ' — ' . retiro_escape((string) $r['cantidad']) . ' ' . retiro_escape($r['unidad'])
            . ', ' . retiro_escape($r['contenedor']) . ' x ' . retiro_escape((string) $r['cantidad_contenedores'])
            . ', ' . retiro_escape($r['estado']) . ($r['detalle'] ? '<br>' . nl2br(retiro_escape($r['detalle'])) : '') . '</li>';
    }
    $horarios = implode(' | ', array_map(fn(array $h): string => $h['dia'] . ': ' . $h['desde'] . ' a ' . $h['hasta'], $d['horarios']));
    $totales = []; foreach ($d['totales'] as $unidad => $cantidad) if ($cantidad > 0) $totales[] = $cantidad . ' ' . $unidad;
    $recambios = $d['recambio'] === 'si' ? implode(' · ', array_map(fn(string $tipo, int $cantidad): string => $tipo . ': ' . $cantidad, array_keys($d['recambios']), $d['recambios'])) : 'No solicitado';
    $alerta = $d['manifiesto'] === 'no' ? '<p style="padding:12px;background:#ffe4e1;color:#8b1e1e;font-weight:bold">NO PROGRAMAR — PENDIENTE DE MANIFIESTO</p>' : '';
    $maps = $d['domicilio']['maps'] ? '<a href="' . retiro_escape($d['domicilio']['maps']) . '">Ver ubicación en Google Maps</a>' : 'No informado';
    $tabla = retiro_fila('Generador', $d['generadorCodigo'] . ' — ' . $d['generadorNombre'])
        . retiro_fila('Contacto', implode(' | ', $d['contacto'])) . retiro_fila('Higiene y Seguridad', implode(' | ', $d['hys']))
        . retiro_fila('Horarios', $horarios) . retiro_fila('Domicilio', implode(', ', array_filter([$d['domicilio']['direccion'], $d['domicilio']['numero'], $d['domicilio']['localidad'], $d['domicilio']['provincia'], $d['domicilio']['cp']])))
        . retiro_fila('Manifiesto', $d['manifiesto'] === 'si' ? 'Sí' . ($d['numeroManifiesto'] ? ': ' . $d['numeroManifiesto'] : '') : 'Pendiente de manifiesto') . retiro_fila('Orden de compra', $d['oc'] === 'si' ? 'Recibida' : 'No informada')
        . retiro_fila('Recambio', $recambios);
    $referencias = $d['domicilio']['referencias'] !== '' ? retiro_fila('Referencias del domicilio', $d['domicilio']['referencias']) : '';
    $tabla .= $interno ? $referencias : '';
    $detalleInterno = $interno
        ? '<p><strong>Google Maps:</strong> ' . $maps . '</p><h2>Condiciones operativas</h2>'
            . retiro_condicion('Autoelevador', ['si' => 'Sí', 'no' => 'No', 'no_se' => 'No sé', '' => 'No informado'][$d['condiciones']['auto_elevador']])
            . retiro_condicion('Personal adicional TeCMA', ['si' => 'Sí', 'no' => 'No', 'no_se' => 'No sé', '' => 'No informado'][$d['condiciones']['personal_tecma']])
            . ($d['condiciones']['personal_tecma'] === 'si' && $d['condiciones']['personal_cantidad'] !== '' ? retiro_condicion('Cantidad aproximada', $d['condiciones']['personal_cantidad']) : '')
            . retiro_condicion('Electricidad', ['si' => 'Sí', 'no' => 'No', 'no_se' => 'No sé', '' => 'No informado'][$d['condiciones']['electricidad']])
            . ($d['condiciones']['electricidad'] === 'si' && $d['condiciones']['electricidad_tipo'] !== '' ? retiro_condicion('Tipo', $d['condiciones']['electricidad_tipo']) : '')
            . ($d['condiciones']['electricidad'] === 'si' && $d['condiciones']['electricidad_distancia'] !== '' ? retiro_condicion('Distancia', $d['condiciones']['electricidad_distancia'] . ' m') : '')
            . ($d['condiciones']['observaciones'] !== '' ? retiro_condicion('Observaciones de acceso/carga/equipamiento', $d['condiciones']['observaciones']) : '')
            . ($d['observaciones'] !== '' ? '<p><strong>Observaciones generales:</strong><br>' . nl2br(retiro_escape($d['observaciones'])) . '</p>' : '')
        : '<p>Solicitud recibida no significa retiro programado.</p>';
    return '<!doctype html><html><body style="font-family:Arial,sans-serif;color:#252525">' . $alerta . '<h1>' . ($interno ? 'Nueva solicitud de retiro' : 'Solicitud recibida') . '</h1><p><strong>Número:</strong> ' . retiro_escape($numero) . '<br><strong>Fecha:</strong> ' . retiro_escape($fecha) . '</p><table><tbody>' . $tabla . '</tbody></table><h2>Residuos</h2><ul>' . $residuos . '</ul><p><strong>Totales:</strong> ' . retiro_escape(implode(' · ', $totales)) . '</p>' . $detalleInterno . '</body></html>';
}
function retiro_enviar_resend(array $config, array $payload, string $clave): bool { if (!function_exists('curl_init')) throw new RuntimeException('El servidor no tiene cURL disponible para enviar la solicitud.'); $curl=curl_init('https://api.resend.com/emails'); curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>20,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$config['RESEND_API_KEY'],'Content-Type: application/json','Idempotency-Key: '.$clave],CURLOPT_POSTFIELDS=>json_encode($payload)]); $respuesta=curl_exec($curl); $estado=(int)curl_getinfo($curl,CURLINFO_RESPONSE_CODE); curl_close($curl); return $respuesta !== false && $estado >= 200 && $estado < 300; }
