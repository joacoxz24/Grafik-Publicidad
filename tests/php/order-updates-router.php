<?php
require __DIR__.'/order-updates-env.php';
if (isset($_SERVER['HTTP_X_TEST_NO_PERMISSION'])) $caps=[];
if ($_SERVER['REQUEST_METHOD']==='POST') Grafik_Order_Updates::ajax();
if (isset($_GET['file'])) Grafik_Order_Updates::download();
if (isset($_GET['email'])) {Grafik_Order_Updates::email_content(wc_get_order(29),false,false,(object)['id'=>$_GET['email']]);exit;}
echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>Prueba local — pedido</title><style>body{font:15px Arial;margin:24px}*{box-sizing:border-box}</style><style>'.file_get_contents(GRAFIK_TYVEK_DIR.'assets/css/order-updates.css').'</style><form id="post">';
Grafik_Order_Updates::box(wc_get_order(29));
echo '<button type="submit">Actualizar pedido</button></form><script>'.file_get_contents(GRAFIK_TYVEK_DIR.'assets/js/order-updates.js').'</script></html>';
