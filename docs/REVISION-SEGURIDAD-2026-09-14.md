# Revisión de seguridad — 14 de septiembre de 2026

Alcance: lectura del código propio de tema/configurador y solicitudes públicas de bajo volumen a grafikpublicidad.cl. No se realizaron compras, subidas, envíos de correo, pruebas de carga ni cambios al hosting. No equivale a una auditoría forense ni permite certificar ausencia de intrusión.

## Resultados comprobados

- HTTPS responde correctamente y publica HSTS de 63072000 segundos.
- REST de pedidos `/wp-json/wc/v3/orders/29` y su variante `rest_route` devuelven 401 sin autenticación.
- El endpoint de diseños sin sesión redirige al login. El código comprueba permisos administrativos, pertenencia pedido/item y rutas reales confinadas; los archivos activos como SVG no se sirven inline. Descargas con nosniff, sandbox y no-cache.
- Las rutas consultadas de diseños no muestran listados: devuelven 404 o redirigen a portada. Esto no prueba por sí solo el bloqueo de cada archivo individual.
- `/.git/HEAD` devuelve 404; `/wp/.env` y `/wp/wp-content/debug.log` devuelven 406, sin contenido sensible.
- El adaptador Flow verifica estado en servidor con TLS, importe, moneda y pedido; usa bloqueo entre callbacks y `payment_complete()`. Los precios del configurador se calculan en servidor y los ajustes requieren permisos y nonce.
- Las subidas actuales aceptan PNG/JPG/PDF, máximo tres archivos de 10 MB por solicitud, con comprobación de tipo y nombres aleatorios. Los diseños dependen también del bloqueo Apache mediante `.htaccess`.

## Mejora incluida en el tema 1.4.1

El formulario público de cotización tenía nonce y sanitización, pero ningún límite de frecuencia. Un visitante podía repetir envíos y saturar el correo administrativo. Se añade una mitigación básica de un intento por minuto por IP (hash con secreto de WordPress; sin almacenar IP literal), campo trampa y límites de tipo/tamaño. Se corrige la respuesta HTTP de nonce inválido a 403 y se exige POST. No se enviaron correos para probarlo.

El límite mediante transients es una mitigación básica, no un bloqueo atómico ante concurrencia ni una defensa contra bots distribuidos; una IP compartida comparte la espera. Para ataques persistentes se necesita rate limiting del hosting/WAF. Las subidas tampoco tienen cuota global por IP: queda pendiente revisar límites de disco y reglas del hosting.

## Límites y pendientes de servidor

No se inspeccionaron usuarios administradores, logs, base de datos, tareas programadas, permisos reales ni checksums de WordPress/plugins. No se puede afirmar que no exista una vulneración. Revisar esos elementos y respaldos desde hosting si se necesita una comprobación de intrusión.

La portada anuncia WordPress 7.1, WooCommerce 11.1.0 y Smash Balloon 6.13.0; son versiones declaradas por HTML, no un inventario autenticado. Se consultaron documentación y changelog oficiales, sin auditoría completa de dependencias de terceros:

- https://woocommerce.com/document/woocommerce-security-faq/
- https://developer.woocommerce.com/2026/09/03/wc-11-1-release-notes/
- https://wordpress.org/plugins/instagram-feed/

La portada no devuelve `X-Content-Type-Options`; es una mejora de cabeceras pendiente del servidor, no prueba de explotación. Evitar aplicar una CSP global restrictiva sin probar Flow, WooCommerce e Instagram.

## Entrega

Tema 1.4.1 con logo oficial blanco original (PNG intacto, márgenes transparentes ajustados por CSS). El logo personalizado elegido en WordPress conserva prioridad. ZIP para instalación manual; ningún cambio de esta entrega está desplegado en producción. El plugin propio sigue en 1.3.0.
