<?php
// CLI only: a single homepage edit with backup.
if (PHP_SAPI !== 'cli') { exit; }
$_SERVER['HTTP_HOST']='ruskyobchod.sk';$_SERVER['REQUEST_METHOD']='GET';$_SERVER['REQUEST_URI']='/';
define('DISABLE_WP_CRON',true);
require '/home/u595644545/domains/ruskyobchod.sk/public_html/wp-load.php';
if (wp_parse_url(home_url(), PHP_URL_HOST) !== 'ruskyobchod.sk') { throw new RuntimeException('Unexpected site'); }
$id=(int)get_option('page_on_front');$post=get_post($id);
if(!$post || $post->post_type!=='page')throw new RuntimeException('Homepage not found');
$backup='/home/u595644545/backups/format-domain-20260914-01';
if(!is_dir($backup)&&!mkdir($backup,0700,true))throw new RuntimeException('Backup directory failed');
$file=$backup.'/homepage-before.json';
if(!file_exists($file))file_put_contents($file,json_encode(['id'=>$id,'content'=>$post->post_content],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),LOCK_EX);
if(strpos($post->post_content,'id="gastronom-new-format"')===false){
 $notice=file_get_contents('/tmp/gastronom-format-20260914-announcement.html');
 if(!$notice||strpos($notice,'Нов')===false)throw new RuntimeException('Notice missing');
 $result=wp_update_post(['ID'=>$id,'post_content'=>wp_slash($notice."\n".$post->post_content)],true);
 if(is_wp_error($result))throw new RuntimeException($result->get_error_message());
}
if(function_exists('wp_cache_clear_cache'))wp_cache_clear_cache();
clean_post_cache($id);
echo json_encode(['homepage'=>$id,'notice_present'=>strpos(get_post($id)->post_content,'gastronom-new-format')!==false,'backup'=>$file],JSON_UNESCAPED_UNICODE)."\n";
