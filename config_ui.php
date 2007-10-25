<?php

require_once(dirname(__FILE__).'/cache.php');
require_once(dirname(__FILE__).'/const.php');

define('ExecPhp_ACTION_UPDATE_OPTIONS', 'update_options');
define('ExecPhp_CONFIG_FORM_ID', 'execphp_configuration');

// ----------------------------------------------------------------------------
// the ExecPhp_ConfigUi class displays the config interface in the
// admin panel
// ----------------------------------------------------------------------------

// use this guard to avoid error messages in WP admin panel if plugin
// is disabled because of a version conflict but you still try to reload
// the plugins config interface
if (!class_exists('ExecPhp_ConfigUi')) :
class ExecPhp_ConfigUi
{
	var $m_cache;

	// ----------------------------------------------------------------------------
	// init
	// ----------------------------------------------------------------------------

	// Sets up the Exec-Php config menu
	function ExecPhp_ConfigUi(&$cache, $status)
	{
		$this->m_cache = $cache;
		$this->toggle_filter($status);
		add_filter('admin_head', array(&$this, 'filter_admin_head'));
	}

	function toggle_filter($status)
	{
		if ($status == ExecPhp_STATUS_OKAY)
			add_filter('admin_menu', array(&$this, 'filter_admin_menu_option'));
		else
			remove_filter('admin_menu', array(&$this, 'filter_admin_menu_option'));

		if ($status == ExecPhp_STATUS_PLUGIN_VERSION_MISMATCH)
			add_filter('admin_footer', array(&$this, 'filter_admin_footer_plugin_version'));
		else
			remove_filter('admin_footer', array(&$this, 'filter_admin_footer_plugin_version'));

		if ($status != ExecPhp_STATUS_OKAY
			&& $status != ExecPhp_STATUS_PLUGIN_VERSION_MISMATCH)
			add_filter('admin_footer', array(&$this, 'filter_admin_footer_unknown'));
		else
			remove_filter('admin_footer', array(&$this, 'filter_admin_footer_unknown'));
	}

	function filter_admin_menu_option()
	{
		// delay initialization until the WP framework is loaded
		add_submenu_page('options-general.php',
			ExecPhp_PLUGIN_NAME,
			ExecPhp_PLUGIN_NAME,
			'edit_others_posts', __FILE__,
			array(&$this, 'submenu_page_option_general'));
	}

	function filter_admin_head()
	{
?>
	<script type="text/javascript">
		function ExecPhp_setMessage(heading, text)
		{
			var message = "<p><strong>" + heading + "</strong> " + text + "</p>";
			var container = document.getElementById("exec-php-message");
			try
			{
				container.innerHTML = container.innerHTML + message;
			}
			catch(e)
			{
				container = document.createElement("div");
				container.className = "updated fade-ff0000";
				container.setAttribute("id", "exec-php-message");
				var adminmenu = document.getElementById("adminmenu");
				adminmenu.parentNode.insertBefore(container, adminmenu.nextSibling);
				container.innerHTML = message;
			}
		}
	</script>

<?php
	}

	function filter_admin_footer_plugin_version()
	{
		$option =& $this->m_cache->get_option();

		$heading = sprintf(__('%s plugin is not active.', ExecPhp_PLUGIN_ID)
			, ExecPhp_PLUGIN_NAME);
		$text = sprintf(__('For security reasons the %1$s plugin functionality was turned off because no necessary upgrade of the plugin could be performed. All code may be viewable to your blog readers. This is plugin version %2$s but previously there was version %3$s installed. Downgrading from a newer version to an older version of the plugin is not supported.', ExecPhp_PLUGIN_ID)
			, ExecPhp_PLUGIN_NAME, ExecPhp_VERSION, $option->get_version());
		$this->print_admin_message($heading, $text);
	}

