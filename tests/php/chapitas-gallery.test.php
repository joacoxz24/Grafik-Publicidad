<?php
define('ABSPATH', __DIR__);
function add_action(...$args) {}
function absint($n) { return abs((int)$n); }
function get_theme_mod($key,$default=0) { return $GLOBALS['mods'][$key]??$default; }
function wp_attachment_is_image($id) { return $id!==99; }
function wp_get_attachment_image_url($id,$size) { return $id!==98; }
require __DIR__.'/../../wordpress/grafik-publicidad/inc/chapitas-gallery.php';
function verify($expected) { if(array_column(grafik_chapitas_gallery_slides(),'id')!==$expected) throw new Exception('Gallery ordering failed'); }
$mods=[];verify([0]);
$mods=['grafik_chapitas_real_1'=>12];verify([0,12]);
$mods=['grafik_chapitas_promo_1'=>5,'grafik_chapitas_promo_3'=>7,'grafik_chapitas_real_1'=>12];verify([5,7,12]);
$mods['grafik_chapitas_real_2']=5;verify([5,7,12]);
$mods=['grafik_chapitas_promo_1'=>99,'grafik_chapitas_real_1'=>98];verify([0]);
echo "PASS: 5 gallery ordering, fallback and invalid-image checks.\n";
