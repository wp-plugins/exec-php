<?php

define('ExecPhp_VERSION', '4.1');
define('ExecPhp_PLUGIN_ID', 'exec-php');
define('ExecPhp_DIR', 'wp-content/plugins/exec-php');

define('ExecPhp_CAPABILITY_WIDGETS', 'switch_themes');
define('ExecPhp_CAPABILITY_EXECUTE_ARTICLES', 'exec_php');
define('ExecPhp_CAPABILITY_WRITE_ARTICLES', 'unfiltered_html');
define('ExecPhp_CAPABILITY_MANAGE', 'edit_plugins');

define('ExecPhp_POST_WIDGET_SUPPORT', 'execphp_widget_support');

define('ExecPhp_STATUS_OKAY', 0);
define('ExecPhp_STATUS_UNINITIALIZED', 1);
define('ExecPhp_STATUS_PLUGIN_VERSION_MISMATCH', 2);

?>