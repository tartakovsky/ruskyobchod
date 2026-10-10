<?php
require __DIR__.'/wp-fixture.php';
function live_check($ok,$name){if(!$ok)throw new Exception('FAIL '.$name);echo "PASS $name\n";}
$GLOBALS['requests']=[];$GLOBALS['remote_orders']=[];$GLOBALS['stock_row']=['stockQuantityStatus'=>47,'stockStatusVersionDate'=>'2026-09-11T08:11:00.123456Z'];
function is_wp_error($v){return $v instanceof WP_Error;}
function wp_json_encode($v){return json_encode($v);}
function rdf_access_context(){return ['cloud_id'=>'test-cloud','access_token'=>'test-token'];}
function rdf_branch_id($context){return 7;}
function rdf_payment_method_id($order){return $order->get_payment_method()==='cod'?900000009:900000019;}
function rdf_api_get($context,$path){$GLOBALS['requests'][]=['get',$path];return ['data'=>$GLOBALS['remote_orders']];}
function wp_remote_post($url,$args){$GLOBALS['requests'][]=['post',$url,json_decode($args['body'],true)];return ['body'=>'{"code":0}'];}
function wp_remote_retrieve_body($response){return $response['body'];}
function rdf_retry_stock_service(){return ['warehouse_id'=>'test-warehouse','service'=>new class{function getProductOnWarehouse($w,$p){return $GLOBALS['stock_row'];}}];}
$o=new WC_Order(1);$GLOBALS['orders'][1]=$o;$s=Gastronom\OrderReview\Review::command(gor_state($o),'approve',['version'=>1,'order_id'=>1,'cod'=>true,'dispatch_date'=>'2026-09-12'],'2026-09-10','1');
$plan=gor_plan($o,$s);
$GLOBALS['remote_orders']=[['id'=>42,'externalId'=>$plan['receipt']['external-id'],'_branchId'=>7,'currency'=>'EUR','paid'=>true,'flags'=>1<<29,'totalValueRounded'=>40,'completed'=>1789114200000,'orderItems'=>[['stockDeduct'=>true,'_productId'=>101,'quantity'=>1],['stockDeduct'=>true,'_productId'=>102,'quantity'=>1]]]];
$t=gor_live_transport($o);$receipt=$t->send($plan);Gastronom\OrderReview\Executor::validate($receipt,$plan);
live_check(str_contains($GLOBALS['requests'][0][1],'/clouds/test-cloud/branches/7/pos-actions'),'release adapter constructs branch-scoped endpoint');
live_check($GLOBALS['requests'][0][2]['payment-method-id']===900000009&&$GLOBALS['requests'][0][2]['external-id']===$plan['receipt']['external-id'],'release transport uses stable reference and configured payment mapping');
live_check($receipt['completed_at']===1789114200,'remote millisecond timestamps normalized');
$o->meta['_gor_execution']=['completed_at'=>$receipt['completed_at']];gor_reserve($o,$s['quote']);
gor_settle_live_stock($o,$s);
live_check($GLOBALS['stock'][1]===47.0&&$GLOBALS['stock'][2]===47.0,'release stock adapter sets authoritative stock without extra deduction');
live_check(gor_held_quantity(1)===0,'release stock adapter frees completed hold');
gor_settle_live_stock($o,$s);live_check($GLOBALS['stock'][1]===47.0,'repeated reconciliation does not subtract sold quantities');
$GLOBALS['stock_row']['stockStatusVersionDate']=1789114100000;
try{gor_settle_live_stock($o,$s);live_check(false,'stale stock');}catch(DomainException $e){live_check(true,'pre-receipt warehouse snapshot rejected');}
live_check(count(array_filter($GLOBALS['requests'],fn($r)=>$r[0]==='post'))===1,'stock synchronization never invokes sale endpoint');

$priorStock=$GLOBALS['stock'];
$GLOBALS['stock_row']=['stockQuantityStatus'=>1,'versionDate'=>1789114260000];
try{gor_settle_live_stock($o,$s);live_check(false,'missing stock version');}catch(DomainException $e){live_check(true,'generic product version cannot replace stock version');}
$GLOBALS['stock_row']=['stockQuantityStatus'=>1,'stockStatusVersiondate'=>1789114260000];
try{gor_settle_live_stock($o,$s);live_check(false,'wrong stock version casing');}catch(DomainException $e){live_check(true,'incorrect stock field casing rejected');}
live_check($GLOBALS['stock']===$priorStock,'invalid warehouse version leaves local stock untouched');
