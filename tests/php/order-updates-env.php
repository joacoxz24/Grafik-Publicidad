<?php
/** Local isolated WP/WC doubles. Never connects to the store or sends real emails. */
$test_root = getenv('GRAFIK_MEDIA_TEST_ROOT') ?: sys_get_temp_dir().'/grafik-updates-'.getmypid();
if (!is_dir($test_root.'/public')) mkdir($test_root.'/public',0700,true);
define('ABSPATH',$test_root.'/public/');
define('DAY_IN_SECONDS',86400);
if (!defined('GRAFIK_TEST_REAL_PLUGIN')) {
 define('GRAFIK_TYVEK_DIR',__DIR__.'/../../wordpress/grafik-tyvek-configurator/');
 define('GRAFIK_TYVEK_URL','/plugin/');
 define('GRAFIK_TYVEK_VERSION','test');
}
$_SERVER['DOCUMENT_ROOT']=$test_root.'/public';
$caps=['manage_woocommerce','edit_shop_order']; $hooks=[]; $options=[];
function add_action($hook,$fn,$priority=10,$args=1){$GLOBALS['hooks'][$hook][$priority][]=[$fn,$args];}
function add_filter(...$args){add_action(...$args);}
function do_action($hook,...$args){$groups=$GLOBALS['hooks'][$hook]??[];ksort($groups);foreach($groups as $list)foreach($list as [$fn,$n])$fn(...array_slice($args,0,$n));}
function apply_filters($hook,$value,...$args){$groups=$GLOBALS['hooks'][$hook]??[];ksort($groups);foreach($groups as $list)foreach($list as [$fn,$n])$value=$fn(...array_slice([$value,...$args],0,$n));return $value;}
function current_user_can($cap,...$args){return in_array($cap,$GLOBALS['caps'],true);}
function home_url(){return 'https://example.test';}
function path_is_absolute($p){return str_starts_with($p,'/')||(bool)preg_match('/^[a-z]:[\\\\\/]/i',$p);}
function wp_mkdir_p($p){return is_dir($p)||mkdir($p,0700,true);}
function sanitize_file_name($p){return basename($p);}
function sanitize_text_field($s){return strip_tags($s);}
function wp_unslash($s){return $s;}
function wp_parse_url($s){return parse_url($s);}
function esc_url_raw($s,...$a){return $s;}
function esc_attr($s){return htmlspecialchars((string)$s,ENT_QUOTES);}
function esc_html($s){return esc_attr($s);}
function esc_url($s){return esc_attr($s);}
function admin_url($s){return 'http://127.0.0.1:5192/'.$s;}
function add_query_arg($args,$url){return $url.'?'.http_build_query($args);}
function wp_salt($s){return 'local-test-secret';}
function wp_delete_file($p){unlink($p);}
function absint($n){return abs((int)$n);}
function get_option($k,$d=false){return $GLOBALS['options'][$k]??$d;}
function update_option($k,$v,...$a){$GLOBALS['options'][$k]=$v;}
function delete_option($k){unset($GLOBALS['options'][$k]);}
function add_option($k,$v,...$a){if(isset($GLOBALS['options'][$k]))return false;$GLOBALS['options'][$k]=$v;return true;}
function wp_date($f,$s){return gmdate($f,$s);}
function wp_create_nonce($s){return hash('sha256',$s);}
function selected($a,$b,$echo=true){return $a===$b?'selected':'';}
function checked($a,$b,$echo=true){return $a===$b?'checked':'';}
function wp_enqueue_style(...$a){}
function wp_enqueue_script(...$a){}
function add_meta_box(...$a){$GLOBALS['boxes'][]=$a;}
function wc_get_page_screen_id($a){return 'woocommerce_page_wc-orders';}
function wp_die($s,$t='',$args=[]){http_response_code($args['response']??400);echo $s;exit;}
function wp_send_json_error($d,$code=400){http_response_code($code);header('Content-Type: application/json');echo json_encode(['success'=>false,'data'=>$d]);exit;}
function wp_send_json_success($d){header('Content-Type: application/json');echo json_encode(['success'=>true,'data'=>$d]);exit;}
function check_ajax_referer($action,$field){if(($_POST[$field]??'')!==wp_create_nonce($action))wp_send_json_error(['message'=>'Nonce inválido'],403);}
function nocache_headers(){header('Cache-Control: no-store, private');}
class WC_Order {
 public array $meta=[],$shipping=[]; public int $saves=0; public string $status='grafik-listo';
 function __construct(private int $id){$this->read_meta_data();}
 function get_id(){return $this->id;}
 function get_meta($k,$s=true){return $this->meta[$k]??'';}
 function update_meta_data($k,$v){$this->meta[$k]=$v;}
 function get_billing_email(){return 'cliente@example.test';}
 function get_shipping_methods(){return $this->shipping;}
 function get_order_number(){return (string)$this->id;}
 function get_date_created(){return true;}
 function save_meta_data(){$this->saves++;file_put_contents($GLOBALS['test_root'].'/order-'.$this->id.'.json',json_encode($this->meta));}
 function read_meta_data($force=false){$file=$GLOBALS['test_root'].'/order-'.$this->id.'.json';if(is_file($file))$this->meta=json_decode(file_get_contents($file),true);}
}
function wc_get_order($id){return in_array($id,[29,30],true)?new WC_Order($id):false;}
if (defined('GRAFIK_TEST_REAL_PLUGIN')) {
 function register_activation_hook(...$a){}
 function register_deactivation_hook(...$a){}
 function plugin_dir_path($s){return dirname($s).'/';}
 function plugin_dir_url($s){return '/plugin/';}
 require __DIR__.'/../../wordpress/grafik-tyvek-configurator/grafik-tyvek-configurator.php';
} else {
 class Grafik_Tyvek_Configurator {const DELIVERY_LABELS=['pickup'=>'Retiro coordinado CONCEPCIÓN','pickup_linares'=>'Retiro LINARES','transport'=>'Envío por transporte'];}
}
require GRAFIK_TYVEK_DIR.'includes/class-grafik-order-updates.php';
Grafik_Order_Updates::boot();
class TestUpdateEmail {
 public $object; public int $sent=0;
 function __construct(public string $id){}
 function trigger($id,$order){$this->object=$order;if(apply_filters('woocommerce_email_enabled_'.$this->id,true,$order,$this)){$this->sent++;do_action('woocommerce_email_sent',true,$this->id,$this);}}
}
$emails=array_map(fn($id)=>new TestUpdateEmail($id),array_keys(Grafik_Order_Updates::EMAILS));
function WC(){return new class {function mailer(){return new class {function get_emails(){return $GLOBALS['emails'];}};}};}
