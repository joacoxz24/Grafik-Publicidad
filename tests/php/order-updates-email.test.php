<?php
/** Execute the existing custom email class and registration, not a replacement renderer. */
define('GRAFIK_TEST_REAL_PLUGIN',true);
require __DIR__.'/order-updates-env.php';
function wc_format_datetime($d){return '11/11/2026';}
function wp_kses_post($s){return $s;}
function wpautop($s){return '<p>'.$s.'</p>';}
function wp_strip_all_tags($s){return strip_tags($s);}
function wptexturize($s){return $s;}
#[AllowDynamicProperties]
class WC_Email {
 public $object=false,$recipient='',$settings=[],$sent=[];
 function __construct(){}
 function get_blogname(){return 'Grafik';}
 function setup_locale(){}
 function restore_locale(){}
 function get_heading(){return $this->heading;}
 function get_subject(){return $this->subject;}
 function get_option($k,$d=''){return $this->settings[$k]??$d;}
 function get_recipient(){return $this->recipient;}
 function get_headers(){return [];}
 function get_attachments(){return [];}
 function get_additional_content(){return $this->settings['additional_content']??'';}
 function get_content(){return $this->get_content_html();}
 function is_enabled(){return apply_filters('woocommerce_email_enabled_'.$this->id,true,$this->object,$this);}
 function send($to,$subject,$html,$headers,$attachments){$this->sent[]=compact('to','subject','html','attachments');do_action('woocommerce_email_sent',true,$this->id,$this);return true;}
}
$checks=0;
function check($ok,$why){$GLOBALS['checks']++;if(!$ok)throw new RuntimeException($why);}
try {
 $plugin=(new ReflectionClass(Grafik_Tyvek_Configurator::class))->newInstanceWithoutConstructor();
 $completed=new TestUpdateEmail('customer_completed_order');
 $registered=$plugin->order_email_classes(['WC_Email_Customer_Completed_Order'=>$completed]);
 check(count($registered)===4,'three existing custom emails plus native completed');
 check($registered['WC_Email_Customer_Completed_Order']===$completed,'native completed reused');
 $emails=array_values($registered);$order=new WC_Order(29);$order->meta['_grafik_delivery_method']='pickup_linares';$order->save_meta_data();
 add_action('woocommerce_email_header',fn()=>print('<header>Grafik Publicidad</header>'),10,0);
 add_action('woocommerce_email_order_details',function($o){echo '<table><tr><td>Resumen del pedido #'.$o->get_id().'</td></tr></table>';},10,1);
 add_action('woocommerce_email_footer',fn()=>print('<footer>Grafik</footer>'),10,0);
 $ready=$registered['Grafik_Email_Order_Ready'];
 $ready->settings=['message'=>'Mensaje personalizado en WooCommerce','additional_content'=>'Información adicional'];
 $plugin->send_status_email(29,'grafik-confirmado','grafik-listo',$order);
 check(count($ready->sent)===1,'ready sends on existing transition');
 $html=$ready->sent[0]['html'];
 foreach(['<header>','<footer>','Resumen del pedido','Mensaje personalizado en WooCommerce','Información adicional','Retiro LINARES'] as $expected)check(str_contains($html,$expected),'preserved '.$expected);
 check($ready->sent[0]['attachments']===[],'no attachment added');
 $plugin->send_status_email(29,'grafik-confirmado','grafik-listo',$order);check(count($ready->sent)===1,'repeated transition not duplicated');
 $plugin->send_status_email(29,'grafik-listo','grafik-listo',$order);check(count($ready->sent)===1,'same-state update no email');
 Grafik_Order_Updates::resend($order,'grafik_order_ready');check(count($ready->sent)===2,'explicit action reuses same email');
 check(str_contains($ready->get_content_plain(),'Retiro LINARES'),'existing plain template enriched');
 $shipped=$registered['Grafik_Email_Order_Shipped'];$plugin->send_status_email(29,'grafik-listo','grafik-enviado',$order);check(count($shipped->sent)===1,'existing shipped transition sends');
 $plugin->send_status_email(29,'grafik-enviado','completed',$order);check($completed->sent===0,'no duplicate completed sender added to custom transitions');
 echo "PASS: $checks existing-email lifecycle/template integration checks.\n";
} finally {Grafik_Order_Updates::unlock_all();foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($test_root,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST) as $e){$e->isDir()?rmdir($e->getPathname()):unlink($e->getPathname());}rmdir($test_root);}
