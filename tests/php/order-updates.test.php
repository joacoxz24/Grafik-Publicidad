<?php
require __DIR__.'/order-updates-env.php';
$checks=0;
function check($ok,$why){$GLOBALS['checks']++;if(!$ok)throw new RuntimeException($why);}
function rejects($fn,$why){try{$fn();}catch(RuntimeException $e){check(true,$why);return;}throw new RuntimeException('Not rejected: '.$why);}
function output($order,$id,$plain=false,$admin=false){ob_start();Grafik_Order_Updates::email_content($order,$admin,$plain,(object)['id'=>$id]);return ob_get_clean();}
$order=new WC_Order(29);
try {
 Grafik_Order_Updates::boxes();check(array_column($GLOBALS['boxes'],3)===['shop_order','woocommerce_page_wc-orders'],'both editors');
 check(Grafik_Order_Updates::can_manage($order),'authorized manager');$caps=[];check(!Grafik_Order_Updates::can_manage($order),'anonymous denied');$caps=['manage_woocommerce'];check(!Grafik_Order_Updates::can_manage($order),'must edit specific order');$caps=['edit_shop_orders','edit_shop_order'];check(Grafik_Order_Updates::can_manage($order),'shop manager allowed');
 $root=Grafik_Order_Updates::root();check(!str_starts_with($root,ABSPATH),'private root outside public');
 $_SERVER['DOCUMENT_ROOT']='';check(Grafik_Order_Updates::root()===$root,'CLI email uses initialized root');$_SERVER['DOCUMENT_ROOT']=$test_root.'/public';
 $tmp=$test_root.'/photo.png';file_put_contents($tmp,base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a9WQAAAAASUVORK5CYII='));
 $upload=['tmp_name'=>$tmp,'name'=>'foto.png','error'=>0];$photo=Grafik_Order_Updates::validate_file($upload);
 check($photo['mime']==='image/png'&&$photo['size']>0,'actual MIME validated');
 foreach(['foto.jpg','evil.php','x.svg','test.pdf'] as $name)rejects(fn()=>Grafik_Order_Updates::validate_file(array_replace($upload,['name'=>$name])),'forged extension '.$name);
 rejects(fn()=>Grafik_Order_Updates::validate_file(array_replace($upload,['error'=>UPLOAD_ERR_INI_SIZE])),'upload error');
 $pdf=$test_root.'/receipt.pdf';file_put_contents($pdf,"%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");
 $receipt=Grafik_Order_Updates::validate_file(['tmp_name'=>$pdf,'name'=>'recibo.pdf','error'=>0]);check($receipt['mime']==='application/pdf','real PDF');
 $large=$test_root.'/large.png';$fp=fopen($large,'w');ftruncate($fp,10485761);fclose($fp);rejects(fn()=>Grafik_Order_Updates::validate_file(['tmp_name'=>$large,'name'=>'large.png','error'=>0]),'file too large');
 wp_mkdir_p($root.'/29');foreach([$photo,$receipt] as $i=>$f)copy($i?$pdf:$tmp,$root.'/29/'.$f['id'].'.'.$f['ext']);
 $photo['emails']=['grafik_order_ready','customer_completed_order'];$receipt['emails']=['customer_completed_order','grafik_order_shipped'];
 $order->update_meta_data(Grafik_Order_Updates::META,['files'=>[$photo,$receipt],'tracking'=>[],'revision'=>'v1']);$order->save_meta_data();
 check(Grafik_Order_Updates::resolve($order,$photo)!==null,'resolves own file');check(Grafik_Order_Updates::resolve(new WC_Order(30),$photo)===null,'other order cannot resolve');
 $tamper=$photo;$tamper['id']='../secret';check(Grafik_Order_Updates::resolve($order,$tamper)===null,'traversal denied');
 parse_str(parse_url(Grafik_Order_Updates::url($order,$photo,true),PHP_URL_QUERY),$request);
 check(Grafik_Order_Updates::authorized_file($order,$request,false)===$photo,'signed guest link');
 foreach(['signature'=>str_repeat('a',64),'expires'=>time()-1,'file'=>$receipt['id']] as $k=>$v)check(Grafik_Order_Updates::authorized_file($order,array_replace($request,[$k=>$v]),false)===null,'tampered '.$k);
 check(Grafik_Order_Updates::authorized_file($order,['file'=>$photo['id']],false)===null,'ID alone denied');check(Grafik_Order_Updates::authorized_file($order,['file'=>$photo['id']],true)===$photo,'admin preview');
 $cross=new WC_Order(30);$cross->meta=$order->meta;check(Grafik_Order_Updates::authorized_file($cross,$request,false)===null,'signature bound to order');
 foreach(['pickup'=>'CONCEPCIÓN','pickup_linares'=>'LINARES','transport'=>'listo para despacho'] as $method=>$text){$order->update_meta_data('_grafik_delivery_method',$method);$html=output($order,'grafik_order_ready');check(str_contains($html,$text),'delivery-specific ready '.$method);check(str_contains($html,'foto.png')&&!str_contains($html,'recibo.pdf'),'ready file selection');}
 check(output($order,'customer_processing_order')===''&&output($order,'grafik_order_ready',false,true)==='','other/admin emails untouched');
 check(!str_contains(output($order,'customer_completed_order'),'Transportista'),'empty tracking hidden');
 $input=['revision'=>'v1','selection'=>[$photo['id']=>array_keys(Grafik_Order_Updates::EMAILS),$receipt['id']=>['grafik_order_shipped','invented']], 'tracking'=>['carrier'=>'Starken','code'=>'TRACK-1','url'=>'https://example.test/track?id=1','date'=>'2026-11-11']];
 Grafik_Order_Updates::save($order,$input);check($order->status==='grafik-listo','save never changes status');check(array_sum(array_column($emails,'sent'))===0,'save sends no emails');
 rejects(fn()=>Grafik_Order_Updates::save($order,$input),'stale edit rejected');
 $state=Grafik_Order_Updates::data($order);check($state['files'][1]['emails']===['grafik_order_shipped'],'allowlisted selection');
 foreach(['customer_completed_order','grafik_order_shipped'] as $id){$html=output($order,$id);check(str_contains($html,'Starken')&&str_contains($html,'TRACK-1')&&str_contains($html,'11/11/2026')&&str_contains($html,'Ver seguimiento'),'tracking '.$id);check(str_contains($html,'width:100%;max-width:480px;height:auto'),'responsive inline images');check(!str_contains($html,$root),'no physical path');check(str_contains(output($order,$id,true),'TRACK-1'),'plain tracking');}
 check(str_contains(output($order,'grafik_order_shipped'),'Ver comprobante'),'PDF link');check(!str_contains(output($order,'grafik_order_ready'),'TRACK-1'),'ready hides tracking');
 foreach(['pickup','pickup_linares'] as $method){$order->update_meta_data('_grafik_delivery_method',$method);check(!str_contains(output($order,'grafik_order_shipped'),'TRACK-1')&&!str_contains(output($order,'customer_completed_order'),'Starken'),'pickup hides transport '.$method);}
 $order->meta['_grafik_delivery_method']='';$order->shipping=[new class {function get_method_id(){return 'local_pickup';}function get_name(){return 'Retiro en tienda configurado';}}];
 check(str_contains(output($order,'grafik_order_ready'),'Retiro en tienda configurado'),'native WooCommerce pickup label');check(!str_contains(output($order,'customer_completed_order'),'TRACK-1'),'native pickup hides tracking');
 $order->shipping=[new class {function get_method_id(){return 'flat_rate';}}];check(str_contains(output($order,'customer_completed_order'),'TRACK-1'),'native WooCommerce transport');$order->shipping=[];
 foreach([['url'=>'javascript:alert(1)'],['url'=>'https://user:pass@example.test'],['date'=>'2026-02-30'],['carrier'=>'unknown']] as $bad)rejects(fn()=>Grafik_Order_Updates::tracking($bad),'invalid tracking');
 foreach($emails as $email){$email->trigger(29,$order);$email->trigger(29,$order);check($email->sent===1,'automatic idempotence '.$email->id);Grafik_Order_Updates::resend($order,$email->id);check($email->sent===2,'explicit resend '.$email->id);}
 $failed=new TestUpdateEmail('grafik_order_ready');$failed->object=$order;$order->meta['_grafik_notice_grafik_order_ready']='';$order->save_meta_data();
 check(Grafik_Order_Updates::allow_email(true,$order,$failed),'acquire failed-send test');check(!Grafik_Order_Updates::allow_email(true,$order,$failed),'concurrent send blocked');Grafik_Order_Updates::email_sent(false,$failed->id,$failed);check(Grafik_Order_Updates::allow_email(true,$order,$failed),'retry allowed after transport failure');Grafik_Order_Updates::unlock_all();
 $state=Grafik_Order_Updates::data($order);Grafik_Order_Updates::save($order,['revision'=>$state['revision'],'remove'=>[$photo['id']],'selection'=>[]]);check(!is_file($root.'/29/'.$photo['id'].'.png'),'removed file deleted');check(Grafik_Order_Updates::authorized_file($order,$request,false)===null,'old link revoked');
 check(output($order,'customer_completed_order')===''&&output($order,'grafik_order_shipped')==='','empty blocks hidden');
 define('GRAFIK_ORDER_MEDIA_DIR',ABSPATH.'unsafe');rejects(fn()=>Grafik_Order_Updates::root(),'public storage refused');
 echo "PASS: $checks order-update/security/notification checks.\n";
} finally {Grafik_Order_Updates::unlock_all();foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($test_root,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST) as $e){$e->isDir()?rmdir($e->getPathname()):unlink($e->getPathname());}rmdir($test_root);}
