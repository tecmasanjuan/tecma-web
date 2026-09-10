<?php
declare(strict_types=1);

require dirname(__DIR__) . '/includes/auth.php';

solicitudes_no_indexar();
solicitudes_requiere_autenticacion();
?>
<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow" />
    <title>Solicitud de Retiro | TeCMA San Juan</title>
    <link rel="stylesheet" href="../assets/css/solicitudes.css" />
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
        <div class="request-content" aria-label="Formulario de solicitud de retiro"></div>
      </section>
    </main>
  </body>
</html>
