# Instalación de Grafik Publicidad en WordPress

Este paquete contiene dos instalables:

1. `grafik-publicidad.zip`: tema visual para WordPress y WooCommerce.
2. `grafik-tyvek-configurator.zip`: configurador de pulseras y datos de entrega.

No contiene WooCommerce ni el plugin de Flow. Ambos deben mantenerse instalados
por separado.

## Orden recomendado

1. Crea un respaldo completo desde cPanel.
2. Confirma que WooCommerce esté activo.
3. Instala y activa `grafik-tyvek-configurator.zip`.
4. Instala y activa `grafik-publicidad.zip`.
5. Abre **Grafik → Configurador Tyvek** y confirma:
   - $10.500 por cada 100 unidades.
   - 20% de descuento desde 1.000 unidades.
   - máximo 10.000 unidades por diseño.
6. Abre **WooCommerce → Ajustes → Pagos** y confirma que solo exista una
   integración de Flow activa.
7. Revisa **Ajustes → Generales** y **Ajustes → Enlaces permanentes**.
8. Haz una compra de prueba completa en el ambiente Sandbox de Flow.

## Qué crea el configurador

- Producto interno “Pulseras Tyvek personalizadas”.
- Cantidades de 100 en 100.
- Cada combinación de cantidad, color, detalles y archivos queda separada en
  el carrito y en el pedido.
- Hasta tres archivos por diseño.
- Archivos protegidos de acceso público.
- Retiro coordinado o envío por transporte con RUT, dirección, región y ciudad.
- Consentimiento para promociones guardado en el pedido.
- Página Productos conectada al catálogo de WooCommerce.
- Estados y correos: Pedido recibido, confirmado, listo y enviado.

## Actualizaciones habituales

- Productos: **WordPress → Productos**.
- Logo: **Apariencia → Personalizar → Identidad del sitio**.
- Correos: **WooCommerce → Ajustes → Correos electrónicos**.
- Estado de un pedido: **WooCommerce → Pedidos**, abre el pedido y cambia su estado.
- Instagram automático: instala y conecta `Smash Balloon Social Photo Feed`; la
  conexión con Instagram debe autorizarla el propietario de la cuenta.

## Administración

El acceso se realiza con usuarios reales de WordPress. Para el trabajo diario
se recomienda el rol **Gestor de la tienda**; reserva **Administrador** para
instalar plugins, temas y cambiar configuraciones críticas.

Nunca guardes en GitHub:

- Contraseñas.
- Claves de aplicación de WordPress.
- Credenciales de Flow.
- Copias de la base de datos.
- Pedidos, datos de clientes o diseños subidos.
