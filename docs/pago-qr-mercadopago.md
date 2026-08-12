# Pago con QR de Mercado Pago

## Enfoque del MVP

El MVP mostrará el QR de Mercado Pago vigente como recurso estático.
No se implementará ninguna integración con la API de Mercado Pago.
No se generará ni reemplazará el QR en esta etapa.

## Funcionamiento manual

- El QR se presenta como referencia de pago estática.
- El pago se realiza de forma manual por el cliente usando el QR mostrado.
- La identificación del pago se hará con mecanismos administrativos pendientes de definición.

## Regla del descuento del 5 %

- El descuento del 5 % aplica únicamente a los pagos realizados entre el día 1 y el día 15 inclusive de cada mes.
- El descuento del 5 % se aplica sobre la próxima factura, sujeto a validación administrativa.
- Los pagos realizados después del día 15 no acceden al descuento.
- La aplicación del descuento requiere validación administrativa.

## Automatizaciones fuera del MVP

No se implementarán en esta fase:

- API de Mercado Pago.
- Webhooks.
- Confirmación automática.
- Notificaciones.
- Conciliación.
- Identificación automática del pagador.
- Aplicación automática del descuento.
- Integración con facturación.

## Mejoras futuras

- Validar el QR vigente actual antes de publicarlo.
- Definir el mecanismo administrativo para confirmar pagos y aplicar descuentos.
- Evaluar la incorporación de un checkout integrado solo cuando el MVP esté operativo.
