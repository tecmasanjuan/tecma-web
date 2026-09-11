# Módulo de solicitudes

## Objetivo

`/solicitudes/` es un módulo independiente para trámites de clientes y generadores autorizados. Esta primera fase incorpora únicamente el acceso protegido y la base visual de la Solicitud de Retiro.

La persistencia y los campos operativos del retiro quedan fuera de esta fase. En etapas posteriores podrán incorporarse otros trámites bajo `/solicitudes/<tipo>/`.

## Estructura y despliegue

El módulo se mantiene autocontenido en `solicitudes/`, con su propia autenticación, estilos e imagen institucional. No depende de recursos de la landing ubicada en `/web/`.

```text
solicitudes/
├── index.php
├── logout.php
├── includes/auth.php
├── retiro/index.php
└── assets/
    ├── css/solicitudes.css
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

Para generar el hash en un entorno PHP seguro:

```bash
php -r "echo password_hash('clave-a-definir', PASSWORD_DEFAULT), PHP_EOL;"
```

## Sesión

La sesión usa el nombre propio `tecma_solicitudes`, cookies `HttpOnly`, `SameSite=Lax` y `Secure` cuando la conexión es HTTPS. Al autenticar se regenera el identificador de sesión. Las rutas protegidas redirigen a `/solicitudes/` si no hay una sesión válida, y `logout.php` finaliza la sesión.

Las páginas emiten directivas `noindex, nofollow`; esto reduce la exposición a buscadores, pero el control de acceso efectivo es la autenticación de sesión.
