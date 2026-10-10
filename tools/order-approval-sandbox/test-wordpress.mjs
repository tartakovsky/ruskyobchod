import {PHP,loadPHPRuntime} from '@php-wasm/universal';
import {getPHPLoaderModule} from '@php-wasm/node-8-3';
import {createNodeFsMountHandler} from '@php-wasm/node';
import fs from 'node:fs';
import path from 'node:path';
import {root} from './runtime.mjs';
const runtime=path.join(root,'tools/order-approval-sandbox/runtime');
const wp=process.env.GOR_TEST_WORDPRESS_ROOT ? path.resolve(process.env.GOR_TEST_WORDPRESS_ROOT) : path.join(runtime,'wordpress');
if(!fs.existsSync(path.join(wp,'wp-load.php')))throw Error('Extract the official WordPress and SQLite packages into runtime first.');
fs.mkdirSync(path.join(wp,'wp-content/plugins'),{recursive:true});
fs.cpSync(path.join(runtime,'sqlite-database-integration'),path.join(wp,'wp-content/plugins/sqlite-database-integration'),{recursive:true});
fs.copyFileSync(path.join(runtime,'sqlite-database-integration/db.copy'),path.join(wp,'wp-content/db.php'));
fs.mkdirSync(path.join(wp,'wp-content/mu-plugins'),{recursive:true});
fs.writeFileSync(path.join(wp,'wp-content/mu-plugins/00-isolation.php'),`<?php
add_filter('pre_wp_mail',static function(){return true;},PHP_INT_MAX,2);
add_filter('pre_http_request',static function(){return new WP_Error('isolated','Network disabled');},PHP_INT_MAX,3);
class Dotypos { public static $keys=['product'=>['field-id'=>'_dotypos_id']]; }
`);
fs.copyFileSync(path.join(root,'wordpress/wp-content/mu-plugins/rusky-order-review-guard.php'),path.join(wp,'wp-content/mu-plugins/rusky-order-review-guard.php'));
fs.copyFileSync(path.join(root,'wordpress/wp-content/mu-plugins/rusky-shipping-notice-email.php'),path.join(wp,'wp-content/mu-plugins/rusky-shipping-notice-email.php'));
fs.writeFileSync(path.join(wp,'wp-config.php'),`<?php
define('DB_NAME','sandbox');define('DB_USER','sandbox');define('DB_PASSWORD','');define('DB_HOST','localhost');
define('DB_CHARSET','utf8');define('DB_COLLATE','');define('DB_ENGINE','sqlite');
define('WP_HOME','http://sandbox.invalid');define('WP_SITEURL','http://sandbox.invalid');
define('WP_ENVIRONMENT_TYPE','local');define('GASTRONOM_REVIEW_SANDBOX',true);
define('GASTRONOM_REVIEW_FROM_UTC','2026-09-10 00:00:00');
define('DISABLE_WP_CRON',true);define('WP_HTTP_BLOCK_EXTERNAL',true);define('AUTOMATIC_UPDATER_DISABLED',true);
define('WP_DEBUG',true);define('WP_DEBUG_DISPLAY',false);define('WP_DEBUG_LOG',true);
define('AUTH_KEY','isolated-only-not-a-production-secret');define('SECURE_AUTH_KEY',AUTH_KEY);define('LOGGED_IN_KEY',AUTH_KEY);define('NONCE_KEY',AUTH_KEY);
$table_prefix='sandbox_';if(!defined('ABSPATH'))define('ABSPATH',__DIR__.'/');require_once ABSPATH.'wp-settings.php';
`);
const php=new PHP(await loadPHPRuntime(await getPHPLoaderModule(),{}));
php.mkdir('/wordpress');await php.mount('/wordpress',createNodeFsMountHandler(wp));
for(const plugin of ['woocommerce','gastronom-order-review','packeta','gls-shipping-for-woocommerce']){
  php.mkdir('/wordpress/wp-content/plugins/'+plugin);await php.mount('/wordpress/wp-content/plugins/'+plugin,createNodeFsMountHandler(plugin==='woocommerce' && process.argv[2] ? path.resolve(root,process.argv[2]) : path.join(root,'wordpress/wp-content/plugins',plugin)));
}
let storage='posts';
const runId=Date.now();
async function run(code){const response=await php.run({code:'<?php define("DB_FILE","test-'+runId+'-'+storage+'.sqlite"); $_SERVER["HTTP_HOST"]="sandbox.invalid";$_SERVER["REQUEST_METHOD"]="GET";$_SERVER["SERVER_PROTOCOL"]="HTTP/1.1";'+code});process.stdout.write(response.text);if(response.errors)process.stderr.write(response.errors);if(response.exitCode!==0||/Fatal error|Uncaught |FAIL /.test(response.text+response.errors))throw Error('WordPress verification failed');return response;}
const tests=fs.readFileSync(path.join(root,'tests/order-review/full-wordpress.php'),'utf8').replace(/^<\?php\s*/,'')+fs.readFileSync(path.join(root,'tests/order-review/full-integrations.php'),'utf8').replace(/^<\?php\s*/,'');
for (storage of ['posts','hpos']) {
  await run(`define('WP_INSTALLING',true);require '/wordpress/wp-load.php';require_once ABSPATH.'wp-admin/includes/upgrade.php';if(!is_blog_installed()){wp_install('Gastronom isolated','sandbox_admin','sandbox@example.invalid',false,'','sandbox-local-test-only');}if(!class_exists('WooCommerce')){require '/wordpress/wp-content/plugins/woocommerce/woocommerce.php';}WC_Install::install();update_option('active_plugins',['woocommerce/woocommerce.php','gastronom-order-review/gastronom-order-review.php']);update_option('woocommerce_custom_orders_table_enabled','${storage==='hpos'?'yes':'no'}');echo "Fresh isolated WordPress database installed.\\n";`);
  await run(`require '/wordpress/wp-load.php';echo 'WordPress '.get_bloginfo('version').' / WooCommerce '.WC_VERSION."\\n";`);
  await run(tests);
}
