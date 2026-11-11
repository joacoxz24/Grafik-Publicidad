# Fotos, comprobantes y seguimiento — plugin 1.4.0

## Flujo existente que se amplía

Se revisaron `order_email_classes()`, `send_status_email()` y
`Grafik_Order_Status_Email`. Se mantienen:

| Estado | Correo existente | Ampliación |
|---|---|---|
| `grafik-listo` | `grafik_order_ready` | Archivos seleccionados y mensaje según la entrega del pedido |
| `completed` | `customer_completed_order` nativo | Archivos seleccionados y seguimiento opcional |
| `grafik-enviado` | `grafik_order_shipped` | Archivos seleccionados y seguimiento opcional |

No se registra otro estado ni otro correo. Se conserva el encabezado, pie,
mensaje editable, contenido adicional y resumen existentes. El nuevo bloque usa
`woocommerce_email_order_details`, tanto en HTML como en texto plano. Los diseños
originales del cliente continúan en su flujo independiente `Grafik_Order_Files`.

Referencias de integración: [correo completado de WooCommerce](https://woocommerce.github.io/code-reference/classes/WC-Email-Customer-Completed-Order.html),
[recetario HPOS](https://github.com/woocommerce/woocommerce/blob/trunk/docs/features/orders/high-performance-order-storage/recipe-book.md),
[WC_Email y confirmación del transporte de correo](https://woocommerce.github.io/code-reference/classes/WC-Email.html).

## Instalación y uso

1. Respaldar el plugin actual y sustituirlo por `grafik-configurador-1.4.0.zip`.
   No requiere actualizar el tema ni desactivar/reinstalar WooCommerce.
2. Abrir un pedido existente en **WooCommerce → Pedidos**. El apartado
   **Fotos, comprobantes y seguimiento** aparece en el editor clásico y en HPOS.
3. Subir JPG/JPEG, PNG o PDF. Máximo 20 archivos por pedido y 10 MB por archivo,
   sujeto también al límite PHP del hosting. Se valida el contenido real con
   `fileinfo`, no solo la extensión.
4. Marcar por archivo uno, varios o los tres correos. Los archivos recién
   subidos empiezan sin avisos seleccionados para evitar compartirlos por error.
5. Completar transportista (Starken, Blue Express u Otro), número, enlace y/o
   fecha de despacho cuando corresponda. No hay campos obligatorios de transporte.
6. Pulsar **Guardar fotos y seguimiento** antes de actualizar el estado.
   La subida guarda primero las selecciones previas del apartado. Ninguna de
   estas operaciones cambia el estado ni envía mensajes.
7. Cambiar al estado correspondiente para enviar su aviso. Para enviarlo de
   nuevo, usar **Acciones del pedido → Reenviar: Pedido … (Grafik)** y ejecutar
   esa acción. Se respetan los correos desactivados en Ajustes de WooCommerce.

Las imágenes aparecen dentro del correo con ancho adaptable y un enlace de
respaldo «Ver foto». Algunos lectores requieren habilitar imágenes externas.
Los PDF muestran «Ver comprobante». No se adjuntan archivos al mensaje.

El seguimiento se muestra solo en completado/enviado, solo con datos rellenados
y solo si la entrega es por transporte. El retiro en Concepción/Linares lo
oculta; también se admite el método nativo de retiro local de WooCommerce cuando
el pedido no tiene la elección Grafik. Un bloque sin datos no aparece.

## Almacenamiento y acceso privado

La carpeta se crea **fuera de DOCUMENT_ROOT, ABSPATH y WP_CONTENT_DIR**:
en el directorio padre de la raíz pública del sitio, con nombre
`.grafik-order-media-{identificador-del-sitio}`. No se usa la Biblioteca de
medios ni una URL directa de uploads. Cada pedido tiene un subdirectorio y cada
archivo un identificador aleatorio. Los archivos nuevos usan permisos 0600.
La metadata del pedido conserva identificadores, nombre, MIME, tamaño, secreto
de acceso y selección de correos, nunca una ruta física visible al cliente.

Si el hosting no permite crear esa carpeta, se muestra un error y se rechaza la
subida. El hosting puede crear una carpeta privada escribible por PHP y definir
en `wp-config.php`, antes de cargar WordPress:

```php
// Sustituir por una ruta privada real del hosting, nunca dentro de public_html.
define( 'GRAFIK_ORDER_MEDIA_DIR', '/home/USUARIO/grafik-private-orders' );
```

La carpeta tampoco debe estar publicada mediante alias del servidor. El plugin
rechaza ubicaciones dentro de las raíces públicas conocidas, incluso mediante
enlaces simbólicos. Se inicializa la raíz pública desde un pedido administrativo
para que los correos programados por CLI puedan recuperar los mismos archivos.

Las vistas administrativas requieren permisos de WooCommerce y edición de ese
pedido. Las operaciones AJAX requieren además un nonce para el pedido. Las
descargas de cliente usan una firma HMAC vinculada a pedido, archivo, secreto
y vencimiento; identificadores solos o enlaces alterados no autorizan acceso.
El enlace es una autorización de lectura para quien lo posee: no publicarlo ni
compartirlo con terceros. Caduca a los 90 días; un reenvío genera otro vigente.
Quitar el archivo y guardar elimina el original y revoca sus enlaces; desmarcar
todos los avisos también impide nuevos accesos de cliente. Las copias que el
destinatario ya descargó o que su lector de correo almacenó no pueden revocarse.

Para migrar, respaldar **la base de datos y esta carpeta privada**, además de
los uploads existentes. Conservar los salts de WordPress mantiene las firmas
vigentes; cambiarlos revoca enlaces anteriores. GitHub/ZIP solo contienen código.
Si cambia el dominio, el nombre automático de carpeta cambia: trasladar los
archivos al nuevo nombre o fijar `GRAFIK_ORDER_MEDIA_DIR` a la carpeta migrada.
Desinstalar el plugin conserva los pedidos y archivos, como en versiones previas.

## Notificaciones e idempotencia

Los correos se siguen enviando mediante `WC_Email`, sin `wp_mail()` manual nuevo.
El filtro `woocommerce_email_enabled_{id}` bloquea avisos automáticos ya enviados
al mismo destinatario. Un bloqueo atómico por pedido/correo evita dos trabajadores
simultáneos; tiene recuperación después de cinco minutos si un proceso termina
abruptamente. Solo se marca enviado cuando `woocommerce_email_sent` informa éxito.
Un fallo del transporte permite reintentar; reenviar de forma explícita permite
otro envío y no altera el estado. Igual que WooCommerce, éxito del transporte
no garantiza entrega en bandeja de entrada. No se puede prometer entrega
exactamente una vez si el proceso se interrumpe después de aceptar el correo y
antes de guardar la marca.

Los pedidos anteriores a esta versión no tienen marca histórica: su primer
aviso después de instalarla se enviará normalmente. No se reenvían correos al
instalar, abrir, subir o guardar. Los textos personalizados en Ajustes se conservan.
No se modifican Flow, el checkout, los precios, el stock ni los estados de pago.

## Validación realizada

- 69 comprobaciones de permisos, storage privado, MIME/tamaño, aislamiento entre
  pedidos, firma/vencimiento, seguimiento, retiro, selección por aviso, revocación,
  guardado sin envío/cambio de estado, reintentos e idempotencia.
- 16 comprobaciones ejecutando el registro y la clase de correo originales:
  resumen/encabezado/pie/textos conservados, transición/reenvío, sin duplicar el
  correo nativo de completado ni agregar adjuntos.
- Prueba HTTP local con subidas multipart y descarga real de imagen/PDF:
  nonce/permisos, enlace firmado, cruce de pedidos, archivo falsificado,
  conflictos de edición, guardado persistente y formulario a 390/1000 px.
- Suite PHP existente completa: catálogo, entrega, Flow, archivos originales,
  confirmación de compra, seguridad del contacto, SEO y etiquetas bancarias.

Las pruebas usan dobles locales de WordPress/WooCommerce. No sustituyen una
prueba en el hosting con su versión concreta de WooCommerce y configuración de
correo. Tras instalar, probar primero un pedido propio con un JPG y un PDF,
guardar, revisar las tres selecciones y enviar los avisos a un correo propio.
Esta entrega no envía mensajes reales ni modifica el hosting.
