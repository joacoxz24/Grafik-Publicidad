<?php
/** Local UI fixture rendering the real gallery template; no WordPress required. */
define('ABSPATH',__DIR__);
function grafik_chapitas_gallery_slides(){return array_map(static fn($i)=>array('id'=>$i,'caption'=>'Fotografía de prueba '.$i),range(1,7));}
function esc_attr($value){return htmlspecialchars((string)$value,ENT_QUOTES);}
function esc_html($value){return esc_attr($value);}
function esc_url($value){return esc_attr($value);}
function get_post_meta(...$args){return '';}
function wp_get_attachment_image($id,$size,$icon,$attributes){return '<img src="__IMG__" width="1024" height="1024" alt="'.esc_attr($attributes['alt']).'">';}
include __DIR__.'/../../wordpress/grafik-publicidad/template-parts/chapitas-gallery.php';
