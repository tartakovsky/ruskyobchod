import {PHP,loadPHPRuntime} from '@php-wasm/universal';
import {getPHPLoaderModule} from '@php-wasm/node-8-3';
import {createNodeFsMountHandler} from '@php-wasm/node';
import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';
import {root} from './runtime.mjs';
const runtime=path.join(root,'tools/order-approval-sandbox/runtime');
const wp=path.join(runtime,'wp-6.9.10/wordpress');
const php=new PHP(await loadPHPRuntime(await getPHPLoaderModule(),{}));
php.mkdir('/wordpress');await php.mount('/wordpress',createNodeFsMountHandler(wp));
for(const plugin of ['woocommerce','gastronom-order-review']){php.mkdir('/wordpress/wp-content/plugins/'+plugin);await php.mount('/wordpress/wp-content/plugins/'+plugin,createNodeFsMountHandler(path.join(root,plugin==='woocommerce'?'tools/order-approval-sandbox/runtime/live-10.7.0/woocommerce':'wordpress/wp-content/plugins/gastronom-order-review')));}
const db='http-consent-'+Date.now()+'.sqlite';
const bootstrap=`define('DB_FILE','${db}');$_SERVER['HTTP_HOST']='sandbox.invalid';$_SERVER['REQUEST_METHOD']='GET';$_SERVER['SERVER_PROTOCOL']='HTTP/1.1';require '/wordpress/wp-load.php';`;
async function run(code,boot=bootstrap){const r=await php.run({code:'<?php '+boot+code});if(r.errors||r.exitCode||/Fatal error|FAIL /.test(r.text))throw Error(r.text+r.errors);return r;}
const ok=(yes,name)=>{assert.ok(yes,name);console.log('PASS '+name);};
await run(`require_once ABSPATH.'wp-admin/includes/upgrade.php';wp_install('Sandbox','admin','shop@example.invalid',false,'','test-only');require '/wordpress/wp-content/plugins/woocommerce/woocommerce.php';WC_Install::install();update_option('active_plugins',['woocommerce/woocommerce.php','gastronom-order-review/gastronom-order-review.php']);update_option('woocommerce_currency','EUR');update_option('woocommerce_custom_orders_table_enabled','no');`,bootstrap.replace("require '/wordpress/wp-load.php';","define('WP_INSTALLING',true);require '/wordpress/wp-load.php';"));
const setup=await run(`$p=new WC_Product_Simple();$p->set_name('Test product');$p->set_regular_price('9');$p->set_manage_stock(true);$p->set_stock_quantity(10);$p->update_meta_data('_dotypos_id','123');$p->save();$o=wc_create_order();$o->set_date_created('2026-10-10 09:00:00');$o->set_currency('EUR');$o->set_status('processing');$o->set_payment_method('cod');$o->set_billing_email('customer@example.invalid');$o->update_meta_data('_gastronom_lang','ru');$o->add_product($p,3);$o->calculate_totals(false);$o->save();function cmd($o,$a,$data=[]){$o=wc_get_order($o->get_id());$r=new WP_REST_Request('POST');$r->set_param('id',$o->get_id());$r->set_header('Content-Type','application/json');$r->set_body(wp_json_encode(['action'=>$a,'version'=>gor_state($o)['version']]+$data));$result=gor_api($r);if(is_wp_error($result))throw new Exception($result->get_error_message());return $result;}cmd($o,'edit');cmd($o,'save',['quote'=>['lines'=>[['product_id'=>$p->get_id(),'quantity'=>'1','price'=>'9']]],'reason'=>'Shortage']);$s=cmd($o,'proposal')['state'];$expires=$s['mail']['proposal-1']['approval_expires'];echo wp_json_encode(['id'=>$o->get_id(),'product'=>$p->get_id(),'expires'=>$expires,'token'=>Gastronom\\OrderReview\\Consent::token($o->get_id(),$s,$o->get_billing_email(),$expires,wp_salt('auth'))]);`);
const data=JSON.parse(setup.text);
const credentials={gor_review:data.id,expires:data.expires,token:data.token};
const query=JSON.stringify(credentials);
async function request(method='GET',post={},args=query){return run(`$_GET=json_decode('${args}',true);$_POST=json_decode('${JSON.stringify(post)}',true);$_SERVER['REQUEST_METHOD']='${method}';do_action('template_redirect');`);}
async function read(){return JSON.parse((await run(`$o=wc_get_order(${data.id});echo wp_json_encode(['state'=>gor_state($o),'total'=>$o->get_total(),'stock'=>wc_get_product(${data.product})->get_stock_quantity(),'effects'=>$o->get_meta('_gor_sandbox_effects',true),'original'=>$o->get_items() ? count($o->get_items()) : 0]);`)).text);}
const before=await read();let response=await request();
ok(response.text.includes('Согласен с изменениями')&&response.text.includes('не хватает 2 шт.'),'real WordPress GET renders explicit acceptance and exact shortage');
ok(JSON.stringify(await read())===JSON.stringify(before),'email-scanner GET does not change order, agreement or stock');
let headers=JSON.stringify(response.headers).toLowerCase();ok(headers.includes('no-store')&&headers.includes('no-referrer')&&headers.includes('noindex'),'customer page has private cache and referral headers');
response=await request('GET',{},JSON.stringify({...credentials,token:'0'.repeat(64)}));ok(response.text.includes('Ссылка недействительна')&&JSON.stringify(await read())===JSON.stringify(before),'forged HTTP link cannot read proposal or modify order');
response=await request('POST',{decision:'agree'});const accepted=await read();
ok(response.text.includes('Ваше согласие записано')&&accepted.state.status==='agreed'&&accepted.state.consent_note==='customer-link','real anonymous POST records customer agreement');
ok(accepted.stock===before.stock&&accepted.total===before.total&&accepted.effects===before.effects&&accepted.original===before.original,'public POST leaves POS, stock and Woo source unchanged');
await request('POST',{decision:'agree'});ok(JSON.stringify(await read())===JSON.stringify(accepted),'repeated real POST is idempotent');
await run(`$o=wc_get_order(${data.id});$s=gor_state($o);$s=Gastronom\\OrderReview\\Review::command($s,'edit',['version'=>$s['version']],current_time('Y-m-d'),'admin');gor_save($o,$s);`);
const edited=await read();response=await request('POST',{decision:'agree'});ok(response.text.includes('Ссылка недействительна')&&JSON.stringify(await read())===JSON.stringify(edited),'old proposal HTTP POST rejected after administrator edits');
