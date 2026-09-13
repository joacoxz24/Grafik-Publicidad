<?php
/** Run: php tests/php/catalog.test.php (no hosting, WordPress or database needed). */
define( 'ABSPATH', __DIR__ . '/' );
function add_action(...$args) {}
function add_filter(...$args) {}
function add_shortcode(...$args) {}
function register_activation_hook(...$args) {}
function register_deactivation_hook(...$args) {}
function plugin_dir_path($file) { return dirname($file) . '/'; }
function plugin_dir_url($file) { return 'https://example.test/plugins/'; }
function absint($value) { return abs((int)$value); }
function get_option($key, $fallback = false) { return $GLOBALS['options'][$key] ?? $fallback; }
function update_option($key, $value) { $GLOBALS['options'][$key]=$value; }
function wc_get_product($id) { return $GLOBALS['products'][$id] ?? false; }
function wc_format_decimal($value) { return (string)$value; }
function wc_add_notice($message, $type) { $GLOBALS['notices'][]=$message; }
function number_format_i18n($number) { return (string)$number; }
function is_admin() { return false; }
function wp_doing_ajax() { return false; }
function wp_list_pluck($rows,$key) { return array_column($rows,$key); }
function wp_json_encode($value) { return json_encode($value); }
function esc_attr($value) { return htmlspecialchars((string)$value); }
function esc_html($value) { return htmlspecialchars((string)$value); }
function WC() { return $GLOBALS['wc']; }
function wc_get_product_id_by_sku($sku) { foreach($GLOBALS['products'] as $id=>$p) if(($p->fields['sku']??'')===$sku) return $id; return 0; }
class WooCommerce {}
class WC_Product {
 public array $fields=[]; public float $price=0; public array $meta=[];
 public function set_price($price) { $this->price=(float)$price; }
 public function get_regular_price() { return $this->fields['regular_price']??500; }
 public function get_meta($key) { return $this->meta[$key]??''; }
 public function update_meta_data($key,$value) { $this->meta[$key]=$value; }
 public function get_id(){return $this->fields['id'];}
 public function __call($name,$args) { if(str_starts_with($name,'set_')) $this->fields[substr($name,4)]=$args[0]; }
 public function save(){ $id=$this->fields['id']??(count($GLOBALS['products'])+100); $this->fields['id']=$id; $GLOBALS['products'][$id]=$this; return $id; }
}
class WC_Product_Simple extends WC_Product {}
class WC_Cart { public array $cart_contents=[]; public function get_cart(){return $this->cart_contents;} }
class WC_Order {}
class WC_Order_Item_Product {
 public array $meta=[]; private int $qty;
 public function __construct($qty){$this->qty=$qty;}
 public function get_quantity(){return $this->qty;}
 public function add_meta_data($key,$value,$unique){$this->meta[$key]=$value;}
}
function expect($actual,$expected,$label) { if($actual!==$expected) throw new RuntimeException($label.': '.json_encode([$actual,$expected])); $GLOBALS['checks']++; }
$GLOBALS['checks']=0;$GLOBALS['products']=[];$GLOBALS['options']=['grafik_tyvek_product_id'=>1];$GLOBALS['notices']=[];
require __DIR__.'/../../wordpress/grafik-tyvek-configurator/grafik-tyvek-configurator.php';
require __DIR__.'/../../wordpress/grafik-tyvek-configurator/includes/class-grafik-chapitas.php';
$chapitas=new Grafik_Chapitas(); $tyvek=Grafik_Tyvek_Configurator::instance();
$chapitas->setup(); expect(count($GLOBALS['products']),3,'Three products created');
$chapitas->setup(); expect(count($GLOBALS['products']),3,'Setup is idempotent');
foreach ([['alfiler',10,500],['alfiler',100,500],['alfiler',101,400],['llavero',5,750],['llavero',50,750],['llavero',51,650],['destapador',5,950],['destapador',50,950],['destapador',51,860]] as [$kind,$q,$price]) {
 $rule=Grafik_Chapitas::rule($kind);
 expect(Grafik_Chapitas::unit_price($rule,$q),(float)$price,"Price $kind $q");
 $data=['grafik_item_uuid'=>'test','grafik_chapita'=>$kind];
 expect($chapitas->validate_add(true,Grafik_Chapitas::id($kind),$q,0,[],$data),true,"Allow $kind $q");
 expect($chapitas->validate_add(true,Grafik_Chapitas::id($kind),$q),false,"Reject unconfigured $kind");
}
foreach(['alfiler'=>9,'llavero'=>4,'destapador'=>4] as $kind=>$quantity) {
 $data=['grafik_item_uuid'=>'test','grafik_chapita'=>$kind];$id=Grafik_Chapitas::id($kind);
 expect($chapitas->validate_add(true,$id,$quantity,0,[],$data),false,'Reject below minimum');
 expect($chapitas->validate_add(true,$id,10.5,0,[],$data),false,'Reject fractions');
 expect($chapitas->validate_update(true,'key',['product_id'=>$id],10001),false,'Reject too many');
 expect($chapitas->validate_update(true,'key',['product_id'=>$id],0),true,'Allow removal');
}
$cart=new WC_Cart(); $GLOBALS['wc']=(object)['cart'=>$cart];
$cart->cart_contents=[
 'tyvek'=>['product_id'=>1,'quantity'=>10,'grafik_item_uuid'=>'t','data'=>new WC_Product(),'grafik_details'=>'VIP','grafik_files'=>[]],
 'chapita'=>['product_id'=>Grafik_Chapitas::id('llavero'),'quantity'=>51,'grafik_item_uuid'=>'c','grafik_chapita'=>'llavero','data'=>clone wc_get_product(Grafik_Chapitas::id('llavero')),'grafik_details'=>'Logo','grafik_files'=>[['name'=>'logo.pdf','path'=>'/protected/logo.pdf']]],
 'chapita2'=>['product_id'=>Grafik_Chapitas::id('llavero'),'quantity'=>5,'grafik_item_uuid'=>'c2','grafik_chapita'=>'llavero','data'=>clone wc_get_product(Grafik_Chapitas::id('llavero')),'grafik_details'=>'Other design','grafik_files'=>[]],
 'other'=>['product_id'=>99,'quantity'=>1,'data'=>new WC_Product()]
];
$cart->cart_contents['other']['data']->price=999;
$tyvek->apply_price($cart);$chapitas->prices($cart);$tyvek->apply_price($cart);$chapitas->prices($cart);
expect($cart->cart_contents['tyvek']['data']->price,8400.0,'Tyvek price per pack unchanged');
expect($cart->cart_contents['chapita']['data']->price,650.0,'Chapita bulk unit price');
expect($cart->cart_contents['chapita2']['data']->price,750.0,'Separate designs keep separate tiers');
expect($cart->cart_contents['other']['data']->price,999.0,'Other products unchanged');
$line=$cart->cart_contents['chapita'];
expect($tyvek->cart_quantity_label('original','chapita',$line),'original','Chapitas remain individual units');
expect($tyvek->cart_item_data([],$line),[],'No Tyvek color or pack metadata on chapitas');
$orderItem=new WC_Order_Item_Product(51);$order=new WC_Order();
$tyvek->create_order_item($orderItem,'chapita',$line,$order);$chapitas->order_item($orderItem,'chapita',$line,$order);
expect($orderItem->meta['Formato'],'Chapita llavero · 58 mm','Order format');
expect($orderItem->meta['Archivos'],'logo.pdf','Order design file');
expect(isset($orderItem->meta['Color base']),false,'No Tyvek metadata');
expect(json_decode($orderItem->meta['_grafik_files'],true)[0]['path'],'/protected/logo.pdf','Protected file metadata retained');
$cart->cart_contents['chapita']['quantity']=5;$chapitas->prices($cart);
expect($cart->cart_contents['chapita']['data']->price,750.0,'Price recalculated when quantity falls below tier');
$before=count($GLOBALS['notices']);$cart->cart_contents['chapita']['quantity']=1;$chapitas->check_cart();expect(count($GLOBALS['notices'])>$before,true,'Invalid cart blocked at checkout');

