# Grafik Publicidad

Frontend de la tienda de pulseras Tyvek personalizadas de Grafik Publicidad.

## Estado actual

Esta versión contiene:

- Portada “Festival premium”.
- Configurador por tramos de 100 unidades.
- Precio de $10.500 por cada 100 unidades.
- 20% de descuento desde 1.000 unidades por cada diseño.
- Diez colores base opcionales y opción sin color específico.
- Detalles escritos y selección de hasta tres archivos por diseño.
- Carrito con diseños, cantidades, colores y archivos separados.
- Checkout en una página independiente.
- Opción de retiro coordinado o envío por transporte.
- Campos condicionales de RUT, dirección, región y ciudad.
- Resumen de compra, forma de pago Flow y botón de pago.
- Formulario de contacto, Instagram y panel privado de administración.

## Arquitectura prevista para producción

La tienda definitiva se administrará en:

- WordPress.
- WooCommerce.
- Hosting Hosty mediante cPanel.
- Dominio temporal `odcpublicidad.cl`.
- Dominio definitivo `grafikpublicidad.cl`.
- Flow como pasarela de pago instalada sobre WooCommerce.

El checkout de este repositorio representa la experiencia visual y el comportamiento del formulario. El cobro real debe ejecutarse en WordPress mediante el plugin de Flow ya instalado, usando siempre el pedido y el total calculado por WooCommerce.

## Seguridad

Este repositorio no debe contener:

- API Key o Secret Key de Flow.
- Contraseñas de WordPress, cPanel, correo o base de datos.
- Configuraciones privadas.
- Pedidos reales.
- Datos personales de clientes.
- Archivos o diseños cargados por compradores.
- Copias de la base de datos de WooCommerce.

El panel `/admin` de la versión alojada en Sites usa acceso restringido por cuenta y una lista de correos autorizados. En WordPress, el acceso definitivo debe realizarse desde `/wp-admin` con una cuenta propia y un rol de Administrador o Gestor de la tienda. La contraseña se configura en WordPress y nunca se guarda en GitHub.

## Desarrollo local

Requiere Node.js 22.13 o superior.

```bash
npm ci
npm run dev
```

Para construir y validar:

```bash
npm run build
```

## Alcance de este repositorio

No se incluye el plugin personalizado de Flow/WooCommerce porque ya fue instalado por separado. Tampoco se incluye el plugin completo de WooCommerce.

Antes de publicar en Hosty, este frontend debe trasladarse a un tema o plantilla compatible con WordPress/WooCommerce. El código actual está preparado para conservar la referencia completa del diseño y sus interacciones mientras se realiza esa adaptación.
