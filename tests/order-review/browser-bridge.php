<?php
require __DIR__.'/wp-fixture.php';
$request=json_decode(file_get_contents('/request.json'),true);
$scenario=in_array($request['scenario']??'', ['cod','prepaid','weight','slovak'],true)?$request['scenario']:'cod';
$file='/state-'.$scenario.'.json';
$o=new WC_Order(12001);$o->meta['_gastronom_lang']='ru';
if($scenario==='prepaid'){$o->paid=true;$o->method='woocommerce_payments';}
if($scenario==='weight'){$o->items=[new WC_Order_Item_Product(4,6),new WC_Order_Item_Product(2,30.5)];}
if($scenario==='slovak'){$o->lang='sk';$o->meta['_gastronom_lang']='sk';}
if(($request['action']??'')==='reset'&&file_exists($file))unlink($file);
if(file_exists($file)){$persisted=json_decode(file_get_contents($file),true);$o->meta=$persisted['meta'];$GLOBALS['options']=$persisted['options'];}
$GLOBALS['orders'][$o->id]=$o;
$action=$request['action']??'read';
if($action==='fault'){$GLOBALS['options']['gor_sandbox_fail_effect']=!empty($request['effect']);$GLOBALS['options']['gor_sandbox_fail_mail']=!empty($request['mail']);}
if(in_array($action,['read','reset','fault'],true)){$result=['state'=>gor_state($o),'customer'=>gor_customer($o),'today'=>'2026-09-10','sandbox'=>true];}
else{$request['id']=$o->id;$result=gor_api(new WP_REST_Request($request));}
file_put_contents($file,json_encode(['meta'=>$o->meta,'options'=>$GLOBALS['options']]));
if($result instanceof WP_Error){echo json_encode(['error'=>$result->message,'code'=>$result->code]);}
else{echo json_encode($result,JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);}
