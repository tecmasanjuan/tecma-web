# Formulario de contacto

El formulario de la landing conserva sus campos visibles actuales (`nombre`, `email` y
`mensaje`) y envía JSON mediante `POST` a `/api/contacto.php`. El endpoint valida los datos,
guarda primero la consulta en Supabase y luego intenta notificar por Resend. Un fallo de Resend
no elimina la consulta guardada.

## Configuración

El backend necesita estas variables, configuradas en el hosting y nunca en HTML, JavaScript o Git:

- `SUPABASE_URL`
- `SUPABASE_SERVICE_ROLE_KEY`
- `RESEND_API_KEY`
- `CONTACT_FORM_TO_EMAIL`

En cPanel, la opción preferida es cargarlas como variables de entorno de la aplicación PHP. Si
el hosting no las expone correctamente, `contacto.php` también acepta un archivo fuera de
`public_html`, en `../secure/tecma-contacto.php` respecto del document root, que retorne un array
PHP con esas mismas claves. Ese archivo debe tener permisos restrictivos y no debe versionarse.

Ejemplo de estructura fuera del repositorio (sin valores reales en este documento):

```php
<?php
return [
    'SUPABASE_URL' => 'https://proyecto.supabase.co',
    'SUPABASE_SERVICE_ROLE_KEY' => 'configurar-en-el-hosting',
    'RESEND_API_KEY' => 'configurar-en-el-hosting',
    'CONTACT_FORM_TO_EMAIL' => 'destino@dominio.com',
];
```

## Supabase

Ejecutar una vez `supabase/migrations/20260907200000_create_consultas_web.sql` en el proyecto
Supabase. La tabla habilita RLS y no crea políticas públicas de inserción: el backend usa la
credencial de servidor.

## Prueba

1. Configurar las cuatro variables en el hosting.
2. Ejecutar el SQL de setup en Supabase.
3. Publicar `api/contacto.php` junto con la landing.
4. Completar el formulario desde la URL pública y verificar el registro en `consultas_web` y la
   recepción del email.

El frontend bloquea el botón durante el envío, informa estados mediante un mensaje accesible y
solo limpia los campos cuando Supabase confirmó la recepción.
