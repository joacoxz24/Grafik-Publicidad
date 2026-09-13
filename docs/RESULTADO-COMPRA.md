# Resultado de compra — tema 1.3.0

Instalado en grafikpublicidad.cl. Usa la página protegida de pedido recibido de
WooCommerce, sin exponer datos en una página pública independiente.

- Pagado: compra completada, resumen y próximos pasos.
- Pendiente/en espera: no afirma que el pago fue aprobado; permite actualizar.
- Fallido: reintentar el pago del mismo pedido cuando WooCommerce lo permite.
- Cancelado/reembolsado: mensajes específicos, sin confirmación de éxito.
- Sin pedido válido: mensaje genérico sin datos.

Se conservan los hooks de la pasarela y los detalles nativos de WooCommerce,
incluidos productos y datos de entrega. La pasarela debe devolver al cliente a
get_checkout_order_received_url() y actualizar el estado mediante su confirmación
servidor a servidor. No se modifica Flow desde el tema.

Verificación: sintaxis PHP y 36 aserciones con dobles de pedidos, incluidos estados
personalizados, ausencia de pedido y hooks. Falta prueba completa con Flow Sandbox
cuando esté instalada/configurada la pasarela. No se crearon pedidos ni cobros.
