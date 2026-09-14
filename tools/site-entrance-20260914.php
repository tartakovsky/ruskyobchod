<?php
if(PHP_SAPI!=='cli')exit;
$_SERVER['HTTP_HOST']='gastronom-bratislava.sk';$_SERVER['HTTPS']='on';$_SERVER['REQUEST_METHOD']='GET';$_SERVER['REQUEST_URI']='/';define('DISABLE_WP_CRON',true);
require '/home/u595644545/domains/ruskyobchod.sk/public_html/wp-load.php';
global $wpdb;if($wpdb->prefix!=='wp_')throw new RuntimeException('Wrong database');
function entrance_replace($v){
 if(is_array($v)){foreach($v as$k=>$x)$v[$k]=entrance_replace($x);return $v;}
 if(!is_string($v))return $v;
 if(is_serialized($v)){return serialize(entrance_replace(unserialize($v,['allowed_classes'=>false])));}
 $pairs=['вход со стороны улицы Palisády'=>'вход со стороны улицы Замоцкая','Вход со стороны улицы Палисьады'=>'Вход со стороны улицы Замоцкая','Вход со стороны улицы Палисады'=>'Вход со стороны улицы Замоцкая','Вход со&nbsp;стороны улицы&nbsp;Палисады'=>'Вход со&nbsp;стороны улицы&nbsp;Замоцкая','Вход со стороны ул. Palisády'=>'Вход со стороны ул. Zámocká','Vchod z ulice Palisády'=>'Vchod zo Zámockej ulice','Vstup z ulice Palisády'=>'Vstup zo Zámockej ulice'];
 foreach($pairs as$a=>$b){$v=str_replace($a,$b,$v);$v=str_replace(htmlentities($a,ENT_QUOTES,'UTF-8'),htmlentities($b,ENT_QUOTES,'UTF-8'),$v);$a=substr(json_encode($a),1,-1);$b=substr(json_encode($b),1,-1);$v=str_replace($a,$b,$v);}return $v;
}
$specs=[[$wpdb->options,'option_id','option_value',"option_name IN ('widget_text','widget_block')"],[$wpdb->posts,'ID','post_content',"post_type='page'"],[$wpdb->postmeta,'meta_id','meta_value',"post_id IN (SELECT ID FROM {$wpdb->posts} WHERE post_type='page')"]];$changes=[];
foreach($specs as[$table,$pk,$col,$where])foreach($wpdb->get_results("SELECT `$pk` AS id,`$col` AS value FROM `$table` WHERE $where",ARRAY_A)as$r){$next=entrance_replace($r['value']);if($next!==$r['value'])$changes[]=['table'=>$table,'pk'=>$pk,'column'=>$col,'id'=>$r['id'],'before'=>$r['value'],'after'=>$next];}
echo json_encode(['changes'=>array_map(static fn($c)=>[$c['table'],$c['id']],$changes)])."\n";
if(($argv[1]??'inspect')!=='apply')exit;
$backup='/home/u595644545/backups/format-domain-20260914-01/entrance-changes-2.json';if(file_exists($backup))throw new RuntimeException('Already applied');
if(file_put_contents($backup,json_encode($changes))===false)throw new RuntimeException('Backup failed');chmod($backup,0600);
$wpdb->query('START TRANSACTION');try{foreach($changes as$c){if($wpdb->query($wpdb->prepare("UPDATE `{$c['table']}` SET `{$c['column']}`=%s WHERE `{$c['pk']}`=%d AND BINARY `{$c['column']}`=BINARY %s",$c['after'],$c['id'],$c['before']))!==1)throw new RuntimeException('Concurrent edit');}$wpdb->query('COMMIT');}catch(Throwable$e){$wpdb->query('ROLLBACK');throw $e;}
wp_cache_flush();if(function_exists('wp_cache_clear_cache'))wp_cache_clear_cache();echo "Applied\n";
