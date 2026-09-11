# Módulo de solicitudes

## Objetivo

`/solicitudes/` es un módulo independiente para trámites de clientes y generadores autorizados. Esta primera fase incorpora únicamente el acceso protegido y la base visual de la Solicitud de Retiro.

La Solicitud de Retiro se procesa por correo mediante Resend, sin persistir datos ni adjuntos en el servidor. En etapas posteriores podrán incorporarse otros trámites bajo `/solicitudes/<tipo>/`.

## Estructura y despliegue

El módulo se mantiene autocontenido en `solicitudes/`, con su propia autenticación, estilos e imagen institucional. No depende de recursos de la landing ubicada en `/web/`.

```text
solicitudes/
├── index.php
├── logout.php
├── includes/auth.php
├── includes/catalogo_rrpp.php
├── includes/retiro.php
├── retiro/index.php
├── retiro/enviar.php
└── assets/
    ├── css/{solicitudes,retiro}.css
    ├── js/retiro.js
    └── img/tecma-logo.png
```

En DonWeb se prevé copiar esa carpeta a `public_html/solicitudes/`. La landing puede continuar publicada independientemente en `public_html/web/`.

## Configuración de acceso

La clave real no se versiona. El módulo busca primero el archivo externo:

```text
dirname($_SERVER['DOCUMENT_ROOT']) . '/secure/tecma-solicitudes.php'
```

El archivo debe estar fuera de `public_html`, tener permisos restrictivos y retornar el hash:

```php
<?php

return [
    'SOLICITUDES_ACCESS_PASSWORD_HASH' => 'HASH_REAL',
];
```

Como alternativa, el hosting puede proveer la variable de entorno `SOLICITUDES_ACCESS_PASSWORD_HASH`. El valor siempre debe generarse con `password_hash()` y se valida con `password_verify()`; nunca se usa ni se guarda una contraseña en texto plano.

## Configuración de Retiro

El mismo archivo externo puede contener la configuración de envío. También se admiten variables de entorno, que prevalecen sobre el archivo:

```php
return [
    'SOLICITUDES_ACCESS_PASSWORD_HASH' => 'HASH_REAL',
    'RESEND_API_KEY' => 're_xxx',
    'SOLICITUDES_RETIRO_TO_EMAIL' => 'operaciones@ejemplo.com',
    'SOLICITUDES_RETIRO_FROM_EMAIL' => 'TeCMA <solicitudes@ejemplo.com>',
];
```

`SOLICITUDES_RETIRO_FROM_EMAIL` debe ser una dirección remitente verificada en Resend. El correo del cliente solo se usa como `Reply-To`; no debe configurarse como remitente. El servidor debe tener habilitada la extensión cURL. Las órdenes de compra se validan como PDF, se usan únicamente como adjunto del correo interno y se eliminan al finalizar la petición.

Para generar el hash en un entorno PHP seguro:

```bash
php -r "echo password_hash('clave-a-definir', PASSWORD_DEFAULT), PHP_EOL;"
```

## Sesión

La sesión usa el nombre propio `tecma_solicitudes`, cookies `HttpOnly`, `SameSite=Lax` y `Secure` cuando la conexión es HTTPS. Al autenticar se regenera el identificador de sesión. Las rutas protegidas redirigen a `/solicitudes/` si no hay una sesión válida, y `logout.php` finaliza la sesión.

Las páginas emiten directivas `noindex, nofollow`; esto reduce la exposición a buscadores, pero el control de acceso efectivo es la autenticación de sesión.

## HTTPS y limitación de intentos

El módulo requiere HTTPS antes de procesar credenciales, iniciar una sesión o ejecutar el cierre de sesión. Las solicitudes HTTP se redirigen al origen canónico `https://www.tecmasanjuan.com.ar/solicitudes/`, sin construir la URL a partir de encabezados del cliente.

Los intentos fallidos se limitan por `REMOTE_ADDR`: después de 5 fallos, esa dirección queda bloqueada durante 15 minutos. El estado se guarda con bloqueo exclusivo de archivo en `dirname($_SERVER['DOCUMENT_ROOT']) . '/secure/tecma-solicitudes-rate-limit/'`, fuera de `public_html`, y se restablece tras una autenticación válida. Durante los accesos se inspeccionan como máximo 20 archivos para eliminar estados sin bloqueo vigente que lleven más de 24 horas inactivos; un cursor privado rota la ventana de inspección entre ejecuciones. Los archivos en uso o corruptos recientes se conservan.

En cPanel, el usuario que ejecuta PHP debe poder crear y escribir ese directorio dentro de `secure/`. Si no puede guardar el estado de rate limiting, el acceso se rechaza de forma segura.
