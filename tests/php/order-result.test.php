<?php
define('ABSPATH',__DIR__);
function esc_attr($s){return htmlspecialchars($s,ENT_QUOTES);}
function esc_html($s){return htmlspecialchars($s,ENT_QUOTES);}
function esc_url($s){return htmlspecialchars($s,ENT_QUOTES);}
function wp_kses_post($s){return $s;}
function home_url($s){return 'https://example.test'.$s;}
function grafik_products_url(){return 'https://example.test/productos/';}
function wc_get_page_permalink($s){return 'https://example.test/account/';}
function wc_format_datetime($d){return '13 septiembre 2026';}
function wc_get_order_status_name($s){return $s;}
function do_action($name,...$args){$GLOBALS['hooks'][]=$name;}
class ResultOrder {
 public function __construct(public string $status){}
 public function get_status(){return $this->status;}
 public function is_paid(){return in_array($this->status,['processing','completed','grafik-confirmado','grafik-listo','grafik-enviado'],true);}
 public function get_id(){return 123;}
 public function get_order_number(){return '123';}
 public function get_date_created(){return true;}
 public function get_formatted_order_total(){return '$43.860';}
 public function get_payment_method_title(){return 'Flow';}
 public function get_payment_method(){return 'flow';}
 public function needs_payment(){return in_array($this->status,['failed','pending']);}
 public function get_checkout_payment_url(){return 'https://example.test/pay/123?key=test';}
 public function get_checkout_order_received_url(){return 'https://example.test/result/123?key=test';}
}
$checks=0;
foreach(['processing'=>'¡Compra completada!','completed'=>'¡Compra completada!','grafik-confirmado'=>'¡Compra completada!','pending'=>'Pedido recibido, pago pendiente','on-hold'=>'Pedido recibido, pago pendiente','failed'=>'No se pudo completar el pago','cancelled'=>'Pedido cancelado','refunded'=>'Pedido reembolsado','missing'=>'No pudimos mostrar este pedido'] as $state=>$title){
 $order=$state==='missing'?false:new ResultOrder($state);$GLOBALS['hooks']=[];
 $html=(function($order){ob_start();include __DIR__.'/../../wordpress/grafik-publicidad/woocommerce/checkout/thankyou.php';return ob_get_clean();})($order);
 if(!str_contains($html,$title))throw new Exception($state.' title');$checks++;
 if(str_contains($html,'Reintentar pago')!==($state==='failed'))throw new Exception($state.' retry '.$html);$checks++;
 if($order && !in_array('woocommerce_thankyou_flow',$GLOBALS['hooks']))throw new Exception('gateway hook');$checks++;
 if($order && !in_array('woocommerce_thankyou',$GLOBALS['hooks']))throw new Exception('details hook');$checks++;
 if(!$order && str_contains($html,'43.860'))throw new Exception('missing order leaks details');
}
echo "PASS: $checks result assertions\n";
