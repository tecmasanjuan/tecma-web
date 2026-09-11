<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/auth.php';
require dirname(__DIR__) . '/includes/retiro.php';

solicitudes_evitar_cache();
solicitudes_no_indexar();
solicitudes_requiere_autenticacion();
[$fechaSolicitud, $numeroSolicitud] = retiro_fecha_y_numero();
$tokenFormulario = retiro_token_formulario($numeroSolicitud, $fechaSolicitud);
$catalogoRrpp = solicitudes_catalogo_rrpp();
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Solicitud de Retiro | TeCMA San Juan</title>
    <link rel="stylesheet" href="../assets/css/solicitudes.css" />
    <link rel="stylesheet" href="../assets/css/retiro.css" />
  </head>
  <body>
    <main class="module-page">
      <header class="module-header">
        <img class="brand-logo" src="../assets/img/tecma-logo.png" alt="TeCMA San Juan" />
        <a class="logout-link" href="../logout.php">Cerrar sesión</a>
      </header>
      <section class="request-card" aria-labelledby="retiro-title">
        <p class="eyebrow">Solicitudes</p>
        <h1 id="retiro-title">Solicitud de Retiro</h1>
        <p class="intro">Completá aquí la información necesaria para solicitar un retiro.</p>
        <div class="request-content">
          <form id="retiro-form" class="retiro-form" action="enviar.php" method="post" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="token" value="<?= retiro_escape($tokenFormulario) ?>" />
            <div class="form-status" role="alert" aria-live="polite" tabindex="-1"></div>
            <section><h2>Datos de la solicitud</h2><div class="form-grid"><label>Fecha de solicitud<input value="<?= retiro_escape($fechaSolicitud) ?>" readonly /></label><label>Número de solicitud<input value="<?= retiro_escape($numeroSolicitud) ?>" readonly /></label></div></section>
            <section><h2>Generador</h2><div class="form-grid"><label>Código<input name="generador_codigo" maxlength="4" pattern="[A-Za-z][0-9]{3}" required /></label><label>Nombre / Razón social<input name="generador_nombre" maxlength="300" required /></label></div></section>
            <section><h2>Contacto</h2><div class="form-grid"><label>Nombre y apellido<input name="contacto_nombre" maxlength="300" required /></label><label>Teléfono<input name="contacto_telefono" maxlength="100" required /></label><label>Email<input name="contacto_email" type="email" maxlength="300" required /></label></div></section>
            <section><h2>Responsable de Higiene y Seguridad</h2><label class="check"><input type="checkbox" name="hys_misma_persona" value="1" /> Es la misma persona de contacto</label><div class="form-grid" id="hys-fields"><label>Nombre y apellido<input name="hys_nombre" maxlength="300" required /></label><label>Teléfono<input name="hys_telefono" maxlength="100" required /></label><label>Email<input name="hys_email" type="email" maxlength="300" required /></label></div></section>
            <section><div class="section-heading"><h2>Horarios de atención</h2><button type="button" class="secondary add-item" data-add="horario">Agregar horario</button></div><div id="horarios" class="repeat-list"></div></section>
            <section><h2>Domicilio</h2><div class="form-grid"><label>Calle / Ruta / Establecimiento<input name="domicilio_direccion" maxlength="300" required /></label><label>Número / Lote / Km<input name="domicilio_numero" maxlength="100" required /></label><label>Localidad / Departamento<input name="domicilio_localidad" maxlength="200" required /></label><label>Provincia<input name="domicilio_provincia" value="San Juan" maxlength="100" required /></label><label>CP<input name="domicilio_cp" maxlength="20" /></label><label>Google Maps<input name="domicilio_maps" type="url" maxlength="1000" placeholder="https://…" /></label></div><label>Referencias<textarea name="domicilio_referencias" maxlength="500"></textarea></label></section>
            <section><h2>Manifiesto</h2><fieldset><legend>¿Posee actualmente el manifiesto de control requerido para realizar el retiro?</legend><label class="radio"><input type="radio" name="manifiesto" value="si" required /> Sí</label><label class="radio"><input type="radio" name="manifiesto" value="no" /> No</label></fieldset><label class="conditional" data-when="manifiesto:si">Número de manifiesto (opcional)<input name="numero_manifiesto" maxlength="100" disabled /></label><div class="conditional warning" data-when="manifiesto:no"><p>TeCMA no podrá programar ni realizar el retiro sin el manifiesto requerido.</p><label class="check"><input type="checkbox" name="manifiesto_confirmado" value="1" disabled /> Comprendo esta condición.</label></div></section>
            <section><div class="section-heading"><h2>Residuos peligrosos</h2><button type="button" class="secondary add-item" data-add="residuo">Agregar residuo</button></div><div id="residuos" class="repeat-list"></div><div class="totals" aria-live="polite"><strong>Totales aproximados:</strong><span data-total="m³">0 m³</span><span data-total="kg">0 kg</span><span data-total="L">0 L</span><span data-total="tn">0 tn</span></div></section>
            <section><h2>Condiciones operativas <small>(opcional)</small></h2><p class="help">Esta información ayuda a determinar el equipamiento y personal necesarios.</p><div class="form-grid"><label>Autoelevador<select name="auto_elevador"><option value="">No informado</option><option value="si">Sí</option><option value="no">No</option><option value="no_se">No sé</option></select></label><label>Personal adicional TeCMA<select name="personal_tecma"><option value="">No informado</option><option value="si">Sí</option><option value="no">No</option><option value="no_se">No sé</option></select></label><label class="conditional" data-when="personal_tecma:si">Cantidad aproximada<input name="personal_cantidad" inputmode="numeric" maxlength="20" disabled /></label><label>Electricidad<select name="electricidad"><option value="">No informado</option><option value="si">Sí</option><option value="no">No</option><option value="no_se">No sé</option></select></label><label class="conditional" data-when="electricidad:si">Tipo<select name="electricidad_tipo" disabled><option value="">Seleccionar</option><option>Monofásica</option><option>Trifásica</option><option>No sé</option></select></label><label class="conditional" data-when="electricidad:si">Distancia aproximada (m)<input name="electricidad_distancia" inputmode="decimal" maxlength="20" disabled /></label></div><label>Observaciones de acceso, carga o equipamiento<textarea name="condiciones_observaciones" maxlength="1000"></textarea></label></section>
            <section><h2>Recambio</h2><fieldset><legend>¿Necesita recambio de recipientes?</legend><label class="radio"><input type="radio" name="recambio" value="si" required /> Sí</label><label class="radio"><input type="radio" name="recambio" value="no" /> No</label></fieldset><div class="conditional form-grid" data-when="recambio:si"><label>Contenedor<input name="recambio_contenedor" inputmode="numeric" maxlength="10" disabled /></label><label>Tambor metálico de 200 L<input name="recambio_tambor" inputmode="numeric" maxlength="10" disabled /></label><label>Bin<input name="recambio_bin" inputmode="numeric" maxlength="10" disabled /></label></div><p class="help">El recambio queda sujeto a coordinación y disponibilidad de TeCMA.</p></section>
            <section><h2>Orden de compra</h2><fieldset><legend>¿Posee orden de compra para este retiro?</legend><label class="radio"><input type="radio" name="orden_compra" value="si" required /> Sí</label><label class="radio"><input type="radio" name="orden_compra" value="no" /> No</label></fieldset><label class="conditional" data-when="orden_compra:si">PDF de orden de compra (máximo 5 MB)<input name="orden_compra_pdf" type="file" accept="application/pdf" disabled /></label></section>
            <section><h2>Observaciones</h2><label>Información adicional<textarea name="observaciones" maxlength="2000"></textarea></label></section>
            <label class="check confirmation"><input type="checkbox" name="confirmacion" value="1" required /> Declaro que la información ingresada es correcta.</label><button class="submit" type="submit">Enviar solicitud de retiro</button>
          </form>
        </div>
      </section>
    </main>
  </body>
  <template id="horario-template"><div class="repeat-card horario"><div class="form-grid"><label>Día o rango de días<input name="horarios[__INDEX__][dia]" list="dias-sugeridos" maxlength="100" required /></label><label>Desde<input type="time" name="horarios[__INDEX__][desde]" required /></label><label>Hasta<input type="time" name="horarios[__INDEX__][hasta]" required /></label></div><button type="button" class="remove-item secondary">Eliminar horario</button></div></template>
  <datalist id="dias-sugeridos"><option>Lunes a viernes</option><option>Lunes a sábado</option><option>Lunes</option><option>Martes</option><option>Miércoles</option><option>Jueves</option><option>Viernes</option><option>Sábado</option></datalist>
  <template id="residuo-template"><div class="repeat-card residuo"><div class="form-grid"><label>Buscar categoría<input class="rrpp-search" type="search" placeholder="Código o descripción" /></label><label>Categoría<select class="rrpp-category" name="residuos[__INDEX__][categoria]" required><option value="">Seleccionar</option><?php foreach ($catalogoRrpp as $codigo => $descripcion): ?><option value="<?= retiro_escape($codigo) ?>" data-description="<?= retiro_escape($descripcion) ?>"><?= retiro_escape($codigo) ?> — <?= retiro_escape($descripcion) ?></option><?php endforeach; ?></select></label></div><p class="rrpp-description" aria-live="polite"></p><div class="form-grid"><label>Tipo de contenedor<select name="residuos[__INDEX__][contenedor]" required><option value="">Seleccionar</option><option>Contenedor</option><option>Bolsas</option><option>Tambores</option><option>Bidones</option><option>Bandeja</option><option>Bin</option></select></label><label>Cantidad de contenedores<input name="residuos[__INDEX__][cantidad_contenedores]" type="number" min="1" step="1" required /></label><label>Cantidad aproximada<input class="residuo-cantidad" name="residuos[__INDEX__][cantidad]" inputmode="decimal" required /></label><label>Unidad<select class="residuo-unidad" name="residuos[__INDEX__][unidad]" required><option value="">Seleccionar</option><option>m³</option><option>kg</option><option>L</option><option>tn</option></select></label><label>Estado<select name="residuos[__INDEX__][estado]" required><option value="">Seleccionar</option><option>Sólido</option><option>Líquido</option><option>Semisólido</option></select></label></div><label>Detalle del residuo (opcional)<textarea name="residuos[__INDEX__][detalle]" maxlength="1000"></textarea></label><button type="button" class="remove-item secondary">Eliminar residuo</button></div></template>
  <script src="../assets/js/retiro.js" defer></script>
</html>
