<?php

// -----------------------------------------------------------------------------
// this cleans up all options once the user decided to uninstall the plugin
// -----------------------------------------------------------------------------

if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) {
	exit();
}
require_once(dirname(__FILE__).'/includes/cache.php');

$g_execphp_uninstaller =& new ExecPhp_Cache();
$g_execphp_uninstaller->uninstall();

?>