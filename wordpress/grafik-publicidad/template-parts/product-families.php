<?php defined( 'ABSPATH' ) || exit; ?>
<div class="family-grid">
	<a class="family-card" href="<?php echo esc_url( grafik_configurator_url( 'pulseras' ) ); ?>">
		<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/pulseras-tyvek-reales.png' ); ?>" width="1024" height="1024" loading="lazy" alt="Pulseras Tyvek personalizadas de colores">
		<div><span class="kicker">Eventos y accesos</span><h3>Pulseras Tyvek</h3><p>Desde 100 unidades. Elige tu color y adjunta tu diseño.</p><strong><?php echo esc_html( grafik_tyvek_price_label() ); ?></strong><span class="family-link">Personalizar pulseras →</span></div>
	</a>
	<a class="family-card" href="<?php echo esc_url( grafik_configurator_url( 'chapitas' ) ); ?>">
		<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/chapitas-catalogo.png' ); ?>" width="1024" height="1024" loading="lazy" alt="Composición referencial de chapitas con alfiler y llavero">
		<div><span class="kicker">Marcas y promociones</span><h3>Chapitas publicitarias</h3><p>58 mm. Alfiler, llavero y destapador llavero.</p><strong><?php echo esc_html( grafik_chapita_price_label() ); ?></strong><span class="family-link">Personalizar chapitas →</span></div>
	</a>
</div>
