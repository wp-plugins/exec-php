<?php

require_once(dirname(__FILE__).'/cache.php');
require_once(dirname(__FILE__).'/const.php');

define('ExecPhp_ACTION_UPDATE_OPTIONS', 'update_options');
define('ExecPhp_ID_CONFIG_FORM', 'execphp_configuration');
define('ExecPhp_ID_INFO_FORM', 'execphp_information');
define('ExecPhp_ID_INFO_WIDGETS', 'execphp_widgets');
define('ExecPhp_ID_INFO_WRITE_ARTICLES', 'execphp_write_articles');
define('ExecPhp_ID_INFO_EXECUTE_ARTICLES', 'execphp_execute_articles');
define('ExecPhp_ID_MESSAGE', 'exec-php-message-');

// -----------------------------------------------------------------------------
// the ExecPhp_ConfigUi class displays the config interface in the
// admin panel
// -----------------------------------------------------------------------------

// use this guard to avoid error messages in WP admin panel if plugin
// is disabled because of a version conflict but you still try to reload
// the plugins config interface
if (!class_exists('ExecPhp_ConfigUi')) :
class ExecPhp_ConfigUi
{
	var $m_cache;

	// ---------------------------------------------------------------------------
	// init
	// ---------------------------------------------------------------------------

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

		if ($this->rtfm())
			add_filter('edit_form_advanced', array(&$this, 'filter_edit_form_advanced'));
		else
			remove_filter('edit_form_advanced', array(&$this, 'filter_edit_form_advanced'));
	}

	function filter_admin_menu_option()
	{
		// delay initialization until the WP framework is loaded
		add_submenu_page('options-general.php',
			__('Exec-PHP Options', ExecPhp_PLUGIN_ID),
			__('Exec-PHP', ExecPhp_PLUGIN_ID),
			ExecPhp_CAPABILITY_MANAGE, __FILE__,
			array(&$this, 'submenu_page_option_general'));
	}

	function filter_admin_head()
	{
		wp_print_scripts(array('sack'));
?>
	<style type="text/css">
		#<?php echo ExecPhp_ID_INFO_WIDGETS; ?> li,
		#<?php echo ExecPhp_ID_INFO_WRITE_ARTICLES; ?> li,
		#<?php echo ExecPhp_ID_INFO_EXECUTE_ARTICLES; ?> li {
			float: left;
			width: 20em;
			line-height: 1em;
		}

		#<?php echo ExecPhp_ID_INFO_WIDGETS; ?> p,
		#<?php echo ExecPhp_ID_INFO_WRITE_ARTICLES; ?> p,
		#<?php echo ExecPhp_ID_INFO_EXECUTE_ARTICLES; ?> p {
			text-align: center;
		}

		#<?php echo ExecPhp_ID_INFO_WIDGETS; ?> p *,
		#<?php echo ExecPhp_ID_INFO_WRITE_ARTICLES; ?> p *,
		#<?php echo ExecPhp_ID_INFO_EXECUTE_ARTICLES; ?> p * {
			vertical-align: middle;
		}
	</style>

	<script type="text/javascript">
		//<![CDATA[
		function ExecPhp_getUsersOfCapability(capability, display_id)
		{
			var ajax = new sack("<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php");
			ajax.element = display_id;
			ajax.setVar("cookie", document.cookie);
			ajax.setVar("action", "<?php echo ExecPhp_AJAX_ACTION_USERS_OF_CAPABILITY; ?>");
			ajax.setVar("<?php echo ExecPhp_AJAX_POST_CAPABILITY; ?>", capability);
			ajax.onError = function() {alert('<?php _e("AJAX HTTP error", ExecPhp_PLUGIN_ID); ?>')};
			ajax.runAJAX();
		}

		function ExecPhp_setMessage(parent, heading, text)
		{
			var message = "<p><strong>" + heading + "</strong> " + text + "</p>";
			var container = document.getElementById("<?php echo ExecPhp_ID_MESSAGE; ?>" + parent);
			try
			{
				container.innerHTML = container.innerHTML + message;
			}
			catch(e)
			{
				container = document.createElement("div");
				container.className = "updated fade-ff0000";
				container.setAttribute("id", "<?php echo ExecPhp_ID_MESSAGE; ?>" + parent);
				var adminmenu = document.getElementById(parent);
				adminmenu.parentNode.insertBefore(container, adminmenu.nextSibling);
				container.innerHTML = message;
			}
		}
		//]]>
	</script>

