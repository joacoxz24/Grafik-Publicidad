<?php
define('ABSPATH',__DIR__); define('GRAFIK_SALES_EMAIL','ventas@example.test');
$hooks=[];$page='';$external=false;
function add_filter($name,$fn){$GLOBALS['hooks'][$name]=$fn;}
function add_action($name,$fn,$priority=10){$GLOBALS['hooks'][$name]=$fn;}
function apply_filters($name,$value){return $GLOBALS['external']||$value;}
function is_front_page(){return $GLOBALS['page']==='home';}
function is_page($name){return $GLOBALS['page']===$name;}
function is_shop(){return $GLOBALS['page']==='shop';}
function is_paged(){return false;}
function home_url($path){return 'https://example.test'.$path;}
function grafik_instagram_url(){return 'https://www.instagram.com/grafikpublicidad.cl';}
function esc_attr($s){return htmlspecialchars($s,ENT_QUOTES);}
function wp_json_encode($s,$flags){return json_encode($s,$flags);}
require __DIR__.'/../../wordpress/grafik-publicidad/inc/seo.php';
$count=0;
function check($v){global $count;++$count;if(!$v)throw new Exception('Check '.$count);}
$descriptions=[];
foreach(['home','pulseras','chapitas','shop'] as $page){
 $data=grafik_seo_page();check(!empty($data['description']));$descriptions[]=$data['description'];
 $title=$hooks['document_title_parts'](['title'=>'Original','site'=>'Site','page'=>'2']);check(!isset($title['site'])&&$title['page']==='2');
 ob_start();$hooks['wp_head']();$out=ob_get_clean();check(substr_count($out,'name="description"')===1);
 if($page==='home'){preg_match('~<script[^>]*>(.*?)</script>~s',$out,$match);check(json_decode($match[1],true)['@type']==='Organization');}else{check(!str_contains($out,'application/ld+json'));}
}
check(count(array_unique($descriptions))===4);
foreach(['checkout','cart','account','other'] as $page){check(grafik_seo_page()===[]);}
$page='home';$external=true;ob_start();$hooks['wp_head']();check(ob_get_clean()==='');check($hooks['document_title_parts'](['title'=>'Plugin title'])===['title'=>'Plugin title']);
echo "PASS: $count SEO metadata, scope and plugin coexistence checks.\n";
