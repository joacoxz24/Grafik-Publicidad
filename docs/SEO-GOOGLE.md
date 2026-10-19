# SEO de Grafik Publicidad — septiembre de 2026

## Actualización 19/10/2026 — tema 1.8.0 y configurador 1.3.5

Se recuperó la versión 1.7.4 del commit `495cb9db42961cde2c5513af244e2a5edae65daa`, que estaba en una propuesta separada de main. La página pública de Chapitas confirmó el tema 1.7.4 y sus metadatos comerciales. Esta entrega conserva ese contenido, las preguntas frecuentes, las galerías, el diseño gratis y las vistas previas al compartir enlaces.

Cambios preparados para carga manual:

- Productos añade una guía para elegir entre pulseras y los tres formatos de chapitas, personalizar, pagar y coordinar entrega. Usa datos ya confirmados por el propietario.
- Las tarjetas del catálogo usan H2 después de su H1; la portada conserva H3 después de su encabezado de sección. Se mantienen las tarjetas y el catálogo de futuros productos antes de la guía.
- Los títulos de Pulseras y Chapitas son más concisos. La descripción de Pulseras resume producto y entrega. Las etiquetas sociales reutilizan estos datos. Google puede adaptar los títulos y fragmentos; 60/160 caracteres son referencias editoriales del auditor, no límites obligatorios de Google ni factores suficientes para posicionar.
- Las URL de los productos internos redirigen con 301 hacia su configurador en visitas GET/HEAD. Las solicitudes POST conservan el comportamiento temporal anterior. Los productos ajenos al configurador quedan fuera de esa regla.

### Evidencia de OpenSEO y límites

La auditoría de HTML del 19 de octubre recorrió 17 URL con un máximo de 50, sin Lighthouse ni renderizado JavaScript: 44 observaciones, 24 advertencias y 20 informativas. Detectó páginas de ejemplo, archivos sin contenido útil, poco contenido y salto de encabezados en Productos, y metadatos largos en páginas de producto. Carrito, cuenta y recuperación de contraseña ya emitían noindex; no deben indexarse para resolver avisos del auditor. El tiempo medio observado fue 1642 ms y es una medición de ese rastreo, no una prueba de causa del servidor ni de rendimiento móvil. No se midieron posiciones, demanda, visitas orgánicas ni conversiones, y Search Console no está conectado a OpenSEO.

### Instalación y comprobación

1. Guarda un respaldo del tema, del plugin y de la base de datos. En Apariencia → Temas → Añadir nuevo → Subir tema, carga `grafik-tema-1.8.0.zip` y reemplaza el tema instalado.
2. En Plugins → Añadir nuevo → Subir plugin, carga `grafik-configurador-1.3.5.zip` y reemplaza Grafik Configurador de Productos. Este ZIP no corresponde al plugin externo de Flow.
3. Limpia la caché existente. Comprueba Productos, Chapitas, Pulseras y el carrito. La vista previa de Google puede tardar en reflejar cambios.
4. Comprueba que las cuatro URL `/producto/` del configurador respondan con 301 hacia `/chapitas/` o `/pulseras/`, y que el destino responda 200. No cambies las redirecciones de checkout, cuenta ni de la pasarela.

### Limpieza pendiente en la base de datos de WordPress

Esta entrega de código no borra contenido del sitio.

1. En Páginas → Todas las páginas, abre “Sample Page” y confirma que sea la página de ejemplo. Si no tiene contenido propio necesario, muévela a la papelera (recuperable).
2. En Entradas → Todas las entradas, revisa “Hello world!” y mueve a la papelera la entrada de ejemplo si no se usa.
3. Revisa `/category/uncategorized/`, `/categoria-producto/sin-categorizar/` y los archivos de autor. No borres productos ni categorías con productos asignados. Organiza categorías reales según el catálogo y decide cuáles deben aparecer en Google; los archivos sin uso pueden excluirse del índice y del sitemap con la herramienta SEO elegida. No bloquearlos en robots.txt como sustituto de noindex.
4. Abre el sitemap y repite el rastreo tras la limpieza para confirmar qué URL siguen publicadas. No redirigir páginas de ejemplo a la portada solo para ocultar avisos.

### Medición siguiente

Conectar la propiedad de `grafikpublicidad.cl` de Google Search Console desde las integraciones de OpenSEO requiere autorización del propietario de Google. Después revisar indexación/canonical de Chapitas, Pulseras y Productos y registrar clics e impresiones como referencia. Las mejoras del hosting y caché se investigan aparte; nunca cachear carrito, checkout, cuenta ni confirmaciones de pago.

