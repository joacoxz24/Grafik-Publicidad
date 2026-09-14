<?php
/** Filesystem regression tests + gateway boundary tests. No network, orders or emails sent. */
define('ABSPATH', __DIR__);
function add_filter(...$args) { $GLOBALS['filters'][$args[0]][]=$args[1]; }
function add_action(...$args) { $GLOBALS['actions'][$args[0]][]=$args[1]; }
function wp_upload_dir(){return ['basedir'=>$GLOBALS['test_dir']];}
function wp_mkdir_p($dir){return is_dir($dir)||mkdir($dir,0700,true);}
function sanitize_file_name($name){return basename($name);}
function sanitize_mime_type($type){return $type;}
function sanitize_text_field($text){return $text;}
function wp_generate_uuid4(){return 'test-uuid-'.(++$GLOBALS['uuids']);}
function wp_json_encode($data){return json_encode($data);}
function current_user_can($cap){return in_array($cap,$GLOBALS['caps'],true);}
function is_admin(){return true;}
function absint($n){return abs((int)$n);}
function esc_html($s){return htmlspecialchars((string)$s);}
function esc_url($s){return htmlspecialchars($s);}
function admin_url($path){return 'https://example.test/wp/wp-admin/'.$path;}
function add_query_arg($args,$url){return $url.'?'.http_build_query($args);}
function wc_get_order($id){return $GLOBALS['orders'][$id]??false;}
function wp_die($message,$title='',$args=[]){throw new RuntimeException($message,$args['response']??0);}
class WC_Order_Item {
 public array $meta=[]; public int $saves=0;
 public function __construct(private int $id,private int $order_id){}
 public function get_id(){return $this->id;}
 public function get_order_id(){return $this->order_id;}
 public function get_meta($key,$single=true){return $this->meta[$key]??'';}
 public function update_meta_data($key,$value){$this->meta[$key]=$value;}
 public function save(){$this->saves++;}
 public function get_name(){return 'Producto <prueba>';}
 public function get_quantity(){return 10;}
}
class WC_Order {
 public array $items=[],$notes=[]; public string $status='pending',$currency='CLP',$gateway='flowpayment',$transaction='';
 public int $completions=0; public bool $date_paid=false;
 public function __construct(private int $id){}
 public function get_id(){return $this->id;}
 public function get_items(){return $this->items;}
 public function get_item($id){return $this->items[$id]??false;}
 public function get_currency(){return $this->currency;}
 public function get_total(){return 8600;}
 public function get_payment_method(){return $this->gateway;}
 public function get_status(){return $this->status;}
 public function is_paid(){return in_array($this->status,['processing','completed','grafik-confirmed'],true);}
 public function get_date_paid(){return $this->date_paid;}
 public function get_transaction_id(){return $this->transaction;}
 public function payment_complete($trx){$this->completions++;$this->date_paid=true;$this->status='processing';$this->transaction=$trx;return true;}
 public function update_status($status,$note){$this->status=$status;$this->notes[]=$note;}
 public function add_order_note($note){$this->notes[]=$note;}
}
require __DIR__.'/../../wordpress/grafik-tyvek-configurator/includes/class-grafik-order-files.php';
require __DIR__.'/../../wordpress/grafik-tyvek-configurator/includes/class-grafik-flow-lifecycle.php';
require __DIR__.'/../../wordpress/grafik-tyvek-configurator/includes/class-grafik-mail-transport.php';
$test_dir=sys_get_temp_dir().'/grafik-regression-'.bin2hex(random_bytes(6));
$uuids=0;$caps=[];$assertions=0;
function check($condition,$message){$GLOBALS['assertions']++;if(!$condition)throw new RuntimeException($message);}
function rejects($fn,$message,$code=null){try{$fn();}catch(RuntimeException $e){check(null===$code||$e->getCode()===$code,$message);return;}throw new RuntimeException('Did not reject: '.$message);}
function fixture($relative,$body='design'){$path=Grafik_Order_Files::root().'/'.$relative;wp_mkdir_p(dirname($path));file_put_contents($path,$body);return $path;}
try {
 $source=fixture('cart/session/abcdefghijklmnopqrst-Raíces-de-Colbun.png',base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a9WQAAAAASUVORK5CYII='));
 $legacy=[['name'=>'Raíces-de-Colbun.png','path'=>$source,'type'=>'image/png']];
 $item=new WC_Order_Item(1,29);$item->meta=['_grafik_files'=>json_encode($legacy),'_grafik_item_uuid'=>'uuid-one'];
 $first=Grafik_Order_Files::recover($item);
 check(count($first)===1,'one file after move');check(!file_exists($source),'source moved');
 check(is_file(Grafik_Order_Files::available($first[0])),'final file exists');
 foreach(['original_name','stored_name','relative_path','mime_type','file_size','order_id','order_item_id','grafik_item_uuid'] as $key){check(isset($first[0][$key]),'structured '.$key);}
 check(!str_contains(json_encode($first),$test_dir)&&!isset($first[0]['path']),'no server path');
 check($first[0]['order_id']===29&&$first[0]['order_item_id']===1,'ownership');
 check(Grafik_Order_Files::preview_type($first[0])==='image','PNG preview');
 $saves=$item->saves;check(Grafik_Order_Files::recover($item)===$first&&$item->saves===$saves,'repeat is no-op');
 // Reproduce old implementation: stale cart metadata + already moved file => []!
 $old_moved=[];foreach($legacy as $f){if(empty($f['path'])||!is_file($f['path']))continue;$old_moved[]=$f;}
 check($old_moved===[],'old destructive bug reproduced');
 $item->meta['_grafik_files']=json_encode($legacy);
 check(Grafik_Order_Files::recover($item)===$first,'stale cart reference recovered without duplicate');
 $item->meta['_grafik_files']='[]';
 check(Grafik_Order_Files::recover($item)===$first,'legacy order 29 exact directory recovery');
 fixture('orders/29/2/abcdefghijklmnopqrst-otro.zip','PK design');
 $other=new WC_Order_Item(2,29);$second=Grafik_Order_Files::recover($other);
 check(count($second)===1&&$second[0]['order_item_id']===2,'second product isolated');
 check($second[0]['original_name']==='otro.zip'&&Grafik_Order_Files::preview_type($second[0])==='','archive download only');
 check(count(Grafik_Order_Files::recover($item))===1,'does not import sibling files');
 check(Grafik_Order_Files::resolve('orders/29/1/../2/abcdefghijklmnopqrst-otro.zip','orders/29/1')===null,'traversal rejected');
 check(Grafik_Order_Files::resolve('orders/29/2/abcdefghijklmnopqrst-otro.zip','orders/29/1')===null,'other item rejected');
 check(Grafik_Order_Files::resolve('orders/29/1/C:\\secret','orders/29/1')===null,'Windows path rejected');
 $missing=new WC_Order_Item(3,29);$missing->meta['_grafik_files']=json_encode([['name'=>'lost.pdf','path'=>Grafik_Order_Files::root().'/cart/s/lost.pdf','type'=>'application/pdf']]);
 check(count(Grafik_Order_Files::recover($missing))===1,'missing reference retained');
 $order=new WC_Order(29);$order->items=[1=>$item,2=>$other];$orders=[29=>$order];
 $old_file=fixture('orders/30/1/abcdefghijklmnopqrst-legacy.png','legacy design');
 $retry=new WC_Order_Item(3,30);$retry->meta=['_grafik_files'=>'[]','_grafik_item_uuid'=>'retry-uuid','Archivos'=>'legacy.png'];
 $orders[30]=new WC_Order(30);$orders[30]->items=[3=>$retry];
 $recovered=Grafik_Order_Files::recover($retry);
 check(count($recovered)===1&&$recovered[0]['order_item_id']===3,'recreated item recovers orphan folder');
 check(!file_exists($old_file)&&str_starts_with($recovered[0]['relative_path'],'orders/30/3/'),'relocated under actual line-item ID');
 check(Grafik_Order_Files::recover($retry)===$recovered,'orphan recovery is repeatable');
 $ambiguous_file=fixture('orders/31/1/abcdefghijklmnopqrst-same.png','ambiguous design');
 $a=new WC_Order_Item(3,31);$b=new WC_Order_Item(4,31);
 $a->meta=['_grafik_item_uuid'=>'a','Archivos'=>'same.png'];$b->meta=['_grafik_item_uuid'=>'b','Archivos'=>'same.png'];
 $orders[31]=new WC_Order(31);$orders[31]->items=[3=>$a,4=>$b];
 check(Grafik_Order_Files::recover($a)===[]&&Grafik_Order_Files::recover($b)===[]&&file_exists($ambiguous_file),'ambiguous orphan never guessed');
 fixture('orders/31/1/.grafik-item.json',json_encode(['grafik_item_uuid'=>'b']));
 check(Grafik_Order_Files::recover($a)===[],'UUID excludes wrong candidate');
 $uuid_files=Grafik_Order_Files::recover($b);check(count($uuid_files)===1&&$uuid_files[0]['order_item_id']===4,'UUID disambiguates repeated filenames');
 $caps=[];rejects(fn()=>Grafik_Order_Files::download(),'unauthorized rejected',403);
 $caps=['edit_shop_orders'];check(Grafik_Order_Files::can_manage(),'shop managers supported');
 $_GET=['order_id'=>29,'item_id'=>99,'file_id'=>'anything'];rejects(fn()=>Grafik_Order_Files::download(),'unknown item rejected',404);
 $_GET=['order_id'=>29,'item_id'=>1,'file_id'=>hash('sha256',$second[0]['relative_path'])];rejects(fn()=>Grafik_Order_Files::download(),'other item file rejected',404);
 ob_start();Grafik_Order_Files::admin($item);$html=ob_get_clean();
 check(str_contains($html,'ARCHIVOS DEL CLIENTE')&&str_contains($html,'<img')&&str_contains($html,'Descargar'),'admin UI');
 check(!str_contains($html,$test_dir)&&!str_contains($html,'/uploads/'),'no public paths');
 $email=(object)['id'=>'new_order'];ob_start();Grafik_Order_Files::email_files($order,true,false,$email);$html=ob_get_clean();
 check(str_contains($html,'Raíces-de-Colbun.png')&&str_contains($html,'grafik_download_design')&&str_contains($html,'otro.zip'),'admin email file links per item');
 ob_start();Grafik_Order_Files::email_files($order,false,false,$email);check(ob_get_clean()==='','customer email never exposes admin links');
 Grafik_Order_Files::boot();$hidden=$filters['woocommerce_hidden_order_itemmeta'][0]([]);check(in_array('_grafik_files',$hidden,true)&&in_array('_grafik_item_uuid',$hidden,true),'hidden internal metadata');
 $result=['commerce_order'=>29,'currency'=>'CLP','amount'=>8600,'status'=>2,'id'=>12345];
 Grafik_Flow_Lifecycle::apply($result,$order);check($order->completions===1&&$order->transaction==='12345'&&$order->status==='processing','standard payment_complete with transaction');
 for($i=0;$i<3;$i++)Grafik_Flow_Lifecycle::apply($result,$order);
 check($order->completions===1&&count($order->notes)===1,'repeated callbacks do not repeat payment or note');
 $order->status='grafik-confirmed';Grafik_Flow_Lifecycle::apply($result,$order);check($order->status==='grafik-confirmed'&&$order->completions===1,'production state preserved');
 $late=$result;$late['status']=4;Grafik_Flow_Lifecycle::apply($late,$order);check($order->status==='grafik-confirmed','late cancellation cannot regress paid');
 $order->status='refunded';Grafik_Flow_Lifecycle::apply($result,$order);check($order->status==='refunded','refund preserved');
 foreach(['amount'=>8599,'currency'=>'USD','commerce_order'=>30,'status'=>99,'id'=>''] as $key=>$value){$invalid=$result;$invalid[$key]=$value;rejects(fn()=>Grafik_Flow_Lifecycle::apply($invalid,$order),'invalid '.$key);}
 $invalid=$result;unset($invalid['amount']);rejects(fn()=>Grafik_Flow_Lifecycle::apply($invalid,$order),'missing amount');
 $invalid=$result;$invalid['id']=999;rejects(fn()=>Grafik_Flow_Lifecycle::apply($invalid,$order),'second transaction');
 $order->gateway='cod';rejects(fn()=>Grafik_Flow_Lifecycle::apply($result,$order),'wrong gateway');
 $mailer=(object)['Mailer'=>'mail','From'=>'ventas@grafikpublicidad.cl','Sender'=>''];$actions['phpmailer_init'][0]($mailer);check($mailer->Sender==='ventas@grafikpublicidad.cl','authenticated envelope domain');
 $mailer->Mailer='smtp';$mailer->Sender='provider@example.test';$actions['phpmailer_init'][0]($mailer);check($mailer->Sender==='provider@example.test','SMTP sender preserved');
 echo "OK: $assertions assertions; filesystem recovery, endpoint isolation, email content and Flow lifecycle.\n";
} finally {
 if(is_dir($test_dir)) {foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($test_dir,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST) as $entry){$entry->isDir()?rmdir($entry->getPathname()):unlink($entry->getPathname());}rmdir($test_dir);}
}
