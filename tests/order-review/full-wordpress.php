<?php
require '/wordpress/wp-load.php';
require_once ABSPATH.'wp-admin/includes/plugin.php';
require_once ABSPATH.'wp-admin/includes/template.php';
require_once ABSPATH.'wp-admin/includes/class-wp-screen.php';
require_once ABSPATH.'wp-admin/includes/screen.php';
set_current_screen('shop_order');
wp_set_current_user(1);
$checks=0;
function assert_full($ok,$name){global $checks;if(!$ok)throw new Exception('FAIL '.$name);$checks++;echo "PASS $name\n";}
assert_full(function_exists('gor_api'),'plugin loaded on real WordPress');
echo 'Storage: '.(Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()?'HPOS':'posts')."\n";
update_option('woocommerce_currency','EUR');
update_option('woocommerce_weight_unit','kg');
$products=[];
foreach([['Test dumplings',6],['Test basket',30.5],['Test replacement',7.5]] as $i=>$data){
    $p=new WC_Product_Simple();$p->set_name($data[0]);$p->set_regular_price((string)$data[1]);$p->set_weight('');$p->set_manage_stock(true);$p->set_stock_quantity(10);$p->update_meta_data('_dotypos_id',(string)(90001+$i));$p->save();$products[]=$p;
}
function make_order(array $products,bool $paid=false){
    $o=wc_create_order();$o->set_date_created('2026-09-10 09:00:00');$o->set_currency('EUR');$o->set_payment_method($paid?'woocommerce_payments':'cod');$o->set_payment_method_title($paid?'Card':'COD');$o->set_billing_first_name('Sandbox');$o->set_billing_email('test@example.invalid');
    $o->add_product($products[0],1);$o->add_product($products[1],1);
    $shipping=new WC_Order_Item_Shipping();$shipping->set_method_id('packeta');$shipping->set_method_title('Packeta');$shipping->set_total('3.50');$o->add_item($shipping);$o->calculate_totals(false);
    if($paid)$o->set_date_paid('2026-09-10 09:01:00');$o->save();return $o;
}
function api_full($o,$action,array $input=[]){$fresh=wc_get_order($o->get_id());$s=gor_state($fresh);$r=new WP_REST_Request('POST','/gastronom-review/v1/orders/'.$o->get_id());$r->set_url_params(['id'=>$o->get_id()]);$r->set_header('Content-Type','application/json');$r->set_body(wp_json_encode(['action'=>$action,'version'=>$s['version']]+$input));return gor_api($r);}
$o=make_order($products);
assert_full(gor_managed($o),'real order assigned by cutoff');
assert_full(gor_state($o)['original']['shipping']===350,'real Woo shipping snapshot');
wc_reduce_stock_levels($o->get_id());
assert_full((float)wc_get_product($products[0]->get_id())->get_stock_quantity()===10.0,'automatic Woo stock reduction blocked');
$date=(new DateTimeImmutable('tomorrow',wp_timezone()))->format('Y-m-d');
$result=api_full($o,'approve',['dispatch_date'=>$date]);
if(is_wp_error($result))throw new Exception($result->get_error_message());
assert_full($result['state']['status']==='approved','real order approval metadata saved');
$fresh=wc_get_order($o->get_id());
assert_full(count($fresh->get_meta('_gor_sandbox_effects',true))===1,'one persisted sandbox effect');
assert_full((float)$fresh->get_total()===40.0&&count($fresh->get_items())===2,'real Woo order amount and items unchanged');
assert_full($fresh->get_meta('_gor_state',true)['mail']['confirmation']['transport']==='sandbox-capture','real order confirmation captured');
$paid=make_order($products,true);api_full($paid,'edit');
$r=api_full($paid,'save',['quote'=>['lines'=>[['product_id'=>$products[1]->get_id(),'quantity'=>'1','price'=>'30.50']]],'reason'=>'Test unavailable item']);
assert_full($r['state']['paid']===4000&&Gastronom\OrderReview\Review::balance($r['state'])===-600,'real prepaid removal refund delta');
api_full($paid,'proposal');api_full($paid,'agree',['consent_note'=>'Sandbox reply 42']);$r=api_full($paid,'approve',['dispatch_date'=>$date]);
assert_full($r['state']['status']==='approved','real prepaid correction approved');
assert_full((float)wc_get_order($paid->get_id())->get_total()===40.0,'original paid Woo total immutable');
$params=new WP_REST_Request('POST','/gastronom-review/v1/orders/'.$paid->get_id());$params->set_url_params(['id'=>$paid->get_id()]);
assert_full(gor_permission($params),'administrator capability on real order');wp_set_current_user(0);assert_full(!gor_permission($params),'anonymous permission denied');wp_set_current_user(1);
// Test the actual metabox registration using sentinel carrier boxes.
$_GET['post']=$paid->get_id();
add_meta_box('packetery_metabox','Packeta',static function(){},'shop_order','side');
add_meta_box('gls-shipping','GLS',static function(){},'shop_order','side');
do_action('add_meta_boxes');
global $wp_meta_boxes;
assert_full(empty($wp_meta_boxes['shop_order']['side']['default']['rusky-shipping-notice-email']),'duplicate shipping notice metabox removed');
assert_full(rsne_send_shipping_notice($paid,$date)===false,'legacy notice sender blocked for managed order');
assert_full(!empty($wp_meta_boxes['shop_order']['normal']['high']['gastronom-order-review']),'review metabox registered');
assert_full(!empty($wp_meta_boxes['shop_order']['side']['default']['packetery_metabox'])&&!empty($wp_meta_boxes['shop_order']['side']['default']['gls-shipping']),'carrier sidebar registrations preserved');
ob_start();gor_render(wc_get_order($paid->get_id()));$html=ob_get_clean();assert_full(strpos($html,'gor-config')!==false&&strpos($html,'Sandbox')!==false,'real metabox renders customer and config');
assert_full(is_wp_error(wp_remote_get('https://example.com')),'real WordPress outbound HTTP denied');
do_action('rest_api_init');
$search=new WP_REST_Request('GET','/gastronom-review/v1/products');$search->set_param('search','Test replacement');$response=rest_get_server()->dispatch($search);
assert_full($response->get_status()===200&&count($response->get_data())>0,'real REST product search returns catalog products');
echo "\n$checks full WordPress/WooCommerce checks passed.\n";

if (!Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
    $view = get_post($paid->get_id()); $persistedTitle = $view->post_title;
    global $post; $savedView = $post ?? null;
    do_action('edit_form_top', $view);
    assert_full(str_contains($post->post_title, (string)$paid->get_order_number()) && !str_contains($post->post_title, '&ndash;'), 'native order editor view has clean localized numbered title');
    assert_full(get_post($paid->get_id())->post_title === $persistedTitle, 'editor title projection leaves stored order title untouched');
    $post = $savedView;
}