$destapador=wc_get_product(Grafik_Chapitas::id('destapador'));
$destapador->update_meta_data('_grafik_chapita_bulk',750);
$chapitas->upgrade_bulk_price();
expect(Grafik_Chapitas::rule('destapador')['bulk'],860.0,'Existing default migrates to 860');
$cart->cart_contents['destapador']=['product_id'=>$destapador->get_id(),'quantity'=>51,'data'=>clone $destapador];
$chapitas->prices($cart);
expect($cart->cart_contents['destapador']['data']->price*51,43860.0,'51 destapadores total');
$cart->cart_contents['destapador']['quantity']=50;$chapitas->prices($cart);
expect($cart->cart_contents['destapador']['data']->price,950.0,'50 destapadores retain regular price');
$destapador->update_meta_data('_grafik_chapita_bulk',750);
$chapitas->upgrade_bulk_price();
expect(Grafik_Chapitas::rule('destapador')['bulk'],750.0,'Migration does not repeat after admin edit');
unset($GLOBALS['options']['grafik_chapitas_bulk_121']);
$destapador->update_meta_data('_grafik_chapita_bulk',880);
$chapitas->upgrade_bulk_price();
expect(Grafik_Chapitas::rule('destapador')['bulk'],880.0,'Migration preserves custom price');
echo 'PASS: '.$GLOBALS['checks']." catalog assertions\n";
