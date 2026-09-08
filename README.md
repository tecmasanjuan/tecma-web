# TeCMA San Juan — Sitio Web Institucional

Sitio web institucional de **TeCMA San Juan**, desarrollado para presentar la empresa, sus servicios, medios de contacto e información relevante para sus clientes y generadores.

El proyecto busca ofrecer una presencia web moderna, clara, accesible y adaptable a dispositivos móviles, manteniendo una arquitectura simple y preparada para incorporar nuevas funcionalidades de manera progresiva.

> **Importante:** este proyecto no se distribuye como software open source. La disponibilidad pública del repositorio no implica autorización para reutilizar, redistribuir o comercializar su código fuera de las condiciones indicadas en [`LICENSE.md`](LICENSE.md).

---

## 🌱 Sobre TeCMA San Juan

**TeCMA San Juan** brinda servicios relacionados con la gestión responsable de residuos, acompañando a empresas, instituciones y organizaciones en necesidades vinculadas con su recolección, transporte, tratamiento y disposición.

El sitio web funciona principalmente como canal institucional, informativo y de comunicación.

---

## 🎯 Objetivo del proyecto

El objetivo de la web es centralizar la información pública de TeCMA San Juan y proporcionar un acceso sencillo a:

* información institucional;
* servicios ofrecidos;
* gestión de residuos;
* información de contacto;
* pago mediante código QR;
* condiciones de beneficios comerciales vigentes;
* contacto directo mediante WhatsApp;
* futuras herramientas de comunicación con clientes y generadores.

La web no funciona actualmente como sistema administrativo ni como portal completo de autogestión.

---

## 🏗️ Estado actual

El proyecto dispone de una landing institucional funcional.

### Implementado

* diseño responsive;
* identidad visual institucional;
* navegación por secciones;
* presentación de la empresa;
* sección de servicios;
* recolección y transporte de residuos;
* tratamiento de residuos;
* consultas sobre residuos patogénicos, peligrosos e industriales;
* servicio de Residuos Sólidos Urbanos (RSU);
* sección de pago mediante Mercado Pago;
* código QR de pago;
* condiciones del beneficio comercial;
* información de contacto;
* acceso directo mediante WhatsApp;
* enlaces externos seguros;
* formulario de contacto con backend PHP, persistencia en Supabase y notificación por Resend;
* estructura preparada para futuras ampliaciones.

### Próximas etapas evaluadas

Entre las funcionalidades consideradas para versiones posteriores se encuentran:

* suscripción de generadores a novedades;
* validación de datos de generadores;
* envío de comunicaciones por correo electrónico;
* integración con servicios externos;
* mejoras de automatización;
* posibles herramientas privadas para clientes o generadores.

Estas funcionalidades no forman necesariamente parte del MVP actual.

---

## 🧰 Tecnologías

La versión actual prioriza una arquitectura sencilla, mantenible y con bajo costo operativo.

### Frontend

* HTML5
* CSS3
* diseño responsive
* JavaScript únicamente cuando una funcionalidad lo requiera

### Infraestructura

La landing puede servirse como sitio estático, pero el formulario requiere un hosting con PHP y
cURL para ejecutar `api/contacto.php`.

La infraestructura de producción debe mantenerse separada del código fuente siempre que implique credenciales, secretos o configuración sensible.

---

## 📁 Estructura del proyecto

```text
tecma-web/
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
│
├── api/
│   └── contacto.php
│
├── docs/
│   ├── alcance-mvp.md
│   ├── decisiones-proyecto.md
│   ├── formulario-contacto.md
│   ├── futuro-no-mvp.md
│   ├── hosting-correo.md
│   ├── legado-sitio-actual.md
│   ├── pago-qr-mercadopago.md
│   └── suscripcion-generadores.md
│
├── supabase/
│   └── migrations/
│       └── 20260907200000_create_consultas_web.sql
│
├── legacy/
│
├── .gitignore
├── index.html
├── LICENSE.md
└── README.md
```

