<?php
/**
 * Correo de cliente para estados personalizados de pedidos Grafik.
 *
 * @package Grafik_Tyvek
 */

defined( 'ABSPATH' ) || exit;

final class Grafik_Order_Status_Email extends WC_Email {
	private string $default_message;

	public function __construct(
		string $id,
		string $title,
		string $description,
		string $heading,
		string $message
	) {
		$this->id               = $id;
		$this->title            = $title;
		$this->description      = $description;
		$this->customer_email   = true;
		$this->heading          = $heading;
		$this->subject          = '[{site_title}] {email_heading} — pedido #{order_number}';
		$this->default_message  = $message;
		$this->template_base    = GRAFIK_TYVEK_DIR;
		$this->placeholders     = array(
			'{site_title}'   => $this->get_blogname(),
			'{order_date}'   => '',
			'{order_number}' => '',
			'{email_heading}'=> $heading,
		);

		parent::__construct();
	}

	public function init_form_fields(): void {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => 'Activar/Desactivar',
				'type'    => 'checkbox',
				'label'   => 'Activar esta notificación por correo electrónico',
				'default' => 'yes',
			),
			'subject' => array(
				'title'       => 'Asunto',
				'type'        => 'text',
				'description' => 'Variables disponibles: {site_title}, {order_number}, {order_date} y {email_heading}.',
				'placeholder' => $this->subject,
				'default'     => '',
				'desc_tip'    => true,
			),
			'heading' => array(
				'title'       => 'Encabezado',
				'type'        => 'text',
				'description' => 'Título principal que verá el cliente dentro del correo.',
				'placeholder' => $this->heading,
				'default'     => '',
				'desc_tip'    => true,
			),
			'message' => array(
				'title'       => 'Mensaje',
				'type'        => 'textarea',
				'description' => 'Texto que aparecerá antes del detalle del pedido.',
				'placeholder' => $this->default_message,
				'default'     => $this->default_message,
				'css'         => 'width:400px; height:100px;',
			),
			'additional_content' => array(
				'title'       => 'Contenido adicional',
				'type'        => 'textarea',
				'description' => 'Contenido opcional que aparecerá al final del correo.',
				'placeholder' => '',
				'default'     => '',
				'css'         => 'width:400px; height:75px;',
			),
			'email_type' => array(
				'title'       => 'Tipo de correo',
				'type'        => 'select',
				'description' => 'Formato del correo electrónico.',
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}

	public function get_default_subject(): string {
		return '[{site_title}] {email_heading} — pedido #{order_number}';
	}

	public function get_default_heading(): string {
		return $this->heading;
	}

	public function get_default_additional_content(): string {
		return '';
	}

	public function trigger( int $order_id, $order = false ): void {
		$this->setup_locale();

		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( is_a( $order, 'WC_Order' ) ) {
			$this->object                         = $order;
			$this->recipient                      = $order->get_billing_email();
			$this->placeholders['{order_date}']   = wc_format_datetime( $order->get_date_created() );
			$this->placeholders['{order_number}'] = $order->get_order_number();
			$this->placeholders['{email_heading}']= $this->get_heading();
		}

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send(
				$this->get_recipient(),
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				$this->get_attachments()
			);
		}

		$this->restore_locale();
	}

	public function get_content_html(): string {
		return $this->render_content( false );
	}

	public function get_content_plain(): string {
		return $this->render_content( true );
	}

	private function render_content( bool $plain_text ): string {
		if ( ! $this->object instanceof WC_Order ) {
			return '';
		}

		ob_start();
		if ( ! $plain_text ) {
			do_action( 'woocommerce_email_header', $this->get_heading(), $this );
			echo wp_kses_post( wpautop( $this->get_option( 'message', $this->default_message ) ) );
		} else {
			echo wp_strip_all_tags( $this->get_option( 'message', $this->default_message ) ) . "\n\n";
		}

		do_action( 'woocommerce_email_order_details', $this->object, false, $plain_text, $this );
		do_action( 'woocommerce_email_order_meta', $this->object, false, $plain_text, $this );
		do_action( 'woocommerce_email_customer_details', $this->object, false, $plain_text, $this );

		$additional_content = $this->get_additional_content();
		if ( $additional_content ) {
			echo $plain_text
				? wp_strip_all_tags( wptexturize( $additional_content ) ) . "\n\n"
				: '<div>' . wp_kses_post( wpautop( wptexturize( $additional_content ) ) ) . '</div>';
		}

		if ( ! $plain_text ) {
			do_action( 'woocommerce_email_footer', $this );
		}
		return (string) ob_get_clean();
	}
}
