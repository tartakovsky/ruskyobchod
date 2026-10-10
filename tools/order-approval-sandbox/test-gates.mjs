import {createSandbox} from './runtime.mjs';
const {php}=await createSandbox();
for(const [name,env,flags] of [
 ['unconfigured production','production',{}],
 ['sandbox flag cannot enable production','production',{GASTRONOM_REVIEW_SANDBOX:true}],
 ['live flag cannot enable staging','staging',{GASTRONOM_REVIEW_LIVE:true}],
 ['live flag cannot enable local','local',{GASTRONOM_REVIEW_LIVE:true}],
 ['conflicting flags fail closed','production',{GASTRONOM_REVIEW_LIVE:true,GASTRONOM_REVIEW_SANDBOX:true}],
 ['missing cutoff fails closed','local',{GASTRONOM_REVIEW_SANDBOX:true}],
]) {
 const defs=Object.entries(flags).map(([k,v])=>`define('${k}',${v?'true':'false'});`).join('');
 const cutoff=name==='missing cutoff fails closed'?'':"define('GASTRONOM_REVIEW_FROM_UTC','2026-09-10 00:00:00');";
 const result=await php.run({code:`<?php define('ABSPATH','/sandbox/');${defs}${cutoff}function wp_get_environment_type(){return '${env}';}require '/app/wordpress/wp-content/plugins/gastronom-order-review/gastronom-order-review.php';if(function_exists('gor_api'))throw new Exception('Gate unexpectedly opened');echo 'PASS ${name}';`});
 console.log(result.text);if(result.errors||result.exitCode!==0)throw Error(result.errors||'Gate failed');
}