### `index.html`

Documento principal del sitio institucional.

### `assets/`

Recursos utilizados por la interfaz:

* hojas de estilo;
* imágenes;
* logotipos;
* favicon;
* código QR;
* recursos visuales.

### `docs/`

Documentación funcional y técnica del proyecto.

Las decisiones importantes deben documentarse en esta carpeta para que el conocimiento del proyecto no dependa únicamente del código o de conversaciones externas.

### `legacy/`

Material proveniente de versiones anteriores del sitio.

Se conserva únicamente como referencia histórica y no forma parte de la arquitectura actual salvo decisión expresa.

---

## 📚 Documentación del proyecto

### Alcance del MVP

[`docs/alcance-mvp.md`](docs/alcance-mvp.md)

Define las funcionalidades incluidas en la primera etapa del proyecto.

### Decisiones del proyecto

[`docs/decisiones-proyecto.md`](docs/decisiones-proyecto.md)

Registro de decisiones funcionales y técnicas relevantes.

### Formulario de contacto

[`docs/formulario-contacto.md`](docs/formulario-contacto.md)

Implementación, configuración y prueba del formulario de contacto.

### Funcionalidades futuras

[`docs/futuro-no-mvp.md`](docs/futuro-no-mvp.md)

Funcionalidades evaluadas pero excluidas del MVP actual.

### Hosting y correo

[`docs/hosting-correo.md`](docs/hosting-correo.md)

Información y decisiones relacionadas con hosting, dominio, DNS y correo electrónico.

### Sitio anterior

[`docs/legado-sitio-actual.md`](docs/legado-sitio-actual.md)

Referencia del contenido proveniente de versiones anteriores del sitio.

### Mercado Pago

[`docs/pago-qr-mercadopago.md`](docs/pago-qr-mercadopago.md)

Documentación relacionada con el QR de Mercado Pago y las condiciones comerciales asociadas.

### Suscripción de generadores

[`docs/suscripcion-generadores.md`](docs/suscripcion-generadores.md)

Diseño preliminar de una futura funcionalidad de suscripción a comunicaciones.

---

## 💳 Pago mediante Mercado Pago

La web permite visualizar el código QR utilizado por TeCMA San Juan para recibir pagos mediante Mercado Pago.

El beneficio actualmente comunicado contempla:

* **5 % de descuento en la próxima factura**;
* pago utilizando **dinero disponible en la cuenta de Mercado Pago**;
* no válido mediante tarjetas de crédito;
* no válido mediante tarjetas de débito;
* no válido mediante otros medios de pago vinculados;
* pagos realizados entre los días **1 y 15 de cada mes**;
* sujeto a **validación administrativa**.

La publicación del QR no constituye una integración con la API de Mercado Pago.

---

## 📱 Contacto y WhatsApp

El sitio proporciona diferentes canales de contacto con TeCMA San Juan.

También incorpora un acceso directo mediante WhatsApp para facilitar las consultas desde dispositivos móviles y computadoras.

Los enlaces externos que se abran en nuevas pestañas deben utilizar las medidas de seguridad correspondientes.

---

## 🖥️ Desarrollo local

Actualmente el sitio puede ejecutarse utilizando cualquier servidor HTTP estático.

Por ejemplo, con Python:

```bash
python3 -m http.server 8000
```

Luego acceder desde el navegador a:

```text
http://localhost:8000
```

También puede utilizarse una extensión o servidor de desarrollo equivalente desde VS Code.

---

## 🌿 Flujo de desarrollo

El proyecto utiliza Git y GitHub para el control de versiones.

Flujo recomendado:

```text
main
  │
  └── rama de trabajo
        │
        ├── implementación
        ├── validación local
        ├── Pull Request
        ├── Deploy Preview
        ├── revisión
        └── merge a main
```

