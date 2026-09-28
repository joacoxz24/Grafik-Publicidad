<?php defined( 'ABSPATH' ) || exit; $chapitas = is_page( 'chapitas' ); ?>
<section class="grafik-product-guide" aria-labelledby="grafik-guide-title">
	<h2 id="grafik-guide-title"><?php echo $chapitas ? 'Chapitas con tu logo para marcas y eventos' : 'Pulseras personalizadas para identificar a tus invitados'; ?></h2>
	<?php if ( $chapitas ) : ?>
		<p>Las chapitas publicitarias de 58 mm permiten llevar tu logo, ilustración o mensaje en un accesorio personalizado. Elige chapitas con alfiler para prendas y mochilas, llaveros para acompañar las llaves o destapadores llavero para regalos promocionales.</p>
		<h3>¿Qué formato y cantidad elegir?</h3>
		<p>Selecciona el formato en el configurador para consultar su cantidad mínima y precio por unidad. El total se actualiza según las unidades que necesites y aplica el precio por mayor cuando corresponde.</p>
	<?php else : ?>
		<p>Las pulseras Tyvek personalizadas ayudan a distinguir grupos e identificar asistentes en fiestas, actividades de empresas y otros eventos. Puedes elegir un color base y enviar tu logo o diseño de referencia para personalizar el pedido.</p>
		<h3>¿Cómo cotizar las pulseras?</h3>
		<p>Selecciona la cantidad en el configurador, que trabaja en incrementos de 100 unidades. Verás el precio y el descuento por volumen que corresponda antes de agregar las pulseras al carrito.</p>
	<?php endif; ?>
	<div class="grafik-guide-questions">
		<details><summary>¿Cómo envío mi logo o diseño?</summary><p>Adjúntalo en el configurador en PNG, JPG o PDF. Puedes enviar hasta tres archivos de 10 MB cada uno y explicar tus colores, textos y otros detalles. Revisaremos el diseño contigo antes de producir.</p></details>
		<details><summary>¿Puedo retirar en Concepción o pedir despacho?</summary><p>Sí. Elige retiro coordinado CONCEPCIÓN o envío por transporte al finalizar la compra. Los despachos son por pagar: el costo del transporte se paga por separado del pedido.</p></details>
		<details><summary>¿Cuánto demora un pedido personalizado?</summary><p>Consulta antes de comprar si tienes una fecha de evento. El plazo depende de la cantidad, la revisión del diseño y la coordinación de producción y entrega. <a href="<?php echo esc_url( home_url( '/#contacto' ) ); ?>">Cuéntanos qué necesitas y para cuándo</a>.</p></details>
	</div>
	<p><?php if ( $chapitas ) : ?>Completa tu evento con <a href="<?php echo esc_url( grafik_configurator_url( 'pulseras' ) ); ?>">pulseras Tyvek personalizadas</a>.<?php else : ?>También puedes agregar <a href="<?php echo esc_url( grafik_configurator_url( 'chapitas' ) ); ?>">chapitas y llaveros personalizados</a> para tu marca.<?php endif; ?></p>
</section>
