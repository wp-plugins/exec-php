<?php

require_once(dirname(__FILE__).'/admin_ui.php');
require_once(dirname(__FILE__).'/cache.php');
require_once(dirname(__FILE__).'/const.php');

// -----------------------------------------------------------------------------
// the ExecPhp_ConfigUi class displays the config interface in the
// admin menu
// -----------------------------------------------------------------------------

// use this guard to avoid error messages in WP admin panel if plugin
// is disabled because of a version conflict but you still try to reload
// the plugins config interface
if (!class_exists('ExecPhp_ConfigUi')) :
class ExecPhp_ConfigUi
{
	var $m_cache;
	var $m_admin_ui;

	// ---------------------------------------------------------------------------
	// init
	// ---------------------------------------------------------------------------

	// Sets up the Exec-Php config menu
	function ExecPhp_ConfigUi(&$cache, &$admin_ui)
	{
		$this->m_cache = $cache;
		$this->m_admin_ui = $admin_ui;
		add_action('admin_menu', array(&$this, 'action_admin_menu'));
	}

	// ---------------------------------------------------------------------------
	// hooks
	// ---------------------------------------------------------------------------

	function action_admin_menu()
	{
		add_submenu_page('options-general.php',
			__('Exec-PHP Options', ExecPhp_PLUGIN_ID),
			__('Exec-PHP', ExecPhp_PLUGIN_ID),
			ExecPhp_CAPABILITY_EDIT_PLUGINS, __FILE__,
			array(&$this, 'submenu_page_option_general'));
	}

	// ---------------------------------------------------------------------------
	// tools
	// ---------------------------------------------------------------------------

	function print_request_users($display_id, $legend, $introduction)
	{
?>
			<fieldset class="options" id="<?php echo $display_id; ?>-container">
				<legend><?php echo $legend; ?></legend>
				<p><?php echo $introduction; ?></p>
				<div id="<?php echo $display_id; ?>">
					<?php _e('The list can not be displayed because you may have disabled Javascript or your browser does not support Javascript.', ExecPhp_PLUGIN_ID); ?>

				</div>
				<script type="text/javascript">
					//<![CDATA[
					document.getElementById('<?php echo $display_id; ?>').innerHTML =
						'<p><img src="<?php echo get_option('siteurl'). '/'. ExecPhp_DIR. '/images/progress.gif'; ?>" alt="<?php _e('An animated icon signaling that this information is still be loaded.', ExecPhp_PLUGIN_ID); ?>" /> <?php _e('Loading user information...', ExecPhp_PLUGIN_ID); ?></p>';
					//]]>
				</script>
			</fieldset>
<?php
	}

	// ---------------------------------------------------------------------------
	// interface
	// ---------------------------------------------------------------------------

	// Exec-PHP configuration page
	function submenu_page_option_general()
	{
		global $wpdb;
		global $wp_version;

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
		$this->m_admin_ui->toggle_action($option->get_status());
?>
	<div class="wrap">
<?php if (version_compare($wp_version, '2.2') >= 0) : ?>
		<h2><?php _e('Exec-PHP Options', ExecPhp_PLUGIN_ID); ?></h2>
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

<?php endif; ?>
		<h2><?php _e('Exec-PHP Information', ExecPhp_PLUGIN_ID); ?></h2>
		<form action="" id="<?php echo ExecPhp_ID_INFO_FORM; ?>">
			<p><?php _e('The following lists show which users are allowed to write or execute PHP code in different cases. Allowing to write or execute PHP code can be adjusted by assigning the necessary capabilities to individual users or roles by using a role manager plugin.', ExecPhp_PLUGIN_ID); ?></p>
<?php $this->print_request_users(ExecPhp_ID_INFO_SECURITY_HOLE, __('Security Hole', ExecPhp_PLUGIN_ID),
	sprintf(__('The following list shows which users have either or both of the &quot;%1$s&quot; or &quot;%2$s&quot; capability and are allowed to change others PHP code by having the &quot;%3$s&quot; capability but do not have the &quot;%4$s&quot; capability for themself. This is a security hole, because the listed users can write and execute PHP code in articles of other users although they are not supposed to execute PHP code at all.', ExecPhp_PLUGIN_ID), ExecPhp_CAPABILITY_EDIT_OTHERS_POSTS, ExecPhp_CAPABILITY_EDIT_OTHERS_PAGES, ExecPhp_CAPABILITY_EDIT_OTHERS_PHP, ExecPhp_CAPABILITY_EXECUTE_ARTICLES)); ?>

<?php if (version_compare($wp_version, '2.2') >= 0) : ?>
<?php $this->print_request_users(ExecPhp_ID_INFO_WIDGETS, __('Executing PHP Code in Text Widgets', ExecPhp_PLUGIN_ID),
	sprintf(__('The following list shows which users have the &quot;%s&quot; capability and therefore are allowed to write and execute PHP code in text widgets. In case you have deselected the option &quot;Execute PHP code in text widgets&quot; from above, this list will appear empty.', ExecPhp_PLUGIN_ID), ExecPhp_CAPABILITY_EXECUTE_WIDGETS)); ?>

<?php endif; ?>
<?php $this->print_request_users(ExecPhp_ID_INFO_EXECUTE_ARTICLES, __('Executing PHP Code in Articles', ExecPhp_PLUGIN_ID),
	sprintf(__('The following list shows which users have the &quot;%s&quot; capability and therefore are allowed to execute PHP code in articles.', ExecPhp_PLUGIN_ID), ExecPhp_CAPABILITY_EXECUTE_ARTICLES)); ?>
		</form>
	</div>
<?php
	}
}
endif;

?>