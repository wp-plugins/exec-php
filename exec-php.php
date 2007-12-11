<?php
/*
Plugin Name: Exec-PHP
Plugin URI: http://bluesome.net/post/2005/08/18/50/
Description: Executes &lt;?php ?&gt; code in your posts, pages and text widgets. See the <a href="../wp-content/plugins/exec-php/docs/readme.html">local documentation</a> for further information.
Version: 4.3
Author: S&ouml;ren Weber
Author URI: http://bluesome.net
*/

require_once(dirname(__FILE__).'/manager.php');

// ----------------------------------------------------------------------------
// main
// ----------------------------------------------------------------------------
global $g_execphp_manager;
if (!isset($g_execphp_manager))
	// strange assignment because of explaination how references work;
	// this will generate warnings with error_reporting(E_STRICT) using PHP5;
	// http://www.php.net/manual/en/language.references.whatdo.php
	$GLOBALS['g_execphp_manager'] =& new ExecPhp_Manager();

?>