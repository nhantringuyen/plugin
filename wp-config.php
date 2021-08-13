<?php
define('DB_NAME', "dsmartwk_bookstore");
define('DB_USER', "dsmartwk_bookstore");
define('DB_PASSWORD', "yEwriAjKn");
define('DB_HOST', "localhost");
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');
define('AUTH_KEY',         'WgW{). 7o-dA^E_^=trHJ;/a9Od_vSQP=x^s$:h?4I$&r@,DeYv9H]RiM.]tTRwk');
define('SECURE_AUTH_KEY',  '[O4w4LK7m.YJKtlIgmz}u?Vo9&tEa}ZZGoU`e[x&7Al8r-3tX7rNxd5ww4[L*,?#');
define('LOGGED_IN_KEY',    'fAAQ2vcs.^u&Tqm:(SLyn;$r]bRZ.b:`w!rCqsK`.LQ85isJ!j>,e]!}?,jKWKkI');
define('NONCE_KEY',        'tz+DUuZck3uFI0okx2jsOAs L:ODr`xcNq[JkS719By@Va:RwFxQ/&e3&CB]6=U@');
define('AUTH_SALT',        'DDQ_z@7%3!U.M=EPNmPNX%Y-wS{+>(R}`?:EYtzg8gKvPU%m^LwOr7zaS2>^KZ#`');
define('SECURE_AUTH_SALT', '^8CE}#Uq1`(F2Y9 a1sgQ.viFy7Tw%/(iKQBp~tv@oA=KJws^nG%J).2=(#:!CIv');
define('LOGGED_IN_SALT',   '?%U3A-PJ 5`i:`UKki)I=vy>Lry$9upT<C|}Sw{LL~CJyv7:+`K75Vrv/r+e&as1');
define('NONCE_SALT',       'bg7Pl6D?hM2<u`/bp)V5Phs4}iIHK|#;35=A0MS/P&fHr4^s#i]q-&]N;J:hmU7n');
$table_prefix  = 'wp_';
define('WP_DEBUG', false);
define('WPLANG', 'vi');
define('WP_POST_REVISIONS', false );
define('DISALLOW_FILE_EDIT',true);
// define('DISALLOW_FILE_MODS',true);
define( 'WPCF7_AUTOP', false );
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
require_once(ABSPATH . 'wp-settings.php');