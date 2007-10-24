<?php
/*
Plugin Name: Exec-PHP
Plugin URI: http://bluesome.net/post/2005/08/18/50/
Description: Executes &lt;?php ?&gt; code in your posts, pages and text widgets. See the <a href="../wp-content/plugins/exec-php/docs/readme.html">documentation</a> for further information.
Version: 3.4
Author: S&ouml;ren Weber
Author URI: http://bluesome.net
Update Server: http://bluesome.net/
Min WP Version: 2.0
*/

define('EXECPHP_VERSION', '3.4');
define('EXECPHP_PLUGIN_ID', 'exec-php');
define('EXECPHP_CAPABILITY', 'exec_php');

// still needed for deletion from the database as they are obsolete since version 3.1
define('EXECPHP_OPTION_HAS_OLD_STYLE', 'exec-php_has_old_style');
define('EXECPHP_OPTION_IGNORE_OLD_STYLE_WARNING', 'exec-php_ignore_old_style_warning');

// --------------------------------------------------------------------------
// Wordpress 2.x support
// --------------------------------------------------------------------------

function execphp_eval_php($content)
{
	// to be compatible with older PHP4 installations
	// don't use fancy ob_XXX shortcut functions
	ob_start();
	eval("?>$content<?php ");
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function execphp_eval_restricted_php($content)
{
	global $post;

	// check whether the post author is allowed to execute PHP code
	if (!isset($post) || !isset($post->post_author))
		return $content;
	$poster = new WP_User($post->post_author);
	if (!$poster->has_cap(EXECPHP_CAPABILITY))
		return $content;

	return execphp_eval_php($content);
}

// --------------------------------------------------------------------------
// initialization
// --------------------------------------------------------------------------

function execphp_install_capability()
{
	global $wp_roles;

	// if there is at least one role with the EXECPHP_CAPABILITY capability,
	// then the plugin was previously been installed and we must not do
	// anything; don't rely that the cap is attachted to the same roles
	// as during installation because this could already be changed
	// by the administrator
	if (!$wp_roles)
		return;
	foreach ($wp_roles->role_objects as $role)
	{
		if ($role->has_cap(EXECPHP_CAPABILITY))
			return;
	}

	// be sure standard roles are available, these may be deleted or
	// renamed by the blog administrator
	$role = get_role('administrator');
	if ($role !== NULL)
		$role->add_cap(EXECPHP_CAPABILITY);
	$role = get_role('editor');
	if ($role !== NULL)
		$role->add_cap(EXECPHP_CAPABILITY);
}

function execphp_install()
{
	// not needed anymore
	delete_option(EXECPHP_OPTION_HAS_OLD_STYLE);
	delete_option(EXECPHP_OPTION_IGNORE_OLD_STYLE_WARNING);
	execphp_install_capability();
}

function execphp_init()
{
	add_filter('admin_menu', 'execphp_install');
	add_filter('the_content', 'execphp_eval_restricted_php', 1);
	add_filter('the_content_rss', 'execphp_eval_restricted_php', 1);
	add_filter('the_excerpt', 'execphp_eval_restricted_php', 1);
	add_filter('the_excerpt_rss', 'execphp_eval_restricted_php', 1);
	add_filter('widget_text', 'execphp_eval_php', 1);
}

// --------------------------------------------------------------------------
// activate plugin
// --------------------------------------------------------------------------

global $wp_version;
if (substr($wp_version, 0, 2) == "1.")
	_e("This version of Exec-PHP does not support Wordpress 1.x anymore", EXECPHP_PLUGIN_ID);
else
	add_filter('init', 'execphp_init');
?>