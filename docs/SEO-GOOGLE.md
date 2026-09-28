# SEO de Grafik Publicidad — septiembre de 2026

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
