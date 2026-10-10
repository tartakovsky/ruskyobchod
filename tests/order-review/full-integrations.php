<?php
// Continues full-wordpress.php on the same real Woo database.
$holdOrder=make_order($products);
$p=wc_get_product($products[0]->get_id());wc_update_product_stock($p,1,'set');
do_action('woocommerce_checkout_order_created',$holdOrder);
assert_full(gor_held_quantity($p->get_id())===1,'checkout creates durable availability hold');
assert_full((float)wc_get_product($p->get_id())->get_stock_quantity()===1.0,'hold leaves physical Woo quantity untouched');
assert_full((float)wc_get_held_stock_quantity($p)===1.0,'Woo reserved-stock SQL includes new ledger');
assert_full((float)wc_get_held_stock_quantity($p,$holdOrder->get_id())===0.0,'checkout excludes its own hold');
$competitor=make_order($products);
try { gor_reserve($competitor,gor_state($competitor)['quote']); assert_full(false,'final piece contention'); } catch(DomainException $e) { assert_full(true,'competing checkout cannot reserve final piece'); }
$holdOrder->update_status('cancelled');
assert_full(gor_held_quantity($p->get_id())===0,'cancellation releases held availability');
wc_update_product_stock($p,10,'set');
$lost=make_order($products);update_option('gor_sandbox_lost_response',true);
$r=api_full($lost,'approve',['dispatch_date'=>$date]);
assert_full($r['state']['status']==='error','real database retains uncertain POS result');
assert_full(gor_held_quantity($p->get_id())===1,'uncertain execution retains stock hold');
update_option('gor_sandbox_lost_response',false);$r=api_full($lost,'retry');
assert_full($r['state']['status']==='approved'&&count(wc_get_order($lost->get_id())->get_meta('_gor_sandbox_effects',true))===1,'lost POS response reconciles without duplicate sale');
assert_full(gor_held_quantity($p->get_id())===0&&(float)wc_get_product($p->get_id())->get_stock_quantity()===9.0,'verified execution settles stock once and releases hold');
// Actual GLS payload builders, including bulk. Only account/address providers are test doubles.
foreach(['GLS_SHIPPING_METHOD_PARCEL_LOCKER_ID','GLS_SHIPPING_METHOD_PARCEL_SHOP_ID','GLS_SHIPPING_METHOD_PARCEL_LOCKER_ZONES_ID','GLS_SHIPPING_METHOD_PARCEL_SHOP_ZONES_ID'] as $constant)define($constant,strtolower($constant));
class GLS_Shipping_Account_Helper { static function get_account_setting($key){return ['client_id'=>123,'client_reference_format'=>'{{order_id}}','content'=>'{{order_total}}','country'=>'SK','insurance_service'=>'no'][$key]??'';} }
class GLS_Shipping_Sender_Address_Helper {static function get_default_sender_address(){return [];}static function format_for_api_pickup(...$args){return ['Name'=>'Test sender'];}}
require_once '/wordpress/wp-content/plugins/gls-shipping-for-woocommerce/includes/api/class-gls-shipping-api-data.php';
$gls=new GLS_Shipping_API_Data($lost->get_id());$parcel=$gls->generate_post_fields()['ParcelList'][0];
assert_full((float)$parcel['CODAmount']===40.0,'real GLS single builder uses approved COD');
$batch=(new GLS_Shipping_API_Data([$lost->get_id(),$paid->get_id()]))->generate_post_fields_multi()['ParcelList'];
assert_full(count($batch)===2&&!isset($batch[1]['CODAmount'])&&(float)$batch[1]['Content']===34.0,'real GLS batch uses corrected prepaid value and no COD');
try{(new GLS_Shipping_API_Data($competitor->get_id()))->generate_post_fields();assert_full(false,'unapproved GLS');}catch(DomainException $e){assert_full(true,'real GLS single blocks unapproved shipment');}
try{(new GLS_Shipping_API_Data([$lost->get_id(),$competitor->get_id()]))->generate_post_fields_multi();assert_full(false,'unapproved GLS batch');}catch(DomainException $e){assert_full(true,'real GLS batch blocks mixed approved and unapproved export');}
// Real Packeta entity and submitter. Isolate only validator and mapper dependencies.
require_once '/wordpress/wp-content/plugins/packeta/src/Packetery/Core/CoreHelper.php';
require_once '/wordpress/wp-content/plugins/packeta/src/Packetery/Core/Entity/Order.php';
require_once '/wordpress/wp-content/plugins/packeta/src/Packetery/Core/Api/InvalidRequestException.php';
require_once '/wordpress/wp-content/plugins/packeta/src/Packetery/Module/Order/PacketSubmitter.php';
$entity=(new ReflectionClass(Packetery\Core\Entity\Order::class))->newInstanceWithoutConstructor();
$number=new ReflectionProperty($entity,'number');$number->setValue($entity,(string)$paid->get_id());$entity->setCustomNumber('PUBLIC-999');
$entity->setCalculatedWeight(0.125);
$entity->setManualValue(40.0);$entity->setManualCod(40.0);$entity->setWeight(2.0);
gor_packeta_display($entity,wc_get_order($paid->get_id()));
assert_full($entity->getFinalValue()===34.0&&$entity->getFinalCod()===null,'real Packeta entity drops stale manual value and COD');
assert_full($entity->getFinalWeight()===2.0,'Packeta manual packaging weight preserved');
$entity->setWeight(null);
gor_packeta_display($entity,wc_get_order($paid->get_id()));
assert_full($entity->getFinalWeight()===0.125,'Packeta calculated/default weight preserved without manual value');
$entity->setWeight(2.0);
$low=gor_packeta_payload(['value'=>40,'cod'=>40,'weight'=>0.1],$entity);
assert_full($low['weight']===0.1,'Packeta low manual payload weight preserved exactly');
$absent=gor_packeta_payload(['value'=>40,'cod'=>40],$entity);
assert_full(!array_key_exists('weight',$absent),'missing Packeta weight left to carrier validation');
$submit=(new ReflectionClass(Packetery\Module\Order\PacketSubmitter::class))->newInstanceWithoutConstructor();
(new ReflectionProperty($submit,'orderValidator'))->setValue($submit,new class{function validate($o){return [];}});
(new ReflectionProperty($submit,'createPacketMapper'))->setValue($submit,new class{function fromOrderToArray($o){return ['number'=>$o->getCustomNumberOrNumber(),'value'=>40,'cod'=>40,'weight'=>2,'deliverOn'=>'2026-09-20'];}});
$prepare=new ReflectionMethod($submit,'preparePacketData');$packet=$prepare->invoke($submit,$entity);
assert_full((float)$packet['value']===34.0&&(float)$packet['cod']===0.0&&$packet['number']==='PUBLIC-999','real Packeta submitter resolves internal ID despite custom public number');
assert_full($packet['deliverOn']==='2026-09-20'&&$packet['weight']===2,'Packeta preserves delivery date and packaging weight');
$number->setValue($entity,(string)$competitor->get_id());
try{$prepare->invoke($submit,$entity);assert_full(false,'Packeta unapproved');}catch(Packetery\Core\Api\InvalidRequestException $e){assert_full(true,'Packeta unapproved export uses plugin handled exception');}
// Actual Woo template resolver, HTML and plain text.
$ack=wc_get_template_html('emails/customer-processing-order.php',['order'=>$competitor,'email_heading'=>'Received','sent_to_admin'=>false,'plain_text'=>false,'email'=>WC()->mailer()->get_emails()['WC_Email_Customer_Processing_Order']]);
assert_full(strpos($ack,'overíme dostupnosť')!==false&&strpos($ack,'Test dumplings')!==false,'Woo first email renders availability acknowledgement');
$ack=wc_get_template_html('emails/plain/customer-processing-order.php',['order'=>$competitor,'email_heading'=>'Received','sent_to_admin'=>false,'plain_text'=>true,'email'=>null]);
assert_full(strpos($ack,'Pôvodná suma:')!==false&&strpos($ack,'<table')===false,'plain-text acknowledgement has explicit original total');
echo "Extended full integration checks completed.\n";
