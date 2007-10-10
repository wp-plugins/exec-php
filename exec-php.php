<?php
/*
Plugin Name: Exec-PHP
Plugin URI: http://bluesome.net/post/2005/08/18/50/
Description: Allows &lt;?php ?&gt; tags inside the content or excerpt of your posts and pages to be executed just as in usual PHP files
Version: 3.0
Author: S&ouml;ren Weber
Author URI: http://bluesome.net
Update Server: http://bluesome.net/
Min WP Version: 2.0
*/

define('EXECPHP_VERSION', '3.0');
define('EXECPHP_PLUGIN_ID', 'exec-php');
define('EXECPHP_CAPABILITY', 'exec_php');
define('EXECPHP_OPTION_HAS_OLD_STYLE', 'exec-php_has_old_style');
define('EXECPHP_OPTION_IGNORE_OLD_STYLE_WARNING', 'exec-php_ignore_old_style_warning');

// --------------------------------------------------------------------------
// Wordpress 1.x support
// --------------------------------------------------------------------------

function execphp_fix_tag_1_x($match)
{
	// replacing WPs strange PHP tag handling with a functioning tag pair
	$output = '<?php'. $match[2]. '?>';
	return $output;
}

/* WP 1.x not supported anymore
function execphp_eval_php_1_x($content)
{
	// for debugging also group unimportant components with ()
	// to check them with a print_r($matches)
	$pattern = '/'.
		'(<[\s]*\?php)'. // the opening of the <?php tag
		'([\s]+((([\'\"])([^\\\5]|\\.)*?\5)|(.*?))*)'. // ignore content of PHP quoted strings
		'(\?>)'. // the closing ? > tag
		'/is';
	$content = preg_replace_callback($pattern, 'execphp_fix_tag_1_x', $content);

	// to be compatible with older PHP4 installations
	// don't use fancy ob_XXX shortcut functions
	ob_start();
	eval(" ?> $content <?php ");
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function execphp_init_1_x()
{
	add_filter('the_content', 'execphp_eval_php_1_x', 1);
}
*/

// --------------------------------------------------------------------------
// Wordpress 2.x and above support
// --------------------------------------------------------------------------

