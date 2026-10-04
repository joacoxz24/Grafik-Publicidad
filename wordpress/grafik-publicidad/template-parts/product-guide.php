<?php defined( 'ABSPATH' ) || exit; $chapitas = is_page( 'chapitas' ); ?>
<section class="grafik-product-guide" aria-labelledby="grafik-guide-title">
	<h2 id="grafik-guide-title"><?php echo $chapitas ? 'Chapitas con tu logo para marcas y eventos' : 'Pulseras personalizadas para identificar a tus invitados'; ?></h2>
	<?php if ( $chapitas ) : ?>
		<p>Las chapitas publicitarias de 58 mm permiten llevar tu logo, ilustración o mensaje en un accesorio personalizado. Elige chapitas con alfiler para prendas y mochilas, llaveros para acompañar las llaves o destapadores llavero para regalos promocionales.</p>
		<h3>¿Qué formato y cantidad elegir?</h3>
		<p>Selecciona el formato en el configurador para consultar su cantidad mínima y precio por unidad. El total se actualiza según las unidades que necesites y aplica el precio por mayor cuando corresponde.</p>
		<h3>Chapitas alfiler personalizadas</h3>
		<p>Una opción para identificar equipos, acompañar campañas o sumar un detalle a una prenda, bolso o mochila. Personalízalas con el logo de tu empresa, un mensaje o una ilustración.</p>
		<h3>Chapitas llavero personalizadas</h3>
		<p>Un accesorio para acompañar las llaves con tu diseño de 58 mm. Puedes pedirlo para regalos de empresa, recuerdos de eventos o para ampliar el catálogo de tu tienda.</p>
		<h3>Chapitas llavero destapador</h3>
		<p>Combinan una cara personalizada con la función de destapador y llavero. Un formato para regalos promocionales y pedidos de empresas que buscan un accesorio útil con su marca.</p>
		<h3>Chapitas por mayor para empresas y revendedores</h3>
		<p>Vendemos por mayor y al detalle, respetando el mínimo de cada formato. El configurador muestra las cantidades y precios activos; el precio por volumen se calcula por diseño y formato. Para un pedido con varios diseños, una fecha específica o una cotización con factura, <a href="<?php echo esc_url( home_url( '/#contacto' ) ); ?>">cuéntanos tu proyecto</a>.</p>
	<?php else : ?>
		<p>Las pulseras Tyvek personalizadas ayudan a distinguir grupos e identificar asistentes en fiestas, actividades de empresas y otros eventos. Puedes elegir un color base y enviar tu logo o diseño de referencia para personalizar el pedido.</p>
		<h3>¿Cómo cotizar las pulseras?</h3>
		<p>Selecciona la cantidad en el configurador, que trabaja en incrementos de 100 unidades. Verás el precio y el descuento por volumen que corresponda antes de agregar las pulseras al carrito.</p>
	<?php endif; ?>
	<div class="grafik-guide-questions">
		<?php if ( $chapitas ) : ?>
		<details><summary>¿El diseño de las chapitas tiene costo?</summary><p>Hacemos el diseño gratis para tu pedido. Envíanos tu logo, textos, colores o una referencia y revisaremos contigo cómo quedará antes de producir.</p></details>
		<details><summary>¿Venden por mayor y al detalle?</summary><p>Sí. Cada formato tiene su cantidad mínima. Al seleccionar el tipo y la cantidad verás el precio por unidad y el total; el precio por mayor se aplica al alcanzar el tramo indicado para ese diseño.</p></details>
		<details><summary>¿Emiten boleta o factura?</summary><p>Sí. Si necesitas factura, indícalo al cotizar para coordinar los datos de facturación antes de cerrar tu pedido.</p></details>
		<?php endif; ?>
		<details><summary>¿Cómo envío mi logo o diseño?</summary><p>Adjúntalo en el configurador en PNG, JPG o PDF. Puedes enviar hasta tres archivos de 10 MB cada uno y explicar tus colores, textos y otros detalles. Revisaremos el diseño contigo antes de producir.</p></details>
		<details><summary>¿Puedo retirar en Concepción o Linares, o pedir despacho?</summary><p>Sí. Al finalizar la compra puedes elegir retiro presencial coordinado en Concepción o Linares, o envío por transporte. Los despachos son por pagar: el costo del transporte se paga por separado del pedido.</p></details>
		<details><summary>¿Cuánto demora un pedido personalizado?</summary><p>Despachamos la mayoría de los pedidos entre 24 y 72 horas hábiles. Sin embargo, el plazo depende de la cantidad total del pedido. Este plazo corresponde al despacho; el tiempo de traslado depende del transporte. Si tienes una fecha de evento, <a href="<?php echo esc_url( home_url( '/#contacto' ) ); ?>">consúltanos antes de comprar</a>.</p></details>
		<details><summary>Formas de pago</summary><p>Puedes pagar mediante transferencia bancaria y con tarjetas de débito o crédito a través de la pasarela de pago Flow.</p></details>
	</div>
	<p><?php if ( $chapitas ) : ?>Completa tu evento con <a href="<?php echo esc_url( grafik_configurator_url( 'pulseras' ) ); ?>">pulseras Tyvek personalizadas</a>.<?php else : ?>También puedes agregar <a href="<?php echo esc_url( grafik_configurator_url( 'chapitas' ) ); ?>">chapitas y llaveros personalizados</a> para tu marca.<?php endif; ?></p>
</section>
