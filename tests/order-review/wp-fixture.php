<?php
// Deliberately small WordPress/WooCommerce test doubles. No database, network or mail transport.
declare(strict_types=1);
define('ABSPATH','/sandbox/');
define('DAY_IN_SECONDS',86400);
function home_url($path=''){return 'http://sandbox.invalid'.$path;}
function add_query_arg($args,$url){return $url.'?'.http_build_query($args);}
function wp_salt($scheme){return 'isolated-test-secret';}
function get_user_locale(){return $GLOBALS['admin_locale']??'ru_RU';}

define('GASTRONOM_REVIEW_SANDBOX',true);
define('GASTRONOM_REVIEW_FROM_UTC','2026-09-10 00:00:00');
$GLOBALS['hooks']=[];$GLOBALS['options']=['admin_email'=>'shop@example.invalid'];$GLOBALS['allowed']=true;$GLOBALS['orders']=[];
function add_action($name,$fn,$priority=10,$args=1){$GLOBALS['hooks'][$name][$priority][]=$fn;}
function add_filter($name,$fn,$priority=10,$args=1){add_action($name,$fn,$priority,$args);}
function apply_filters($name,$value,...$args){$hooks=$GLOBALS['hooks'][$name]??[];ksort($hooks);foreach($hooks as $list)foreach($list as $fn)$value=$fn($value,...$args);return $value;}
function wp_get_environment_type(){return 'local';}
function wp_generate_uuid4(){return 'test-'.(++$GLOBALS['uuid']);}
$GLOBALS['uuid']=0;
function wc_get_order($id){return $GLOBALS['orders'][(int)$id]??null;}
function wc_get_product($id){return in_array((int)$id,[1,2,3,4],true)?new WC_Product((int)$id):false;}
function absint($id){return abs((int)$id);}
function wp_strip_all_tags($s,$b=false){return strip_tags($s);}
function current_user_can(...$args){return $GLOBALS['allowed'];}
function get_current_user_id(){return 1;}
function current_time($format){return $format==='mysql'?'2026-09-10 10:00:00':'2026-09-10';}
function get_option($key,$default=false){return $GLOBALS['options'][$key]??$default;}
function add_option($key,$value,...$args){if(array_key_exists($key,$GLOBALS['options']))return false;$GLOBALS['options'][$key]=$value;return true;}
function wp_cache_delete(...$args){}
function update_option($key,$value,...$args){$GLOBALS['options'][$key]=$value;return true;}
function wc_get_held_stock_quantity($p,$exclude=0){return gor_held_quantity($p->get_id(),$exclude);}
function wc_update_product_stock($p,$qty,$operation='set'){$GLOBALS['stock'][$p->get_id()]=$qty;return $qty;}
function delete_option($key){unset($GLOBALS['options'][$key]);}
function sanitize_email($s){return $s;}
function wc_get_weight($w,$unit){return $w*1000;}
function gastronom_order_lang($order){return $order->lang;}
function gastronom_localize_title($name,$lang){if($lang!=='sk')return $name;return ['Пельмени «Домашние», 500 г'=>'Domáce pelmene, 500 g','Продукты к заказу'=>'Ostatné potraviny','Пельмени «Сибирские», 500 г'=>'Sibírske pelmene, 500 g','Сыр, на вес'=>'Vážený syr'][$name]??$name;}
function gastronom_localize_order_label($name,$lang){if($lang!=='sk')return $name;return ['Оплата при получении'=>'Dobierka','Предоплата картой'=>'Platba kartou','Packeta — пункт выдачи'=>'Packeta — výdajné miesto'][$name]??$name;}
class Dotypos {public static $keys=['product'=>['field-id'=>'_dotypos_id']];}
class WP_Error {public $code,$message,$data;function __construct($c,$m,$d=[]){$this->code=$c;$this->message=$m;$this->data=$d;}}
class WP_Post {public $ID;}
class WP_REST_Request implements ArrayAccess {
    private $input;
    function __construct(array $input){$this->input=$input;}
    function get_json_params(){return $this->input;}
    function offsetExists(mixed $offset):bool{return isset($this->input[$offset]);}
    function offsetGet(mixed $offset):mixed{return $this->input[$offset]??null;}
    function offsetSet(mixed $offset,mixed $value):void{$this->input[$offset]=$value;}
    function offsetUnset(mixed $offset):void{unset($this->input[$offset]);}
}
class WC_Product {
    private $id;function __construct($id){$this->id=$id;}
    function get_id(){return $this->id;}
    function get_name(){return [1=>'Пельмени «Домашние», 500 г',2=>'Продукты к заказу',3=>'Пельмени «Сибирские», 500 г',4=>'Сыр, на вес'][$this->id];}
    function get_meta($key,$single=true){if(isset($GLOBALS['product_meta'][$this->id][$key]))return $GLOBALS['product_meta'][$this->id][$key];if($key==='_gastronom_weight_preorder_max_kg')return .5;if($key==='_dotypos_id')return 100+$this->id;if($key==='_gastronom_weight_preorder')return $this->id===4?'yes':'no';return '';}
    function update_meta_data($key,$value){$GLOBALS['product_meta'][$this->id][$key]=$value;}
    function save_meta_data(){}
    function managing_stock(){return true;}
    function get_stock_managed_by_id(){return $this->id;}
    function get_stock_quantity($context='view'){return $GLOBALS['stock'][$this->id]??100;}
    function get_weight(){return .5;}
}
class WC_Order_Item_Product {
    private $id,$price,$qty;
    function __construct($id,$price,$qty=1){$this->id=$id;$this->price=$price;$this->qty=$qty;}
    function get_id(){return $this->id;}
    function get_product(){return new WC_Product($this->id);}
    function get_name(){return $this->get_product()->get_name();}
    function get_total(){return $this->price;}
    function get_total_tax(){return 0;}
    function get_quantity(){return $this->qty;}
    function get_meta($key,$single=true){if($this->id===4 && $key==='_gastronom_weight_min_kg')return .3;if($this->id===4 && $key==='_gastronom_weight_max_kg')return .5;return $key==='_gastronom_weight_preorder'&&$this->id===4?'yes':($key==='_gastronom_price_per_kg'?20:'');}
}
class TestShipping {function get_method_id(){return 'packeta';}}
class WC_Order {
    public $id,$meta=[],$paid=false,$method='cod',$lang='ru',$items=[],$created='2026-09-10 08:00:00';
    function __construct($id){$this->id=$id;$this->items=[new WC_Order_Item_Product(1,6),new WC_Order_Item_Product(2,30.5)];}
    function get_id(){return $this->id;}
    function get_meta($key,$single=true){return $this->meta[$key]??'';}
    function update_meta_data($key,$value){$this->meta[$key]=$value;}
    function save_meta_data(){}
    function read_meta_data($force=false){}
    function get_date_created(){return new DateTimeImmutable($this->created,new DateTimeZone('UTC'));}
    function get_date_paid(){return $this->paid?new DateTimeImmutable('2026-09-10'):null;}
    function get_items(){return $this->items;}
    function get_shipping_total(){return 3.5;}
    function get_shipping_tax(){return 0;}
    function get_total($context='view'){return 40;}
    function get_currency(){return 'EUR';}
    function get_status(){return 'processing';}
    function is_paid(){return $this->paid;}
    function get_shipping_methods(){return [new TestShipping()];}
    function get_order_number(){return (string)$this->id;}
    function get_billing_first_name(){return 'Анна';}
    function get_billing_last_name(){return 'Тестовая';}
    function get_billing_email(){return 'anna@example.invalid';}
    function get_billing_phone(){return '+421 900 000 000';}
    function get_formatted_shipping_address(){return 'Тестовый пункт выдачи Packeta, Bratislava';}
    function get_formatted_billing_address(){return '';}
    function get_payment_method(){return $this->method;}
    function get_payment_method_title(){return $this->method==='cod'?'Оплата при получении':'Предоплата картой';}
    function get_shipping_method(){return 'Packeta — пункт выдачи';}
}
require __DIR__.'/../../wordpress/wp-content/plugins/gastronom-order-review/gastronom-order-review.php';
