## Entrega de opciones de transporte — 14 de septiembre de 2026

Plugin Grafik Configurador 1.3.1: retiro coordinado CONCEPCIÓN y selector obligatorio de Bluexpress, Chilexpress, Varmontt o Starken por pagar al elegir transporte. Guarda _grafik_shipping_carrier mediante WC CRUD y lo muestra en administración y correos. Pedidos antiguos sin selección muestran No especificado. No agrega tarifas ni conexiones con transportistas. 31 pruebas específicas de entrega y 58 de catálogo aprobadas. ZIP manual outputs/grafik-configurador-1.3.1.zip. Tema permanece 1.4.1.

## Entrega del 14 de septiembre de 2026

Tema 1.4.1: logo oficial blanco en encabezado y checkout; validación y mitigación básica contra repetición de cotizaciones. Revisión pública y de código documentada en docs/REVISION-SEGURIDAD-2026-09-14.md. Pruebas: 180 comprobaciones PHP e integración del tema. Entrega por GitHub y ZIP; el usuario instala manualmente. No desplegar diseño al hosting salvo petición expresa. Plugin sigue en 1.3.0.

# Continuidad de Grafik Publicidad

## Correcciones publicadas el 13 de septiembre de 2026

- Hosting definitivo activo en `https://grafikpublicidad.cl`; WordPress permanece
  físicamente en `/wp`, pero la tienda se sirve desde la raíz del dominio.
- Configurador 1.3.0: archivos privados por artículo con metadata estructurada,
  recuperación de pedidos anteriores, miniaturas y descargas autenticadas.
- El pedido 29 se recuperó después de comprobar que WooCommerce recreó el artículo
  del pedido con otro identificador durante la reanudación del checkout.
- Flow Payment 3.0.8 conserva su configuración y checkout. El configurador adapta
  sus callbacks para validar con Flow, serializar confirmaciones, usar
  `payment_complete()` con la transacción y evitar duplicados o regresiones.
- El fallo de correo era de entrega: WooCommerce sí disparó sus correos, pero Gmail
  rechazó el remitente técnico del hosting por SPF/DKIM. Los nuevos envíos usan
  `ventas@grafikpublicidad.cl`; una prueba posterior fue aceptada por cPanel y
  recibida correctamente.
- Tema 1.4.0: cotizaciones dirigidas a `ventas@grafikpublicidad.cl`, feed 1 de
  Smash Balloon en portada y carrusel infinito cada 4 segundos sin botón Pausar.
- Validación local: 58 pruebas de catálogo, 55 de archivos/Flow, 21 del límite del
  callback de Flow, 36 de páginas de resultado y prueba de integración del tema.
- GitHub respalda únicamente código. Base de datos, pedidos y `uploads` requieren
  copias de seguridad separadas y no deben publicarse en el repositorio.

## Actualización del 13 de septiembre de 2026

- Destapador llavero: precio mayorista actualizado a $860 desde 51 unidades;
  precio normal $950 y mínimo 5 sin cambios.
- Plugin 1.2.1: migración única del precio anterior de $750 en WooCommerce.
- Verificadas 58 aserciones PHP y precios frontend en el límite 50/51.
- Vista previa local reiniciada en http://localhost:5173/ (respuesta HTTP 200).
- Este punto era histórico; el despliegue posterior está descrito arriba.
- La comprobación TypeScript global encuentra tipos de Cloudflare ausentes
  en db/index.ts y worker/index.ts; no afecta a las pruebas de precios.

## Actualización del 8 de septiembre de 2026

Se recuperaron los últimos mensajes de la misma conversación y se sincronizó
main mediante fast-forward con origin/main, commit
b6a4ac328c74a4aef7c0bf40555985baafceaa30 (8 de septiembre, 11:55, Chile).
Esta es la base vigente; los apartados del 5 de septiembre debajo son históricos.

- Versión 1.2: portada multiproducto, carrusel automático y tarjetas con igual
  visibilidad para pulseras Tyvek y chapitas de 58 mm.
- Chapitas alfiler: mínimo 10, $500/unidad; $400 desde 101.
- Chapitas llavero: mínimo 5, $750/unidad; $650 desde 51.
- Chapitas destapador llavero: mínimo 5, $950/unidad; $750 desde 51.
- Detalles y alcance: docs/CATALOGO-1.2.md.
- Preferencia vigente: trabajar en código y GitHub antes de migrar; no desplegar
  cambios al hosting actual.
- La conversación informó que el sitio actual quedó en mantenimiento.
- Último pendiente solicitado: mostrar la vista previa del diseño actualizado.
- No se ha verificado en esta sesión el servidor WordPress.

Contexto recuperado el 5 de septiembre de 2026 desde la conversación
«Desarrollo página ecommerce» (6a620d66-ce40-83e9-9152-b1e47dafd5e8).

## Base de trabajo comprobada

- Repositorio: https://github.com/joacoxz24/Grafik-Publicidad
- Rama local: main, vinculada a origin/main.
- Commit recuperado: 2327681c5e225496eb465be0c3d8fbf1274c9a29.
- Tema de producción: wordpress/grafik-publicidad.
- Configurador WooCommerce: wordpress/grafik-tyvek-configurator.
- La aplicación de la raíz es el frontend previo; distinguirla de la versión
  WordPress al realizar cambios para la tienda instalada.
- Instalación: wordpress/README-INSTALACION.md.

## Estado informado en la conversación anterior

El usuario informó que la tienda y el pago funcionaban correctamente.
La respuesta anterior informó la instalación de la versión 1.1.0, el catálogo
Productos, la corrección del cupón en escritorio, la preferencia de promociones
y los estados/correos de recibido, confirmado, listo y enviado.

Dominio temporal informado: https://odcpublicidad.cl.
Dominio definitivo previsto: https://grafikpublicidad.cl.
Plataforma: WordPress y WooCommerce, con hosting Hosty/cPanel y Flow separado.

Estos antecedentes no equivalen a una comprobación actual del servidor.
En esta sesión se recuperó el repositorio; todavía no se comprobó la instalación
en producción ni se ejecutaron pruebas de compra.

## Pendientes conocidos

- Confirmar si el propietario conectó Instagram en Smash Balloon Social Photo
  Feed. La conversación anterior dejó pendiente insertar y verificar el feed
  real en la portada después de esa conexión.
- Continuar con los próximos cambios de productos, diseño y funcionalidades
  que indique el usuario.
- Preparar la futura migración cuando se definan hosting y dominio de destino.

## Alcance del respaldo

GitHub contiene código, pero no respalda la base de datos, pedidos, productos
guardados en WordPress, uploads ni configuraciones privadas del servidor.
La migración necesita respaldar esos elementos por separado.
No guardar credenciales ni datos de clientes en este repositorio.
El plugin de Flow se mantiene por separado y no está incluido aquí.