<?php
	}

	function filter_admin_footer_plugin_version()
	{
		$option =& $this->m_cache->get_option();
		$heading = __('Exec-PHP plugin is not active.', ExecPhp_PLUGIN_ID);
		$text = sprintf(__('For security reasons the Exec-PHP plugin functionality was turned off because no necessary upgrade of the plugin could be performed. All PHP code may be viewable to your blog readers. This is plugin version %1$s, previously there was version %2$s installed. Downgrading from a newer version to an older version of the plugin is not supported.', ExecPhp_PLUGIN_ID)
			, ExecPhp_VERSION, $option->get_version());
		$this->print_admin_message($heading, $text);
	}

	function filter_admin_footer_unknown()
	{
		$option =& $this->m_cache->get_option();
		$heading = __('Exec-PHP plugin is not active.', ExecPhp_PLUGIN_ID);
		$text = sprintf(__('For security reasons the Exec-PHP plugin functionality was turned off because an unknown error (%s) occured. All PHP code may be viewable to your blog readers. This error should never happen if you use the plugin with a compatible WordPress version and installed it as described in the documentation.', ExecPhp_PLUGIN_ID)
			, $option->get_status());
		$this->print_admin_message($heading, $text);
	}

	function filter_edit_form_advanced()
	{
		$option =& $this->m_cache->get_option();
		$heading = __('Exec-PHP Screw Up Warning.', ExecPhp_PLUGIN_ID);
		$text = __('Saving this post will screw up all contained PHP code and will render it permanently unuseful. Ignore this warning in case this article does not or will not make use of PHP code. Read the Exec-PHP documentation if you are unsure what to do next.', ExecPhp_PLUGIN_ID);
		$this->print_user_message($heading, $text);
	}

	// ---------------------------------------------------------------------------
	// tools
	// ---------------------------------------------------------------------------

	function print_admin_message($heading, $text)
	{
?>
	<script type="text/javascript">
		//<![CDATA[
		ExecPhp_setMessage('adminmenu', '<?php echo $heading; ?>', '<?php echo $text; ?>');
		//]]>
	</script>
<?php
	}

	function print_user_message($heading, $text)
	{
?>
	<script type="text/javascript">
		//<![CDATA[
		ExecPhp_setMessage('submenu', '<?php echo $heading; ?>', '<?php echo $text; ?>');
		//]]>
	</script>
<?php
	}

	function print_users_of_capability($capability, $display_id, $legend, $introduction)
	{
?>
			<fieldset class="options">
				<legend><?php echo $legend; ?></legend>
				<p><?php echo $introduction; ?></p>
				<div id="<?php echo $display_id; ?>">
					<?php _e('The list can not be displayed because you may have disabled Javascript or your browser does not support Javascript.', ExecPhp_PLUGIN_ID); ?>

				</div>
				<script type="text/javascript">
					//<![CDATA[
					document.getElementById('<?php echo $display_id; ?>').innerHTML =
						'<p><img src="<?php echo get_option('siteurl'). '/'. ExecPhp_DIR. '/images/progress.gif'; ?>" alt="<?php _e('An animated icon signaling that this information is still be loaded.', ExecPhp_PLUGIN_ID); ?>" /> <?php _e('Loading user information...', ExecPhp_PLUGIN_ID); ?></p>';
					ExecPhp_getUsersOfCapability('<?php echo $capability; ?>', '<?php echo $display_id; ?>');
					//]]>
				</script>
			</fieldset>
<?php
	}

	function rtfm()
	{
		// check whether the article author has read the documentation
		$has_unfiltered_cap = current_user_can(ExecPhp_CAPABILITY_WRITE_ARTICLES);
		if (!$has_unfiltered_cap)
			return true;
		if (user_can_richedit())
			return true;
		return false;
	}

	// ---------------------------------------------------------------------------
	// interface
	// ---------------------------------------------------------------------------

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
		<form action="" method="post" id="<?php echo ExecPhp_ID_CONFIG_FORM; ?>">
			<?php wp_nonce_field(ExecPhp_ACTION_UPDATE_OPTIONS); ?>

			<p class="submit">
				<input type="submit" name="<?php echo ExecPhp_ACTION_UPDATE_OPTIONS; ?>" value="<?php _e('Update Options &raquo;') ?>" />
			</p>
			<p><?php echo sprintf(__('Exec-PHP executes <code>&lt;?php ?&gt;</code> code in your posts, pages and text widgets. See the <a href="%s">local documentation</a> for further information. The latest version of the plugin, documentation and information will be found on the <a href="http://bluesome.net/post/2005/08/18/50/">official plugin homepage</a>.', ExecPhp_PLUGIN_ID), get_option('siteurl'). '/'. ExecPhp_DIR. '/docs/readme.html'); ?></p>

			<fieldset class="options">
				<legend><?php _e('Widget Options', ExecPhp_PLUGIN_ID); ?></legend>
				<p><?php _e('The widget options define how PHP code in text widgets will be handled.', ExecPhp_PLUGIN_ID); ?></p>
				<table class="editform optiontable">
					<tr valign="top">
						<th scope="row"><?php _e('Execute PHP code in text widgets:', ExecPhp_PLUGIN_ID); ?></th>
						<td>
							<label for="<?php echo ExecPhp_POST_WIDGET_SUPPORT; ?>">
								<input type="checkbox" name="<?php echo ExecPhp_POST_WIDGET_SUPPORT; ?>" id="<?php echo ExecPhp_POST_WIDGET_SUPPORT; ?>" value="true" <?php if ($option->get_widget_support()) : ?>checked="checked" <?php endif; ?>/>
								<?php _e('Executing PHP code in text widgets is not restricted to any user. By default users who can modify text widgets will also be able to execute PHP code in text widgets. Unselect this option to generally turn off execution of PHP code in text widgets.', ExecPhp_PLUGIN_ID); ?>

							</label>
						</td>
					</tr>
				</table>
			</fieldset>

			<p class="submit">
				<input type="submit" name="<?php echo ExecPhp_ACTION_UPDATE_OPTIONS; ?>" value="<?php _e('Update Options &raquo;') ?>" />
			</p>
		</form>

		<h2><?php echo sprintf(__('Exec-PHP %s Information', ExecPhp_PLUGIN_ID), ExecPhp_VERSION); ?></h2>
		<form action="" id="<?php echo ExecPhp_ID_INFO_FORM; ?>">
			<p><?php _e('Following are some few informational lists showing which users are allowed to write or execute PHP code in different cases. Allowing to write or execute PHP code can be adjusted by assigning the necessary capabilities to individual users or roles by using a role manager plugin.', ExecPhp_PLUGIN_ID); ?></p>

