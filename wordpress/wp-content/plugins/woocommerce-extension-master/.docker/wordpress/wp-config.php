<?php
define('WP_DEBUG', false);

define('DB_NAME', 'wordpress_db');
define('DB_USER', 'wordpress');
define('DB_PASSWORD', 'wordpress');
define('DB_HOST', 'mariadb');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

define('AUTH_KEY', '*gDRcpOAPq_+~>6rF!/RryPV-7,2ssE6%`k<(@-AK-odlFZ#IpcbCV[MVov.t4Q/');
define('SECURE_AUTH_KEY', ', Ef#OX#)KRi6!WVJ]& x*tUj%u9lJIMSEtrFU| +|G/2Xc1@a>Wfc*TO*fK3:5]');
define('LOGGED_IN_KEY', 'Bb~b?uXOJX6!e?GZZ=/;!Q__{<eOX7s=(.(`EbOV_UQqy0h881}_|N!BJ^T-z?bs');
define('NONCE_KEY', '4y()<N.X43$|fp~zOXgE-hoZWeS!xTch[%rs^_Fy2HEQaBaRHN&LBI(]k|*|vCDB');
define('AUTH_SALT', '_V`?]HpCOs10$A^68BOP0l|.VPBTQ|f!U9c:b`..qEqH7G}K_->9^(qE%%;lL&V~');
define('SECURE_AUTH_SALT', 'I^W<lK1uH Pn-LLxoNhaZ{w|c%Qg/l1b9L_-Nu%#5m+$+fO<ev#7H)o[/Y3ZG8mg');
define('LOGGED_IN_SALT', 'vK$T7Gw35sSvZ$;4y($2fLtQ|h-Go&ldN7)0`*VQ0q`SSEwFGZWWBdgkrvp5a_wc');
define('NONCE_SALT', 'c8n`C%]I-)iy|g%N{X6:A7!QTHA]=KwR<|0r>J-_`;KLqO&r)N7~@Gu5jF^CUDZ^');

$table_prefix = 'wp_';

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
