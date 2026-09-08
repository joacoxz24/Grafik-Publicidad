# Grafik 1.2 — catálogo de pulseras y chapitas

Actualización de código preparada para el próximo hosting. No se ha desplegado en
`odcpublicidad.cl` ni en Sites y no cambia el modo de mantenimiento existente.

## Portada y navegación

Se mantiene el diseño oscuro con acentos fucsia, celeste y violeta. El carrusel
alterna pulseras y chapitas cada seis segundos, con transición suave, flechas,
selección de producto y pausa. Se pausa al pasar el cursor, usar el teclado o
abandonar la pestaña; respeta la preferencia de movimiento reducido.

Ambos productos tienen tarjetas del mismo tamaño. En WordPress, cada familia
abre su página de personalización: `/pulseras/` y `/chapitas/` (usando los enlaces
permanentes reales, también cuando WordPress incluya `index.php`). “Productos”
reúne ambas familias y sigue permitiendo el catálogo estándar de WooCommerce.
Los tres productos internos de chapitas se agrupan en la tarjeta Chapitas;
no aparecen repetidos como tres tarjetas adicionales.

## Precios en pesos chilenos

| Formato 58 mm | Cantidad mínima | Precio normal por unidad | Precio por cantidad |
| --- | ---: | ---: | --- |
| Alfiler | 10 | $500, de 10 a 100 | $400 desde 101 |
| Llavero | 5 | $750, de 5 a 50 | $650 desde 51 |
| Destapador llavero | 5 | $950, de 5 a 50 | $750 desde 51 |

“Sobre 50” y “sobre 100” se interpretaron literalmente como 51 y 101. Los
tramos aplican por diseño y formato; no se suman diseños distintos para el
descuento. Por encima del mínimo las cantidades avanzan de una en una. El límite
operativo es 10.000 unidades por diseño, como en el configurador existente.

Tyvek conserva $10.500 por cada 100 unidades y 20% de descuento desde 1.000
unidades por diseño. Las reglas de chapitas no afectan los paquetes Tyvek.

## Administración y actualización

El plugin conserva su carpeta `grafik-tyvek-configurator` para actualizar el
existente; su nombre visible pasa a **Grafik Configurador de Productos**. No se
instala una segunda copia ni se duplica Flow. Al cargar la actualización con
WooCommerce activo se crean tres productos, una sola vez. Los SKU e IDs
existentes se reutilizan y no se restablecen los precios editados.

En **Productos → editar cada Chapita → Datos del producto → General** se ajustan
Precio normal, Cantidad mínima, Descuento desde (unidades) y Precio por unidad
con descuento. El servidor usa esos valores, recalcula al modificar el carrito y
rechaza cantidades inválidas. El formulario no envía precios como autoridad.
Las fotos del catálogo están incluidas en el tema, con rutas relativas al tema,
y no dependen del dominio ni del hosting anterior.

Cada diseño conserva su tipo, cantidad, detalles y hasta tres archivos. Se
reutiliza el almacenamiento protegido y el acceso administrativo a archivos del
plugin existente. El checkout y la integración de Flow continúan trabajando con
los pedidos de WooCommerce; los estados de producción y correos se conservan.

## Imágenes

`chapitas-catalogo.png` es una composición ilustrativa creada con la herramienta
integrada de imágenes a partir de `01-image.png` y `02-image.png`, aportadas por el
propietario. Se conservaron las chapitas y el texto “TU DISEÑO AQUÍ”, quitando los
textos del flyer, precios y mínimos anteriores. Prompt: “Composite the referenced
black metallic pin badge, silver pin back, and white plastic keychain into a
premium square catalog photo on dark navy, with cyan and pink rim light. Preserve
product designs and TU DISEÑO AQUÍ text; remove surrounding advertising.”

Se revisaron los 13 adjuntos. Las gráficas originales con $350, mínimo 10 en
llaveros, ofertas 2024 o marca ODC no se muestran como ofertas vigentes. No se
recibió una foto específica del destapador llavero: la imagen indica que representa
alfiler y llavero. Cuando exista esa foto podrá agregarse a su variante.

## Comprobaciones y alcance

- Compilación del frontend y prueba del HTML generado.
- Sintaxis PHP y JavaScript.
- Pruebas de lógica del catálogo en `tests/php/catalog.test.php`: mínimos,
  fronteras 50/51 y 100/101, rechazo de compras sin configurar, creación idempotente,
  carrito mixto Tyvek/chapitas, descuentos independientes y metadatos de diseños.
- La prueba PHP usa dobles de WooCommerce; no sustituye una compra completa
  en WordPress. No se probaron cobros, correos ni subidas reales en el hosting.

Antes de abrir el nuevo hosting, verificar con WordPress/WooCommerce reales la
compra de ambos productos, un carrito mixto, archivos, envío, correos y Flow
Sandbox. El frontend React es una referencia visual; solo guarda nombres de
archivos y simula el final del checkout. La compra real corresponde a WordPress.

GitHub guarda el código, no los datos de WooCommerce: la migración necesita base
de datos, uploads y configuración privada de Flow además de los archivos del tema
y del configurador. Los archivos privados de clientes no deben ir al repositorio.