<?php $this->print_users_of_capability(ExecPhp_CAPABILITY_WIDGETS, ExecPhp_ID_INFO_WIDGETS,
	__('PHP Code in Text Widgets', ExecPhp_PLUGIN_ID),
	sprintf(__('The following list shows which users have the &quot;switch_themes&quot; capability and therefore would be allowed to write and execute PHP code in text widgets <em>in case you have selected the option &quot;Execute PHP code in text widgets&quot;</em> from above.', ExecPhp_PLUGIN_ID), ExecPhp_CAPABILITY_WIDGETS)); ?>

<?php $this->print_users_of_capability(ExecPhp_CAPABILITY_WRITE_ARTICLES, ExecPhp_ID_INFO_WRITE_ARTICLES,
	__('Writing PHP Code in Articles', ExecPhp_PLUGIN_ID),
	sprintf(__('The following list shows which users have the &quot;%s&quot; capability and therefore are allowed to write PHP code in articles.', ExecPhp_PLUGIN_ID), ExecPhp_CAPABILITY_WRITE_ARTICLES)); ?>

<?php $this->print_users_of_capability(ExecPhp_CAPABILITY_EXECUTE_ARTICLES, ExecPhp_ID_INFO_EXECUTE_ARTICLES,
	__('Executing PHP Code in Articles', ExecPhp_PLUGIN_ID),
	sprintf(__('The following list shows which users have the &quot;%s&quot; capability and therefore are allowed to execute PHP code in articles.', ExecPhp_PLUGIN_ID), ExecPhp_CAPABILITY_EXECUTE_ARTICLES)); ?>
		</form>
	</div>
<?php
	}
}
endif;

?>