function execphp_eval_php($content)
{
	global $post;

	// check whether the post author is allowed to execute PHP code
	if (!isset($post) || !isset($post->post_author))
		return $content;
	$poster = new WP_User($post->post_author);
	if (!$poster->has_cap(EXECPHP_CAPABILITY))
		return $content;

	// to be compatible with older PHP4 installations
	// don't use fancy ob_XXX shortcut functions
	ob_start();
	eval(" ?> $content <?php ");
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

function execphp_init()
{
	add_filter('admin_menu', 'execphp_init_admin');
	add_filter('the_content', 'execphp_eval_php', 1);
	add_filter('the_excerpt', 'execphp_eval_php', 1);
	add_filter('the_excerpt_rss', 'execphp_eval_php', 1);
}

// --------------------------------------------------------------------------
// migration from previous versions
// --------------------------------------------------------------------------

function execphp_migrate_old_style()
{
	global $g_execphp_old_style_pattern;
	global $wpdb;

	$query = "
		SELECT
			`ID`,
			`post_content`,
			`post_excerpt`
		FROM
			`{$wpdb->posts}`
	";
	$wpdb->query($query);
	$s = $wpdb->get_results($query);
	if (!is_array($s))
		$s = array();

	foreach ($s as $i)
	{
		$i->post_content = $wpdb->escape(preg_replace_callback(
			$g_execphp_old_style_pattern, 'execphp_fix_tag_1_x', $i->post_content));
		$i->post_excerpt = $wpdb->escape(preg_replace_callback(
			$g_execphp_old_style_pattern, 'execphp_fix_tag_1_x', $i->post_excerpt));
		$query = "
			UPDATE `{$wpdb->posts}`
			SET
				`post_content` = '{$i->post_content}',
				`post_excerpt` = '{$i->post_excerpt}'
			WHERE `ID` = {$i->ID}
		";
		$wpdb->query($query);
	}
}

function execphp_scan_for_old_style()
{
	global $g_execphp_old_style_pattern;
	global $wpdb;

	$query = "
		SELECT
			`post_title`,
			`post_content`,
			`post_excerpt`
		FROM `{$wpdb->posts}`
	";
	$wpdb->query($query);
	$s = $wpdb->get_results($query);
	if (!is_array($s))
		$s = array();

	// don't start $has_old_style with 0 to make later checking easier
	$has_old_style = 1;
	$titles = array();
	foreach ($s as $i)
	{
		$content_has_old_style = preg_match($g_execphp_old_style_pattern, $i->post_content);
		$excpert_has_old_style = preg_match($g_execphp_old_style_pattern, $i->post_excerpt);
		if ($content_has_old_style || $excerpt_has_old_style)
		{
			$has_old_style += $content_has_old_style + $excerpt_has_old_style;
			$titles[] = $i->post_title;
			break;
		}
	}
	update_option(EXECPHP_OPTION_HAS_OLD_STYLE, $has_old_style);
	return $titles;
}

function execphp_old_style_warning()
{
	$path = plugin_basename(__FILE__);
	echo "
	<div id='execphp-warning' class='updated fade-ff0000'><p><strong>". __('Exec-PHP found malformed styled PHP tags.', EXECPHP_PLUGIN_ID). "</strong> ". sprintf(__('<a href="%1$s">Convert them on the EXEC-PHP config page</a> to let your PHP code work properly.', EXECPHP_PLUGIN_ID), "options-general.php?page=$path")."</p></div>
	<style type='text/css'>
		#adminmenu { margin-bottom: 5em; }
		#execphp-warning { position: absolute; top: 7em; }
	</style>
	";
}

function execphp_print_old_style_warning()
{
	$has_old_style = get_option(EXECPHP_OPTION_HAS_OLD_STYLE);
	$ignore_old_style_warning = get_option(EXECPHP_OPTION_IGNORE_OLD_STYLE_WARNING);
	if (!$ignore_old_style_warning && $has_old_style > 1)
		add_filter('admin_footer', 'execphp_old_style_warning');
	else
		remove_filter('admin_footer', 'execphp_old_style_warning');
}

function execphp_config_page()
{
	$has_old_style = get_option(EXECPHP_OPTION_HAS_OLD_STYLE);
	$ignore_old_style_warning = get_option(EXECPHP_OPTION_IGNORE_OLD_STYLE_WARNING);
	if (!$ignore_old_style_warning)
		$ignore_old_style_warning = false;

	if (isset($_POST['migrate_execphp']))
	{
		execphp_migrate_old_style();
		$has_old_style = 1;
		update_option(EXECPHP_OPTION_HAS_OLD_STYLE, $has_old_style);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Posts migrated', EXECPHP_PLUGIN_ID) . "</strong></p></div>\n";
	}
	if (isset($_POST['toggle_warning_execphp']))
	{
		$ignore_old_style_warning = !$ignore_old_style_warning;
		update_option(EXECPHP_OPTION_IGNORE_OLD_STYLE_WARNING, $ignore_old_style_warning);
		echo '<div id="message" class="updated fade"><p><strong>' . __('Options updated', EXECPHP_PLUGIN_ID) . "</strong></p></div>\n";
	}
	execphp_print_old_style_warning();
?>
<div class="wrap">
	<h2><?php _e('Exec-PHP Options', EXECPHP_PLUGIN_ID); ?></h2>
	<p><?php _e("The syntax of the PHP tags has changed from previous versions of this plugin allowing one of the following formats", EXECPHP_PLUGIN_ID); ?></p>

	<ul>
	<li><?php _e("<code>&lt;?php ?&gt;</code> (standard style, only usable for Wordpress 2.x)", EXECPHP_PLUGIN_ID); ?></li>
	<li><?php _e("<code>&lt; ?php ?&gt;</code> (spaced style, used for Wordpress 1.x)", EXECPHP_PLUGIN_ID); ?></li>
	<li><?php _e("<code>[?php ?]</code> (alternate style, only usable for Wordpress 2.x and Exec-PHP 2.0)", EXECPHP_PLUGIN_ID); ?></li>
	</ul>
	<p><?php _e("to only allowing standard PHP style in the form of", EXECPHP_PLUGIN_ID); ?></p>
	<ul>
	<li><?php _e("<code>&lt;?php ?&gt;</code> (standard style, only usable for Wordpress 2.x)", EXECPHP_PLUGIN_ID); ?></li>
	</ul>
	<p><?php _e("By pressing the 'Migrate' button below, the plugin will automatically migrate PHP tags from malformed style to standard style. You may execute this action as often as you like even if all tags are converted. <strong>Be sure to backup your database first before pressing the button.</strong>", EXECPHP_PLUGIN_ID); ?></p>
	<p><?php echo sprintf(__("If you feel, the probably displayed Exec-PHP warning is false and you want to just disable the warnings without migrating anything, you can use the 'Toggle warnings' button to toggle warnings on or off. You may also see the warnings if you are using the WYSIWYG editor or having the option '%s' truned on. Both is not recommened and will in almost all cases fail to execute your PHP code.", EXECPHP_PLUGIN_ID), __('WordPress should correct invalidly nested XHTML automatically')); ?></p>
<?php
	if ($has_old_style > 1)	{
?>
	<p><?php _e("The following posts/pages were found to contain malformed styled PHP tags either in the content or the excpert and will be migrated if you press the 'Migrate' button.", EXECPHP_PLUGIN_ID); ?></p>
	<ul>
<?php
		$titles = execphp_scan_for_old_style();
		foreach ($titles as $title)
			echo "<li>$title</li>\n";
	} else {
?>
	<p><?php _e("No posts were found that contain malformed styled PHP tags.", EXECPHP_PLUGIN_ID); ?></p>
<?php
	}
?>
	</ul>
	<form action="" method="post" id="execphp_toggle_warning">
		<p class="submit">
			<input type="submit" name="toggle_warning_execphp" value="<?php echo sprintf(__('Toggle warnings %s', EXECPHP_PLUGIN_ID), ($ignore_old_style_warning ? __('on', EXECPHP_PLUGIN_ID) : __('off', EXECPHP_PLUGIN_ID))); ?> &raquo;" />
		</p>
	</form>
	<form action="" method="post" id="execphp_migration">
		<p class="submit">
		<input type="submit" name="migrate_execphp" value="<?php _e('Migrate', EXECPHP_PLUGIN_ID); ?> &raquo;" />
		</p>
	</form>
</div>
<?php
}

function execphp_init_admin()
{
	/* HACK: #3002 */
	execphp_install();

	add_submenu_page('options-general.php', __('Exec-PHP', EXECPHP_PLUGIN_ID),
		__('Exec-PHP', EXECPHP_PLUGIN_ID), 10, __FILE__, 'execphp_config_page');
}

// --------------------------------------------------------------------------
// installation
// --------------------------------------------------------------------------

function execphp_install_capability()
{
	global $wp_roles;

	// if there is at least one role with the EXECPHP_CAPABILITY capability, then
	// the plugin was previously been installed and we must not do
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
	execphp_install_capability();
	execphp_scan_for_old_style();
	execphp_print_old_style_warning();
}

function execphp_uninstall()
{
	delete_option(EXECPHP_OPTION_HAS_OLD_STYLE);
	delete_option(EXECPHP_OPTION_IGNORE_OLD_STYLE_WARNING);
}

// --------------------------------------------------------------------------
// activate plugin
// --------------------------------------------------------------------------

// for debugging also group unimportant components with ()
// to check them with a print_r($matches)
global $g_execphp_old_style_pattern;
$g_execphp_old_style_pattern = '/'.
	'(?:(?:<[\s]+)|(\[[\s]*))\?php'. // the opening of the <? php or [?php tag
	'(((([\'\"])([^\\\5]|\\.)*?\5)|(.*?))*)'. // ignore content of PHP quoted strings
	'\?(?(1)\]|>)'. // the closing ? > or ?] tag
	'/is';

global $wp_version;
if (substr($wp_version, 0, 2) == "1.")
{
	/* WP 1.x not supported anymore
	add_filter('init', 'execphp_init_1_x');
	*/
	_e("This version of Exec-PHP does not support Wordpress 1.x anymore", EXECPHP_PLUGIN_ID);
}
else
{
/* HACK: #3002
	Does not work with WP 2.0.4 on Windows; see trac ticket #3002;
	so we have to implement some more logic to the has_old_style flag
	register_activation_hook(__FILE__, execphp_install);
	register_deactivation_hook(__FILE__, execphp_uninstall);
*/
	add_filter('init', 'execphp_init');
}
?>