	function filter_admin_footer_unknown()
	{
		$option =& $this->m_cache->get_option();
		$heading = sprintf(__('%s plugin is not active.', ExecPhp_PLUGIN_ID)
			, ExecPhp_PLUGIN_NAME);
		$text = sprintf(__('For security reasons the %1$s plugin functionality was turned off because an unknown error (%2$s) occured. All code may be viewable to your blog readers. This should never happen if you use the plugin with a compatible WordPress version and installed it as described in the documentation.', ExecPhp_PLUGIN_ID)
			, ExecPhp_PLUGIN_NAME, $option->get_cooperation_status());
		$this->print_admin_message($heading, $text);
	}

	// ----------------------------------------------------------------------------
	// tools
	// ----------------------------------------------------------------------------

	function print_admin_message($heading, $text)
	{
?>
	<script type="text/javascript">
		ExecPhp_setMessage('<?php echo $heading; ?>', '<?php echo $text; ?>');
	</script>

<?php
	}

	// ----------------------------------------------------------------------------
	// interface
	// ----------------------------------------------------------------------------

	// Exec-PHP configuration page
	function submenu_page_option_general()
	{
		global $wpdb;

		// executing form actions
		$option =& $this->m_cache->get_option();
		if (isset($_POST[ExecPhp_ACTION_UPDATE_OPTIONS]))
		{
			check_admin_referer(ExecPhp_ACTION_UPDATE_OPTIONS);
			$option->set_from_POST();
			$option->save();
			echo '<div id="message" class="updated fade"><p><strong>'.
				__('Options saved.', ExecPhp_PLUGIN_ID) . "</strong></p></div>\n";
		}
		$this->toggle_filter($option->get_status());
?>
	<div class="wrap">
		<h2><?php echo sprintf(__('Exec-PHP %s Options', ExecPhp_PLUGIN_ID), ExecPhp_VERSION); ?></h2>
		<p><?php echo sprintf(__("Exec-PHP executes <code>&lt;?php ?&gt;</code> code in your posts, pages and text widgets. Execution of PHP code can be restricted by assigning the &quot;exec_php&quot; capability to individual users or roles by using a role manager plugin. A <a href='%s'>local copy of the documentation</a> comes with this plugin. New versions and further documentation may be found on the <a href='http://bluesome.net/post/2005/08/18/50/'>official plugin page</a>.", ExecPhp_PLUGIN_ID), get_option('siteurl'). '/'. ExecPhp_DIR. '/docs/readme.html'); ?></p>
		<form action="" method="post" id="<?php echo ExecPhp_CONFIG_FORM_ID; ?>">
			<fieldset class="options">
				<legend><?php _e('Basic options', ExecPhp_PLUGIN_ID); ?></legend>
				<p><?php _e("The basic options define the overall behavior of the plugin.", ExecPhp_PLUGIN_ID); ?></p>
				<table class="editform optiontable">
					<tr valign="top">
						<th scope="row"><?php _e('Execute PHP code in widgets:', ExecPhp_PLUGIN_ID); ?></th>
						<td>
							<label for="<?php echo ExecPhp_POST_PRINT_USER_FORM; ?>">
								<input type="checkbox" name="<?php echo ExecPhp_POST_WIDGET_SUPPORT; ?>" id="<?php echo ExecPhp_POST_WIDGET_SUPPORT; ?>" value="true" <?php if ($option->get_widget_support()) : ?>checked="checked" <?php endif; ?>/>
								<?php echo sprintf(__("Executing PHP code in widgets is not restricted to any user. Users who can modify widgets (which is restricted by the &quot;switch_themes&quot; capability) will also be able to execute PHP code in widgets. If you want to disallow PHP code execution in widgets for all users, mark this checkbox.", ExecPhp_PLUGIN_ID)); ?>

							</label>
						</td>
					</tr>
				</table>
			</fieldset>

			<p class="submit">
				<input type="submit" name="<?php echo ExecPhp_ACTION_UPDATE_OPTIONS; ?>" value="<?php _e('Update Options') ?> &raquo;" />
				<?php wp_nonce_field(ExecPhp_ACTION_UPDATE_OPTIONS); ?>

			</p>
		</form>
	</div>
<?php
	}
}
endif;

?>