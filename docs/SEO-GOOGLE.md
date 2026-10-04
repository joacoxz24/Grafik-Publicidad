# SEO de Grafik Publicidad — septiembre de 2026

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
