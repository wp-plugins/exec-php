<?php
/*
Plugin Name: Exec-PHP
Plugin URI: http://www.soeren-weber.net/post/2005/08/18/50/
Description: Allows &lt;?PHP ?&gt; or [?php ?] tags inside of your posts to execute PHP code. For WP 2.0 you can restrict PHP execution by assigning the role exec_php which is assigned by default to the administrators and editors role.
Version: 2.0
Author: S&ouml;ren Weber
Author URI: http://soeren-weber.net
*/

/* Remarks:
In WP 2.0 post authors can do the following related to their assigned capabilities:

cap                 write/edit PHP with   write/edit PHP with   execute PHP
                    [?php ?] tags         <?php ?> tags
=================   ===================   ===================   ===========
exec_php                                                             X
unfiltered_html             X                      X
<none from above>           X
*/

define('EXECPHP_VERSION', '2.0');
define('EXECPHP_CAP', 'exec_php');

function execphp_fix_tag($match)
{
	// replacing WPs strange PHP tag handling with a functioning tag pair
	$output = '<?php'. $match[2]. '?>';
	return $output;
}

function execphp_eval_php($content)
{
	// for debugging also group unimportant components with ()
	// to check them with a print_r($matches)
	$pattern = '/'.
		'(?:(?:<)|(\[))[\s]*\?php'. // the opening of the <?php or [?php tag
		'(((([\'\"])([^\\\5]|\\.)*?\5)|(.*?))*)'. // ignore content of PHP quoted strings
		'\?(?(1)\]|>)'. // the closing ? > or ?] tag
		'/is';
	$content = preg_replace_callback($pattern, 'execphp_fix_tag', $content);
	// to be compatible with older PHP4 installations
	// don't use fancy ob_XXX shortcut functions
	ob_start();
	eval(" ?> $content <?php ");
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function execphp_restricted_eval_php($content)
{
	global $post;
	if (!isset($post) || !isset($post->post_author))
		return $content;
	$poster = new WP_User($post->post_author);
	if (!$poster->has_cap(EXECPHP_CAP))
		return $content;
	return execphp_eval_php($content);
}

// --------------------------------------------------------------------------
// activating and installing the plugin
// --------------------------------------------------------------------------

function execphp_install_cap()
{
	global $wp_roles;

	// if there is at least one role with the EXECPHP_CAP capability, then
	// the plugin was previously been installed and we must not do
	// anything; don't rely that the cap is attachted to the same roles
	// as during installation because this could already be changed
	// by the administrator

	foreach($wp_roles->role_objects as $role)
	{
		if ($role->has_cap(EXECPHP_CAP))
			return;
	}

	$role = get_role('administrator');
	if ($role !== NULL)
		$role->add_cap(EXECPHP_CAP);
	$role = get_role('editor');
	if ($role !== NULL)
		$role->add_cap(EXECPHP_CAP);
}

function execphp_activate_1_x()
{
	add_filter('the_content', 'execphp_eval_php', 1);
}

function execphp_activate()
{
	add_action('admin_menu', 'execphp_install_cap');
	add_filter('the_content', 'execphp_restricted_eval_php', 1);
}

function execphp_init()
{
	global $wp_version;
	if (substr($wp_version, 0, 2) == "1.")
		execphp_activate_1_x();
	else
		execphp_activate();
}
add_filter('init', 'execphp_init');
?>