<?php
$n=0;
foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator('/app/wordpress/wp-content/plugins/gastronom-order-review')) as $f){
    if($f->isFile()&&$f->getExtension()==='php'){token_get_all(file_get_contents($f->getPathname()),TOKEN_PARSE);$n++;echo 'PASS syntax '.$f->getFilename()."\n";}
}
echo "$n PHP files parsed.\n";
foreach(['rusky-dotypos-fiscalization.php','rusky-dotypos-stock-bridge.php','rusky-preorder-admin.php','rusky-weight-preorder.php','rusky-preorder-notifications.php','rusky-order-review-guard.php','rusky-shipping-notice-email.php'] as $name){token_get_all(file_get_contents('/app/wordpress/wp-content/mu-plugins/'.$name),TOKEN_PARSE);echo 'PASS syntax '.$name."\n";}

foreach(['gastronom-stock-fix/gastronom-stock-fix.php','gls-shipping-for-woocommerce/includes/api/class-gls-shipping-api-data.php','packeta/src/Packetery/Module/Order/Builder.php','packeta/src/Packetery/Module/Order/PacketSubmitter.php'] as $file){token_get_all(file_get_contents('/app/wordpress/wp-content/plugins/'.$file),TOKEN_PARSE);echo 'PASS syntax '.$file."\n";}
