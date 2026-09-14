<?php
/** CLI-only migration of site URLs; does not touch orders or email addresses. */
if(PHP_SAPI!=='cli')exit;
$mode=$argv[1]??'inspect';
$_SERVER['HTTP_HOST']='ruskyobchod.sk';$_SERVER['REQUEST_METHOD']='GET';$_SERVER['REQUEST_URI']='/';define('DISABLE_WP_CRON',true);
$root='/home/u595644545/domains/ruskyobchod.sk/public_html';require $root.'/wp-load.php';
global $wpdb;
if($wpdb->prefix==='stg_'||wp_get_environment_type()!=='production')throw new RuntimeException('Wrong database');
function gmigrate_text($v){
 $search=['https://www.ruskyobchod.sk','http://www.ruskyobchod.sk','https://ruskyobchod.sk','http://ruskyobchod.sk','//www.ruskyobchod.sk','//ruskyobchod.sk'];
 $replace=['https://gastronom-bratislava.sk','https://gastronom-bratislava.sk','https://gastronom-bratislava.sk','https://gastronom-bratislava.sk','//gastronom-bratislava.sk','//gastronom-bratislava.sk'];
 $search=array_merge($search,array_map(static fn($x)=>str_replace('/','\\/',$x),$search));$replace=array_merge($replace,array_map(static fn($x)=>str_replace('/','\\/',$x),$replace));
 return str_replace($search,$replace,$v);
}
function gmigrate_value($v){if(is_string($v))return gmigrate_text($v);if(is_array($v)){ $a=[];foreach($v as$k=>$x)$a[is_string($k)?gmigrate_text($k):$k]=gmigrate_value($x);return $a;}if(is_object($v))throw new RuntimeException('Serialized object requires manual review');return $v;}
function gmigrate_stored($v){if(is_serialized($v)){ $x=unserialize(trim($v),['allowed_classes'=>false]);return serialize(gmigrate_value($x));}return gmigrate_text($v);}
$specs=[
 [$wpdb->options,'option_id',['option_value'],"option_name IN ('siteurl','home','widget_text','_elementor_local_google_fonts')"],
 [$wpdb->posts,'ID',['post_content','post_excerpt'],"post_type NOT IN ('shop_order','shop_order_refund','revision')"],
 [$wpdb->postmeta,'meta_id',['meta_value'],"post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type NOT IN ('shop_order','shop_order_refund','revision'))"],
 [$wpdb->termmeta,'meta_id',['meta_value'],'1=1'],
 [$wpdb->links,'link_id',['link_url','link_image','link_rss'],'1=1']
];
$changes=[];$counts=[];$skipped=[];
foreach($specs as[$table,$pk,$columns,$where])foreach($columns as$column){
 $rows=$wpdb->get_results("SELECT `$pk` AS id,`$column` AS value FROM `$table` WHERE ($where) AND `$column` LIKE '%ruskyobchod.sk%'",ARRAY_A);
 foreach($rows as$row){try{$next=gmigrate_stored($row['value']);}catch(Throwable$e){$skipped[]=['table'=>$table,'id'=>$row['id'],'column'=>$column,'reason'=>$e->getMessage(),'option_name'=>$table===$wpdb->options?$wpdb->get_var($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_id=%d",$row['id'])):null];continue;}
 if($next===$row['value'])continue;$changes[]=['table'=>$table,'pk'=>$pk,'id'=>(int)$row['id'],'column'=>$column,'before'=>$row['value'],'after'=>$next];$counts[$table.'.'.$column]=($counts[$table.'.'.$column]??0)+1;}
}
$report=['mode'=>$mode,'home'=>home_url('/'),'siteurl'=>site_url('/'),'prefix'=>$wpdb->prefix,'counts'=>$counts,'changes'=>count($changes),'changed_options'=>array_values(array_filter(array_map(static function($c)use($wpdb){return $c['table']===$wpdb->options?$wpdb->get_var($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_id=%d",$c['id'])):null;},$changes))),'skipped'=>$skipped,'front_page'=>(int)get_option('page_on_front'),'wcpay_active'=>class_exists('WC_Payments'),'external_cache'=>wp_using_ext_object_cache()];
if($mode==='inspect'){echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";exit;}
if($mode!=='apply'||$skipped)throw new RuntimeException('Migration requires clean inspection');
$backup='/home/u595644545/backups/format-domain-20260914-01';if(!is_dir($backup))throw new RuntimeException('Missing backup directory');
if(file_exists($backup.'/migration-applied.json'))throw new RuntimeException('Migration already applied');
foreach(['wp-config.php','.htaccess','wp-content/mu-plugins/rusky-front-performance-tuning.php']as$file){$dest=$backup.'/'.str_replace('/','__',$file);if(!file_exists($dest)&&!copy($root.'/'.$file,$dest))throw new RuntimeException('Backup failed');}
file_put_contents($backup.'/url-changes.json',json_encode($changes,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));chmod($backup.'/url-changes.json',0600);
$config=file_get_contents($root.'/wp-config.php');$newConfig=gmigrate_text($config);
if(substr_count($newConfig,"define('WP_HOME', 'https://gastronom-bratislava.sk')")!==1||substr_count($newConfig,"define('WP_SITEURL', 'https://gastronom-bratislava.sk')")!==1)throw new RuntimeException('Unexpected site constants');
$redirect=file_get_contents('/tmp/gastronom-domain-20260914.htaccess');$performance=file_get_contents('/tmp/gastronom-domain-20260914-performance.php');
if(!$redirect||!$performance)throw new RuntimeException('Missing candidate files');
$oldHt=file_get_contents($root.'/.htaccess');$intermediate=preg_replace('/# BEGIN Host redirect.*?# END Host redirect/s','',$oldHt,1);
$atomic=static function($path,$contents){$temp=$path.'.migration-tmp';if(file_put_contents($temp,$contents)===false)throw new RuntimeException('Write failed');chmod($temp,fileperms($path)&0777);if(!rename($temp,$path))throw new RuntimeException('Atomic swap failed');};
$oldPerformance=file_get_contents($root.'/wp-content/mu-plugins/rusky-front-performance-tuning.php');
foreach(array_unique(array_column($changes,'table'))as$table){$engine=$wpdb->get_var($wpdb->prepare('SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=%s',$table));if(strtoupper((string)$engine)!=='INNODB')throw new RuntimeException('Nontransactional table: '.$table);}
$wpdb->query('START TRANSACTION');
try{
 foreach($changes as$c){$sql=$wpdb->prepare("UPDATE `{$c['table']}` SET `{$c['column']}`=%s WHERE `{$c['pk']}`=%d AND BINARY `{$c['column']}`=BINARY %s",$c['after'],$c['id'],$c['before']);if($wpdb->query($sql)!==1)throw new RuntimeException('Concurrent edit or write failure at '.$c['table'].':'.$c['id']);}
 $atomic($root.'/.htaccess',$intermediate);
 $atomic($root.'/wp-config.php',$newConfig);
 $atomic($root.'/wp-content/mu-plugins/rusky-front-performance-tuning.php',$performance);
 $atomic($root.'/.htaccess',$redirect);
 if($wpdb->query('COMMIT')===false)throw new RuntimeException('Commit failed');
 wp_cache_flush();if(function_exists('wp_cache_clear_cache'))wp_cache_clear_cache();
 $report['applied_at']=gmdate('c');file_put_contents($backup.'/migration-applied.json',json_encode($report,JSON_PRETTY_PRINT));
}catch(Throwable$e){$wpdb->query('ROLLBACK');$atomic($root.'/.htaccess',$oldHt);$atomic($root.'/wp-config.php',$config);$atomic($root.'/wp-content/mu-plugins/rusky-front-performance-tuning.php',$oldPerformance);throw $e;}
echo json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)."\n";
