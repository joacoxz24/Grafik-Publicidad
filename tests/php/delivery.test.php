<?php
define('ABSPATH', __DIR__);
function add_action(...$args) {}
function add_filter(...$args) {}
function register_activation_hook(...$args) {}
function register_deactivation_hook(...$args) {}
function plugin_dir_path($file) { return dirname($file).'/'; }
function plugin_dir_url($file) { return ''; }
function wp_unslash($value) { return $value; }
function sanitize_key($value) { return strtolower($value); }
function sanitize_text_field($value) { return strip_tags($value); }
function esc_html($value) { return htmlspecialchars($value); }
function wc_add_notice($message, $type) { $GLOBALS['notices'][]=$message; }
class WC_Order {
 public array $meta=[];
 function update_meta_data($key,$value) { $this->meta[$key]=$value; }
 function delete_meta_data($key) { unset($this->meta[$key]); }
 function get_meta($key,$single=true) { return $this->meta[$key]??''; }
}
require __DIR__.'/../../wordpress/grafik-tyvek-configurator/grafik-tyvek-configurator.php';
$plugin=(new ReflectionClass(Grafik_Tyvek_Configurator::class))->newInstanceWithoutConstructor();
$checks=0;
function check($ok) { global $checks; ++$checks; if(!$ok) throw new Exception('Check '.$checks.' failed'); }
$base=['grafik_delivery_method'=>'transport','grafik_shipping_rut'=>'123','grafik_shipping_address'=>'Sucursal','grafik_shipping_region'=>'Biobío','grafik_shipping_city'=>'Concepción'];
foreach(Grafik_Tyvek_Configurator::CARRIERS as $key=>$label) {
 $_POST=$base+['grafik_shipping_carrier'=>$key]; $GLOBALS['notices']=[];
 $plugin->validate_delivery_fields(); check(!$GLOBALS['notices']);
 $order=new WC_Order; $plugin->save_delivery_fields($order); check($order->get_meta('_grafik_shipping_carrier')===$key);
 foreach([true,false] as $admin) check($plugin->email_delivery_fields([],$admin,$order)['grafik_shipping_carrier']['value']===$label);
 ob_start(); $plugin->admin_delivery_fields($order); check(str_contains(ob_get_clean(),$label));
}
foreach(['','unknown',['starken']] as $invalid) {
 $_POST=$base+['grafik_shipping_carrier'=>$invalid]; $GLOBALS['notices']=[];
 $plugin->validate_delivery_fields(); check(count($GLOBALS['notices'])===1);
 $order=new WC_Order; $plugin->save_delivery_fields($order); check($order->get_meta('_grafik_shipping_carrier')==='');
}
$_POST=['grafik_delivery_method'=>'pickup','grafik_shipping_carrier'=>'starken']; $GLOBALS['notices']=[];
$order=new WC_Order; $order->update_meta_data('_grafik_shipping_carrier','bluexpress');
$plugin->validate_delivery_fields(); check(!$GLOBALS['notices']); $plugin->save_delivery_fields($order); check($order->get_meta('_grafik_shipping_carrier')==='');
$email=$plugin->email_delivery_fields([],false,$order); check(!isset($email['grafik_shipping_carrier'])); check($email['grafik_delivery_method']['value']==='Retiro coordinado CONCEPCIÓN');
$order=new WC_Order; $order->update_meta_data('_grafik_delivery_method','transport'); check($plugin->email_delivery_fields([],true,$order)['grafik_shipping_carrier']['value']==='No especificado');
echo "PASS: $checks delivery checks.\n";
