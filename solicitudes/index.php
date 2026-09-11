<?php
declare(strict_types=1);

require __DIR__ . '/includes/auth.php';

solicitudes_requiere_https();
solicitudes_evitar_cache();
solicitudes_iniciar_sesion();
solicitudes_no_indexar();

if (solicitudes_tiene_sesion_valida()) {
    header('Location: /solicitudes/retiro/');
    exit;
}

$mensaje = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $clave = (string) ($_POST['clave'] ?? '');
    $resultado = solicitudes_autenticar($clave);

    if ($resultado === 'autenticada') {
        header('Location: /solicitudes/retiro/');
        exit;
    }
    if ($resultado === 'bloqueada' || $resultado === 'no_disponible') {
        $mensaje = 'El acceso no está disponible en este momento.';
    } else {
        $mensaje = 'La clave de acceso no es válida.';
    }
}
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Solicitudes | TeCMA San Juan</title>
    <link rel="stylesheet" href="assets/css/solicitudes.css" />
  </head>
  <body>
    <main class="access-page">
      <section class="access-card" aria-labelledby="solicitudes-title">
        <img class="brand-logo" src="assets/img/tecma-logo.png" alt="TeCMA San Juan" />
        <p class="eyebrow">TeCMA San Juan</p>
        <h1 id="solicitudes-title">Solicitudes</h1>
        <p class="intro">Acceso para clientes y generadores autorizados.</p>
        <form method="post" class="access-form">
          <label for="clave">Clave de acceso</label>
          <input id="clave" name="clave" type="password" autocomplete="current-password" required />
          <?php if ($mensaje !== ''): ?>
            <p class="form-message" role="alert"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>
          <button type="submit">Ingresar</button>
        </form>
      </section>
    </main>
  </body>
</html>
