import {spawnSync} from 'node:child_process';
import fs from 'node:fs';
import crypto from 'node:crypto';
import path from 'node:path';
const root=path.resolve(import.meta.dirname,'../..');
function sourceHashes(){
 const files=[];
 for(const dir of ['wordpress/wp-content/plugins/gastronom-order-review','tests/order-review']) {
   for(const f of fs.readdirSync(path.join(root,dir),{recursive:true})) {const rel=dir+'/'+f;if(fs.statSync(path.join(root,rel)).isFile())files.push(rel);}
 }
 for(const f of fs.readdirSync(import.meta.dirname)){if(/\.(mjs|json)$/.test(f))files.push('tools/order-approval-sandbox/'+f);}
 for(const f of ['rusky-dotypos-fiscalization.php','rusky-dotypos-stock-bridge.php','rusky-preorder-admin.php','rusky-preorder-notifications.php','rusky-weight-preorder.php','rusky-order-review-guard.php','rusky-shipping-notice-email.php','rusky-theme-chrome-language.php'])files.push('wordpress/wp-content/mu-plugins/'+f);
 for(const f of ['gastronom-stock-fix/gastronom-stock-fix.php','gls-shipping-for-woocommerce/includes/api/class-gls-shipping-api-data.php','packeta/src/Packetery/Module/Order/Builder.php','packeta/src/Packetery/Module/Order/PacketSubmitter.php'])files.push('wordpress/wp-content/plugins/'+f);
 return Object.fromEntries(files.sort().map(f=>[f,crypto.createHash('sha256').update(fs.readFileSync(path.join(root,f))).digest('hex')]));
}
const testedSources=sourceHashes();
const commands=[
 ['Domain','run-php.mjs'],
 ['WordPress adapter','run-php.mjs','tests/order-review/integration.php'],
 ['Customer consent and shortages','run-php.mjs','tests/order-review/customer-consent.php'],
 ['Admin and checkout language','run-php.mjs','tests/order-review/admin-language.php'],
 ['Fulfillment and reservations','run-php.mjs','tests/order-review/contracts.php'],
 ['Execution recovery','run-php.mjs','tests/order-review/execution.php'],
 ['Mail outbox','run-php.mjs','tests/order-review/outbox.php'],
 ['Release adapter with fake HTTP','run-php.mjs','tests/order-review/live-adapter.php'],
 ['Production gate','run-php.mjs','tests/order-review/production-gate.php'],
 ['Rollback ownership','run-php.mjs','tests/order-review/ownership-guard.php'],
 ['Legacy fiscal rounding','run-php.mjs','tests/order-review/legacy-fiscal-rounding.php'],
 ['New fulfillment rounding','run-php.mjs','tests/order-review/fulfillment-rounding.php'],
 ['Activation gates','test-gates.mjs'],
 ['PHP syntax','run-php.mjs','tests/order-review/lint.php'],
 ['Interface','test-ui.mjs'],
 ['WordPress posts and HPOS','test-wordpress.mjs'],
 ['Production WooCommerce posts and HPOS','test-wordpress.mjs','tools/order-approval-sandbox/runtime/live-10.7.0/woocommerce'],
 ['Current WordPress 6.9.10 and WooCommerce 10.7.0 posts and HPOS','test-wordpress.mjs','tools/order-approval-sandbox/runtime/live-10.7.0/woocommerce'],
 ['Customer public HTTP','test-customer-http.mjs'],
 ['Review flow','check-short-email.mjs'],
 ['Four email variants','check-four-mails.mjs'],
 ['Review demo build','build-short-email.mjs'],
 ['Offline build','build-offline.mjs'],
];
let failed=false;const report=[];
for(const [label,file,...args] of commands){
  const env=label.startsWith('Current WordPress') ? {...process.env,GOR_TEST_WORDPRESS_ROOT:path.join(import.meta.dirname,'runtime/wp-6.9.10/wordpress')} : process.env;
  const run=spawnSync(process.execPath,[path.join(import.meta.dirname,file),...args],{cwd:root,env,encoding:'utf8',maxBuffer:4*1024*1024});
  const output=(run.stdout||'')+(run.stderr||'');
  report.push({suite:label,exitCode:run.status,checks:(output.match(/^PASS /gm)||[]).length,output});
  console.log(`${run.status===0?'PASS':'FAIL'} ${label} (${report.at(-1).checks} checks)`);
  if(run.status!==0){failed=true;console.error(output);break;}
}
if(JSON.stringify(testedSources)!==JSON.stringify(sourceHashes())){failed=true;report.push({suite:'Source stability',exitCode:1,checks:0,output:'Files changed during verification; rerun required.'});}
fs.writeFileSync(path.join(root,'docs/order-review/verification.json'),JSON.stringify({date:new Date().toISOString(),isolated:true,productionChanged:false,sourceHashes:testedSources,report},null,2)+'\n');
if(failed)process.exitCode=1;
