<?php
/** File-only, resumable update of an already enabled workflow. No options/orders/config writes. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') exit(1);
$mode=$argv[1]??'inspect';
$release='/tmp/gor-review-20261010';
$root='/home/u595644545/domains/ruskyobchod.sk/public_html';
$evidence=$release.'/docs/order-review/update-20261010';
$manifest=json_decode(file_get_contents($evidence.'/manifest.json'),true,512,JSON_THROW_ON_ERROR);
$baseline=json_decode(file_get_contents($evidence.'/baseline.json'),true,512,JSON_THROW_ON_ERROR);
$_SERVER['HTTP_HOST']='gastronom-bratislava.sk';$_SERVER['HTTPS']='on';$_SERVER['REQUEST_METHOD']='GET';$_SERVER['REQUEST_URI']='/';
define('DISABLE_WP_CRON',true);require $root.'/wp-load.php';
add_filter('query',static function($sql){if(!preg_match('/^\s*(SELECT|SHOW|DESCRIBE|EXPLAIN)\b/i',$sql))throw new RuntimeException('Updater blocked SQL mutation');return $sql;},PHP_INT_MAX);
add_filter('pre_http_request',static function(){throw new RuntimeException('Updater blocked external HTTP');},PHP_INT_MAX,3);
add_filter('pre_wp_mail',static function(){throw new RuntimeException('Updater blocked mail');},PHP_INT_MAX,2);
if(!GASTRONOM_REVIEW_LIVE||gor_is_sandbox()||get_option('gor_review_cutoff')!==$manifest['cutoff'])throw new RuntimeException('Workflow ownership/configuration differs');
function target(string $path):string {global $root;if(!str_starts_with($path,'wordpress/wp-content/'))throw new RuntimeException('Invalid release path');return $root.'/'.substr($path,10);}
function source(string $path):string {global $release;return $release.'/'.$path;}
function sha(string $path):?string {return is_file($path)?hash_file('sha256',$path):null;}
function record(string $name,array $data):void {global $evidence;if(file_put_contents($evidence.'/'.$name,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n",LOCK_EX)===false)throw new RuntimeException('Evidence write failed');}
function expected_files(bool $allowCandidate):void {global $baseline,$manifest;$changes=array_column($manifest['files'],null,'path');foreach($baseline as $p=>$h){$actual=sha(target($p));if($actual!==$h&&(!$allowCandidate||$actual!==($changes[$p]['after']??null)))throw new RuntimeException('Live baseline mismatch: '.$p);}foreach($manifest['files'] as $f){if(sha(source($f['path']))!==$f['after'])throw new RuntimeException('Release changed: '.$f['path']);if($f['before']===null&&sha(target($f['path']))!==null&&(!$allowCandidate||sha(target($f['path']))!==$f['after']))throw new RuntimeException('New file already exists: '.$f['path']);}}
function command(array $cmd,array $io):void {$proc=proc_open($cmd,$io,$pipes);if(!is_resource($proc)||proc_close($proc)!==0)throw new RuntimeException('External backup/check failed; diagnostic stays private');}
function snapshot():array {global $root;$o=wc_get_order(11654);return ['config_sha256'=>sha($root.'/wp-config.php'),'active_plugins_sha256'=>hash('sha256',serialize(get_option('active_plugins'))),'cutoff'=>get_option('gor_review_cutoff'),'test_order_state_sha256'=>$o?hash('sha256',serialize($o->get_meta('_gor_state',true))):null,'test_order_source_sha256'=>$o?gor_source_fingerprint($o):null,'debug_bytes'=>is_file($root.'/wp-content/debug.log')?filesize($root.'/wp-content/debug.log'):null];}
$beforeFile=$evidence.'/backup-result.json';
if($mode==='backup'){
 expected_files(false);$backup='/home/u595644545/backups/order-review-ui-20261010-'.gmdate('His');
 if(!mkdir($backup,0700,true))throw new RuntimeException('Private backup creation failed');
 foreach($baseline as $p=>$h){$dest=$backup.'/files/'.substr($p,10);if(!is_dir(dirname($dest))&&!mkdir(dirname($dest),0700,true))throw new RuntimeException('Backup directory failed');if(!copy(target($p),$dest)||sha($dest)!==$h)throw new RuntimeException('File backup failed: '.$p);}
 copy($root.'/wp-config.php',$backup.'/wp-config.php');chmod($backup.'/wp-config.php',0600);
 copy($evidence.'/manifest.json',$backup.'/manifest.json');
 $credentials=$backup.'/dump-client.cnf';
 $quote=static fn($v)=>'"'.str_replace(["\\",'"',"\n","\r"],["\\\\",'\\"','\\n','\\r'],(string)$v).'"';
 file_put_contents($credentials,"[client]\nuser=".$quote(DB_USER)."\npassword=".$quote(DB_PASSWORD)."\nhost=".$quote(DB_HOST)."\n");chmod($credentials,0600);
 try{command(['/usr/bin/mysqldump','--defaults-extra-file='.$credentials,'--single-transaction','--quick','--skip-lock-tables','--no-tablespaces','--hex-blob',DB_NAME],[0=>['file','/dev/null','r'],1=>['file',$backup.'/database.sql','w'],2=>['file',$backup.'/dump-errors.txt','w']]);}finally{unlink($credentials);}
 if(filesize($backup.'/database.sql')<1000)throw new RuntimeException('Empty database backup');chmod($backup.'/database.sql',0600);
 command(['/usr/bin/gzip',$backup.'/database.sql'],[0=>['file','/dev/null','r'],1=>['file','/dev/null','w'],2=>['file',$backup.'/gzip-errors.txt','w']]);
 command(['/usr/bin/gzip','-t',$backup.'/database.sql.gz'],[0=>['file','/dev/null','r'],1=>['file','/dev/null','w'],2=>['file',$backup.'/gzip-errors.txt','a']]);
 $result=['at'=>gmdate('c'),'backup'=>$backup,'files'=>count($baseline),'database_bytes'=>filesize($backup.'/database.sql.gz'),'snapshot'=>snapshot()];record('backup-result.json',$result);file_put_contents($backup.'/backup-complete.json',json_encode($result));echo json_encode($result)."\n";exit;
}
if($mode==='deploy'){
 if(!is_file($beforeFile))throw new RuntimeException('Completed backup required');$backup=json_decode(file_get_contents($beforeFile),true);
 if(!is_file($backup['backup'].'/backup-complete.json')||sha($root.'/wp-config.php')!==$backup['snapshot']['config_sha256'])throw new RuntimeException('Backup/configuration changed');
 expected_files(true);
 // Dependency files first. Every publication is one same-directory atomic rename.
 foreach($manifest['files'] as $f){$dest=target($f['path']);if(sha($dest)===$f['after'])continue;$tmp=dirname($dest).'/.gor-ui-20261010-'.basename($dest);if(!copy(source($f['path']),$tmp)||sha($tmp)!==$f['after'])throw new RuntimeException('Stage copy failed');chmod($tmp,is_file($dest)?(fileperms($dest)&0777):0644);if(!rename($tmp,$dest))throw new RuntimeException('Atomic publication failed');if(function_exists('opcache_invalidate'))opcache_invalidate($dest,true);}
 record('deployed.json',['at'=>gmdate('c'),'files'=>count($manifest['files']),'cutoff_unchanged'=>true,'options_orders_config_writes'=>0]);echo "Published 9 files; no options, orders, config or business effects. Run verify in a new process.\n";exit;
}
if($mode==='verify'){
 foreach($manifest['files'] as $f){if(sha(target($f['path']))!==$f['after'])throw new RuntimeException('Installed hash mismatch: '.$f['path']);}
 $r=['at'=>gmdate('c'),'php'=>PHP_VERSION,'wp'=>get_bloginfo('version'),'woo'=>WC_VERSION,'files'=>count($manifest['files']),'snapshot'=>snapshot()];
 $b=json_decode(file_get_contents($beforeFile),true)['snapshot'];
 foreach(['config_sha256','active_plugins_sha256','cutoff','test_order_state_sha256','test_order_source_sha256'] as $key)if($r['snapshot'][$key]!==$b[$key])throw new RuntimeException('Unexpected baseline change: '.$key);
 if(!class_exists('Gastronom\\OrderReview\\Consent')||!function_exists('gor_approval_url'))throw new RuntimeException('New web adapter not loaded');
 $o=wc_get_order(11654);$view=gor_admin_state(gor_state($o));if($view['language']!=='sk')throw new RuntimeException('Customer language changed');
 foreach(['ru_RU','sk_SK'] as $locale){$lang=str_starts_with($locale,'ru')?'ru':'sk';$s=gor_state($o);$s['language']=$lang;$s['cause']='absence';$s['quote']=$s['original'];$s['quote']['lines']=array_slice($s['quote']['lines'],1);$html=Gastronom\OrderReview\Mail::changes(gor_mail_state($s));if(!str_contains($html,$lang==='ru'?'не хватает':'chýba'))throw new RuntimeException('Shortage text missing');}
 ob_start();wp_set_current_user(1);gor_render($o);$html=ob_get_clean();if(!str_contains($html,'uiLanguage')||!str_contains($html,'0.3.0')&& !str_contains(wp_scripts()->registered['gor']->ver,'0.3.0'))throw new RuntimeException('Admin rendering/version failed');
 $new=new WC_Order();$new->set_date_created(time());$new->set_payment_method('cod');
 if(apply_filters('woocommerce_can_reduce_order_stock',true,$new)||rdf_should_fiscalize($new))throw new RuntimeException('New-order ownership guard lost');
 $r['read_only_checks']='admin-render, RU/SK shortage, dependency-load, original-order/config invariants, old-fiscal/stock guards';record('verified.json',$r);echo json_encode($r)."\n";exit;
}
expected_files(false);echo "Preflight OK; no changes.\n";
