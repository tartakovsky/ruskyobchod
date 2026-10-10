<?php
// No SMTP connections, real credentials or WordPress database.
define('ABSPATH','/app/');
$GLOBALS['hooks']=[];
function add_filter($name,$callback,$priority=10){$GLOBALS['hooks'][$name][]=$callback;}
function add_action($name,$callback,$priority=10){add_filter($name,$callback,$priority);}
require __DIR__.'/../../wordpress/wp-content/mu-plugins/rusky-mail-transport.php';
function mt(bool $yes,string $label){if(!$yes)throw new Exception('FAIL '.$label);echo 'PASS '.$label."\n";}
mt($GLOBALS['hooks']===[],'mail transport installation is dormant without private configuration');
class FakeMailer {
 public $Host,$Port,$SMTPAuth,$Username,$Password,$SMTPSecure,$SMTPAutoTLS,$SMTPDebug,$Timeout,$Sender;
 public $From,$FromName,$mode='mail',$replyTo='customer@example.invalid',$to='shop@example.invalid',$Body='<p>message</p>',$SMTPOptions=[];
 function isSMTP(){$this->mode='smtp';}function setFrom($email,$name,$auto){$this->From=$email;$this->FromName=$name;}
}
$c=['host'=>'smtp.m1.websupport.sk','port'=>465,'encryption'=>'ssl','username'=>'objednavky@example.invalid','password'=>'isolated-fake-secret','from'=>'objednavky@example.invalid','name'=>'Gastronom'];
$m=new FakeMailer();rmt_apply($m,$c);
mt($m->mode==='smtp'&&$m->SMTPAuth&&$m->Host===$c['host']&&$m->Port===465&&$m->SMTPSecure==='ssl','authenticated Websupport SMTP uses encrypted port 465');
mt($m->From===$c['from']&&$m->Sender===$m->From&&$m->FromName==='Gastronom','envelope and visible sender aligned');
mt($m->to==='shop@example.invalid'&&$m->replyTo==='customer@example.invalid'&&$m->Body==='<p>message</p>','mail configuration preserves recipient, customer Reply-To and body');
mt($m->SMTPDebug===0&&$m->Timeout===15&&$m->SMTPOptions===[],'SMTP debugging disabled and TLS certificate validation not bypassed');
$c['port']=587;$c['encryption']='tls';rmt_apply($m,$c);mt($m->SMTPSecure==='tls'&&$m->SMTPAutoTLS,'STARTTLS transport supported without plaintext fallback');
$path='/smtp-test.php';define('GASTRONOM_SMTP_CONFIG',$path);
function config_file(array $c){global $path;file_put_contents($path,'<?php return '.var_export($c,true).';');chmod($path,0600);}
config_file($c);mt(rmt_config()===$c,'private configuration loaded without WordPress options');
foreach([['port'=>25],['encryption'=>''],['from'=>"bad@example.invalid\r\nBcc: other@example.invalid"],['host'=>'smtp.invalid/path'],['from'=>'different@example.invalid'],['password'=>'']] as $bad){config_file($bad+$c);try{rmt_config();mt(false,'invalid SMTP configuration');}catch(RuntimeException $e){mt(true,'invalid sender/TLS configuration rejected');}}

config_file($c);chmod($path,0644);try{rmt_config();mt(false,'public-readable secret file');}catch(RuntimeException $e){mt(true,'world-readable credential file rejected');}
