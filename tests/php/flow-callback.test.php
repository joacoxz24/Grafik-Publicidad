<?php
/** Server verification/locking boundary; fake HTTP only, never calls Flow. */
define('ABSPATH',__DIR__);
function add_filter(...$args){}
function wp_remote_get($url,$args){$GLOBALS['http_calls'][]=[$url,$args];return $GLOBALS['response'];}
function wp_remote_retrieve_response_code($r){return $r['code'];}
function wp_remote_retrieve_body($r){return $r['body'];}
function is_wp_error($r){return $r instanceof Exception;}
function wc_get_order($id){$GLOBALS['reads'][]=$id;return new WC_Order($id);}
class WC_Order {
 public function __construct(public int $id){}
 public function get_data_store(){return new class{public function read($order){$GLOBALS['refreshes']++;}};}
}
class WC_Flow_Gateway {public array $settings=['mode'=>'PROD','api_key'=>'test-key']; public function get_option($key){return $this->settings[$key];}}
class Grafik_Flow_Lifecycle {public static function apply($result,$order){$GLOBALS['applications']++;if($GLOBALS['reject_apply'])throw new RuntimeException('Invalid payment');}}
class FakeDB {
 public string $prefix='wp_';public array $queries=[];public bool $available=true;
 public function prepare($sql,$value){return str_replace('%s',"'".$value."'",$sql);}
 public function get_var($sql){$this->queries[]=$sql;return str_contains($sql,'GET_LOCK')&&!$this->available?'0':'1';}
}
require __DIR__.'/../../wordpress/grafik-tyvek-configurator/includes/class-grafik-flow-gateway.php';
$gateway=new Grafik_Flow_Gateway();$method=new ReflectionMethod($gateway,'grafik_verified_order');
$wpdb=new FakeDB();$http_calls=[];$reads=[];$refreshes=0;$applications=0;$reject_apply=false;$checks=0;
$response=['code'=>200,'body'=>json_encode(['commerce_order'=>29,'status'=>2,'amount'=>8600,'currency'=>'CLP','id'=>12345])];
function check($ok,$message){$GLOBALS['checks']++;if(!$ok)throw new RuntimeException($message);}
function rejected($method,$gateway){try{$method->invoke($gateway);}catch(RuntimeException $e){$GLOBALS['checks']++;return;}throw new RuntimeException('Expected rejection');}
$_POST=['token'=>'abc123'];$order=$method->invoke($gateway);
check($order->id===29&&$applications===1&&$refreshes===1,'verified order applied after reload');
check($http_calls[0][0]==='https://www.flow.cl/api/v2/order/token/abc123','fixed Flow HTTPS endpoint');
check($http_calls[0][1]['sslverify']===true&&$http_calls[0][1]['redirection']===0,'TLS required, credentials never redirected');
check($http_calls[0][1]['headers']['Authorization']==='Basic '.base64_encode('test-key:'),'matches installed Flow v2 auth');
check(str_contains($wpdb->queries[0],'GET_LOCK')&&str_contains($wpdb->queries[1],'RELEASE_LOCK'),'lock acquired and released');
$reject_apply=true;rejected($method,$gateway);check(str_contains(end($wpdb->queries),'RELEASE_LOCK'),'exception releases lock');$reject_apply=false;
$wpdb->available=false;$before=$applications;rejected($method,$gateway);check($applications===$before,'busy callback does not mutate order');$wpdb->available=true;
foreach([500,302,401] as $code){$response['code']=$code;rejected($method,$gateway);}check($applications===$before,'HTTP errors never apply payment');
$response=['code'=>200,'body'=>'{"code":103,"message":"not a payment"}'];rejected($method,$gateway);
$response=['code'=>200,'body'=>'not JSON'];rejected($method,$gateway);
$response=new RuntimeException('network unavailable');rejected($method,$gateway);
$before=count($http_calls);foreach(['../../secret',['array'],'',str_repeat('x',201)] as $bad){$_POST=['token'=>$bad];rejected($method,$gateway);}check(count($http_calls)===$before,'invalid tokens rejected before HTTP');
echo "OK: $checks Flow callback boundary assertions.\n";