Fuentes: [redirecciones permanentes](https://developers.google.com/search/docs/crawling-indexing/301-redirects), [títulos](https://developers.google.com/search/docs/appearance/title-link), [fragmentos y descripciones](https://developers.google.com/search/docs/appearance/snippet).

## Actualización 04/10/2026 — tema 1.7.4

Entrega de código, sin instalación en el hosting. La página Chapitas destaca venta por mayor y al detalle, diseño gratis, boleta o factura (datos proporcionados por el propietario), cotización por Instagram, correo y formulario. Se agregan descripciones diferenciadas de alfiler, llavero y llavero destapador, contenido para empresas/revendedores y respuestas sobre diseño, compra por volumen y facturación. No cambia mínimos, precios, archivos ni pagos. No promete una fecha universal de entrega.

El título y la metadescripción de Chapitas incorporan intención de compra por mayor. Se añaden Open Graph y Twitter Card a las cuatro páginas gestionadas por el tema, con URLs limpias e imágenes existentes. La capa sigue cediendo el control a plugins SEO conocidos. Los metadatos sociales ayudan a presentar enlaces compartidos; no son una garantía de posicionamiento. No se generan ofertas Product para precios por cantidad ni reseñas ficticias.

### Evidencia y límites de la revisión actual

- Repositorio revisado sobre commit 550931a54fbcff2887bbfda361d3cc0dda3be315.
- La búsqueda pública no permitió confirmar presencia del dominio. Una búsqueda sin resultados no prueba que Google no lo tenga indexado.
- El acceso HTTP de esta conexión a /chapitas/ devolvió 502; el navegador compartido devolvió «502 Bad Gateway / [Errno 111] Connection refused». No permite concluir que los visitantes reales estén recibiendo el mismo error ni atribuirlo al hosting.
- No se verificaron la versión instalada, robots.txt, sitemap, noindex, canonical en producción, rendimiento móvil ni el checkout. No hay acceso verificado a Search Console, GA4 o datos de visitas/pedidos.
- La revisión de Google Ads inmediatamente anterior mostró campaña habilitada y apta, con 0 impresiones, 0 clics y CLP0 en el período 1–4 de octubre. No usar esto para inferir que toda la tienda carece de tráfico: son métricas de una campaña.

### Orden de trabajo para conseguir pedidos

1. **Acceso y compra:** comprobar la tienda desde datos móviles y otra conexión. Si el 502 se reproduce, entregar al hosting la URL y la hora para revisar origen, PHP y servidor web. No dirigir más tráfico a una tienda que falle.
2. **Indexación:** abrir Search Console con el propietario, inspeccionar /chapitas/ con prueba en vivo, revisar si es indexable y cuál es la canonical elegida; enviar el sitemap real y solicitar indexación después de instalar cambios. No crear robots.txt ni cambiar noindex sin leer el estado actual.
3. **Instalación:** integrar el código 1.7.4 al tema completo, respaldar tema anterior y probar en staging. GitHub no despliega la tienda automáticamente. Confirmar plugins SEO activos para evitar duplicados.
4. **Medición:** verificar GA4 y conversiones de compra de Google Ads. Una compra debe registrarse una sola vez con ID de transacción y valor real, tras confirmación de pago. No marcar el clic de pago como compra ni instalar una segunda medición sin inventario previo.
5. **Tráfico inmediato:** diagnosticar la campaña apta sin impresiones, sin subir presupuesto automáticamente. Publicar los nuevos creativos orientados a mayoristas y enlazar /chapitas/ con etiquetado UTM. Cotizaciones recibidas por Instagram/correo también cuentan como oportunidades, aunque no sean ventas del checkout.
6. **Conversión:** mostrar fotos reales y ejemplos de trabajos, mínimo y precio por cantidad, diseño gratis, medios de pago y costo/plazo de despacho. Prueba completa de carrito y checkout sin generar cobros. Las reseñas deben corresponder a compradores reales.
7. **Seguimiento:** medir sesiones, clics en contacto, add_to_cart, begin_checkout y purchase. Comparar fuentes y dispositivos para detectar el punto de abandono. No cambiar campañas por resultados de cero visitas.

El SEO es una inversión progresiva, no una promesa de ventas inmediatas. Google recomienda valorar efectos durante semanas; la indexación y rastreo pueden tardar días o semanas y no están garantizados.


## Entrega del tema 1.6.0

Títulos y metadescripciones específicos para portada, Pulseras, Chapitas y catálogo. Textos visibles de productos, preguntas frecuentes y enlaces entre las familias. Información veraz sobre retiro coordinado en Concepción y envíos por pagar. Datos estructurados Organization en portada, sin inventar dirección, reseñas, horarios o precios. WordPress y WooCommerce conservan canonical y datos Product.

Se desactiva esta capa de metadatos si se detecta Yoast, Rank Math, AIOSEO, SEOPress o The SEO Framework. En ese caso configurar títulos, descripciones y organización en el plugin SEO. Para otro plugin se puede usar el filtro `grafik_external_seo`. No añadir varios plugins SEO a la vez.

Comprobación pública del 28/09/2026: portada, /pulseras/ y /chapitas/ responden 200 y no incluyen metadescripción antes de esta entrega. /wp-sitemap.xml responde 200 XML. No se accedió a Search Console ni se midieron volúmenes, posiciones o Core Web Vitals. Los ZIP son para instalación manual; GitHub no publica automáticamente al hosting.

## Palabras clave iniciales

| Página | Tema principal | Consultas relacionadas |
| --- | --- | --- |
| Portada | Productos personalizados | Pulseras y chapitas, productos personalizados Concepción |
| Pulseras | Pulseras Tyvek personalizadas | Pulseras para eventos, pulseras con logo, pulseras control de acceso |
| Chapitas | Chapitas personalizadas de 58 mm | Chapitas con alfiler, llaveros personalizados, destapadores llavero |

Son hipótesis basadas en el catálogo, no palabras con demanda comprobada. No crear páginas repetidas por ciudad ni repetir términos artificialmente. No usar meta keywords: Google no las utiliza para posicionar.

## Acciones en Google después de instalar

1. Abrir https://search.google.com/search-console/ con la cuenta propietaria. Añadir la propiedad de dominio `grafikpublicidad.cl`. Google entrega un registro TXT para verificar por DNS: se agrega donde estén administrados los DNS, conservando los registros existentes. No requiere entregar contraseñas al desarrollador.
2. En Sitemaps enviar `https://grafikpublicidad.cl/wp-sitemap.xml`. Si un plugin SEO reemplaza el sitemap, usar la URL publicada en robots.txt.
3. Inspeccionar portada, /pulseras/, /chapitas/ y /productos/. Comprobar URL canónica, acceso e indexación y solicitar indexación tras instalar. Revisar también Ajustes > Lectura: no debe estar activada la opción de disuadir a motores de búsqueda en la tienda pública.
4. Medir en Rendimiento las consultas, clics, impresiones y páginas, con país Chile. Guardar una línea base y comparar períodos de 28 días. No prometer posiciones ni resultados inmediatos.
5. Probar URLs en PageSpeed Insights, especialmente móvil. Priorizar problemas medidos de imágenes, carrusel y feed de Instagram. Todavía no se ha realizado esta medición.
6. Completar el Perfil de Empresa en Google solo si el negocio cumple sus requisitos (contacto presencial con clientes). Confirmar elegibilidad del retiro coordinado antes de publicar una dirección; no inventar un local abierto al público. Mantener datos reales y solicitar reseñas auténticas, sin incentivos.
7. Agregar fotografías reales con texto alternativo descriptivo. Publicar guías útiles (elegir formato de chapita, preparar archivo, cantidades y plazos confirmados), enlazadas a los productos.

## Próxima fase de ecommerce

Los configuradores son páginas personalizadas: no asumir que contienen marcado Product completo solo porque WooCommerce está activo. Si se quieren resultados enriquecidos o Merchant Center, revisar páginas canónicas de producto, mínimos de compra y precios por cantidad. Probar con Rich Results Test y cotejar los precios con el checkout antes de publicar ofertas estructuradas. No anunciar envío gratuito: el transporte es por pagar.

## Fuentes oficiales

- https://developers.google.com/search/docs/fundamentals/seo-starter-guide
- https://developers.google.com/search/docs/appearance/snippet
- https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap
- https://developers.google.com/search/docs/appearance/structured-data/product-snippet
- https://support.google.com/business/answer/7091

Estas mejoras facilitan comprender y encontrar el catálogo, pero no garantizan una posición en Google.