### Reglas generales

* evitar desarrollar directamente sobre `main`;
* crear una rama para cada cambio o conjunto relacionado de cambios;
* mantener cada tarea con un alcance definido;
* utilizar Pull Requests;
* validar los cambios antes del merge;
* utilizar Deploy Preview cuando corresponda;
* evitar despliegues innecesarios en producción;
* publicar en producción únicamente cuando los cambios hayan sido validados;
* mantener `main` estable.

---

## 🔐 Seguridad

El repositorio no debe contener:

* contraseñas;
* tokens;
* claves API;
* credenciales;
* secretos de servicios externos;
* archivos `.env` con datos reales;
* información confidencial;
* datos personales innecesarios;
* credenciales de infraestructura.

Los secretos deben configurarse mediante variables de entorno o mediante los mecanismos seguros proporcionados por la plataforma correspondiente.

---

## ♿ Accesibilidad

El desarrollo procura mantener buenas prácticas básicas de accesibilidad web:

* HTML semántico;
* navegación mediante teclado;
* textos alternativos en imágenes;
* etiquetas accesibles;
* contraste suficiente;
* estructura correcta de encabezados;
* enlaces comprensibles;
* diseño adaptable.

Las futuras modificaciones deben conservar estos criterios.

---

## 📱 Diseño responsive

La interfaz está diseñada para funcionar correctamente en:

* teléfonos móviles;
* tablets;
* notebooks;
* computadoras de escritorio.

Las modificaciones visuales deben verificarse tanto en resoluciones móviles como de escritorio antes de incorporarse a producción.

---

## 🧪 Validaciones

Antes de incorporar cambios a `main` se recomienda ejecutar:

```bash
git diff --check
```

Y verificar, según corresponda:

* estructura HTML;
* navegación;
* enlaces;
* comportamiento responsive;
* visualización móvil;
* visualización de escritorio;
* accesibilidad básica;
* ausencia de regresiones visuales;
* funcionamiento de enlaces externos;
* ausencia de secretos o credenciales;
* estado limpio del repositorio.

---

## 🗂️ Código legado

El contenido ubicado dentro de `legacy/` se conserva exclusivamente como referencia.

No debe trasladarse automáticamente a la nueva web.

Cualquier elemento recuperado del sitio anterior debe:

1. analizarse;
2. comprobar que continúe siendo necesario;
3. adaptarse a la arquitectura actual;
4. incorporarse únicamente cuando aporte valor al proyecto.

---

## 🚀 Evolución

El proyecto está pensado para crecer de manera incremental.

Las nuevas funcionalidades deben incorporarse procurando:

* mantener una arquitectura sencilla;
* evitar duplicación;
* evitar dependencias innecesarias;
* minimizar riesgos de regresión;
* conservar compatibilidad con las funcionalidades existentes;
* separar correctamente funcionalidades públicas y privadas;
* documentar las decisiones relevantes.

---

## 👨‍💻 Desarrollo

Proyecto desarrollado para:

**TeCMA San Juan**

Desarrollo web:

**Nexar Sistemas**

La participación de Nexar Sistemas en el desarrollo no convierte al proyecto en software de distribución libre ni concede permisos de reutilización por terceros.

---

## ⚖️ Licencia y derechos de uso

Este proyecto **no es software open source**.

La disponibilidad pública del repositorio no debe interpretarse como una autorización general para reutilizar, modificar, redistribuir, sublicenciar o comercializar el proyecto.

Las condiciones completas se encuentran en:

[`LICENSE.md`](LICENSE.md)

---

## 📌 Aviso

La información comercial, condiciones de pago, beneficios, datos de contacto y servicios publicados en el sitio pueden modificarse conforme a las políticas vigentes de TeCMA San Juan.

La documentación técnica del repositorio debe actualizarse cuando una modificación afecte decisiones funcionales o arquitectónicas relevantes.
