<?php
require __DIR__.'/../../wordpress/wp-content/plugins/gastronom-order-review/src/Outbox.php';
use Gastronom\OrderReview\Outbox as O;
function check_mail($ok,$name){if(!$ok)throw new Exception('FAIL '.$name);echo "PASS $name\n";}
$entry=['status'=>'pending','attempts'=>0,'to'=>'test@example.invalid','reply_to'=>'shop@example.invalid','message'=>['subject'=>'Test','html'=>'<p>Frozen quote</p>']];
$saved=[];$calls=0;$save=function($v)use(&$saved){$saved=$v;};
$transport=function($v)use(&$calls,&$saved){check_mail($saved['status']==='sending','mail intent durable before transport');$calls++;return false;};
$failed=O::deliver($entry,$save,$transport);check_mail($failed['status']==='failed'&&$failed['attempts']===1,'explicit mail refusal retryable');
$sent=O::deliver($failed,$save,function($v)use(&$calls){$calls++;return true;});
O::deliver($sent,$save,$transport);check_mail($calls===2,'sent email replay does not send again');
check_mail($sent['message']===$entry['message']&&$sent['to']===$entry['to'],'retry retains frozen text and recipient');
$uncertain=O::deliver($entry,$save,function($v){throw new RuntimeException('Connection lost after SMTP accept');});
check_mail($uncertain['status']==='sending','ambiguous mail transport remains uncertain');
try{O::deliver($uncertain,$save,$transport);check_mail(false,'uncertain resend');}catch(DomainException $e){check_mail(true,'uncertain email not automatically resent');}
$GLOBALS['mail_call']=null;
function wp_mail(...$args){$GLOBALS['mail_call']=$args;return true;}
check_mail(O::wordpress($entry)&&$GLOBALS['mail_call'][0]==='test@example.invalid'&&in_array('Reply-To: shop@example.invalid',$GLOBALS['mail_call'][3]),'WordPress mail adapter preserves reply channel');
