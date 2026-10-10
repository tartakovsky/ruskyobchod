<?php
define('ABSPATH','/sandbox/');
define('GASTRONOM_REVIEW_SANDBOX',true);
function wp_get_environment_type(){return 'production';}
require __DIR__.'/../../wordpress/wp-content/plugins/gastronom-order-review/gastronom-order-review.php';
if(function_exists('gor_managed')||function_exists('gor_api')){throw new Exception('Production gate failed');}
echo "PASS production environment cannot activate review handlers even with sandbox flag\n";
