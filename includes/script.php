<?php

require_once(dirname(__FILE__).'/const.php');
require_once(dirname(__FILE__).'/l10n.php');

// -----------------------------------------------------------------------------
// the ExecPhp_Script class displays the Exec-PHP javascript if necessary
// -----------------------------------------------------------------------------

if (!class_exists('ExecPhp_Script')) :

define('ExecPhp_ID_L10N_ADMIN', 'execphpAdminL10n');

class ExecPhp_Script
{
	function ExecPhp_Script()
	{
		wp_enqueue_script(ExecPhp_ID_SCRIPT_COMMON, ExecPhp_HOME_URL. '/js/common.js');

		if (current_user_can(ExecPhp_CAPABILITY_EDIT_PLUGINS)
			|| current_user_can(ExecPhp_CAPABILITY_EDIT_USERS))
		{
			wp_enqueue_script(ExecPhp_ID_SCRIPT_ADMIN, ExecPhp_HOME_URL. '/js/admin.js', array('sack'));
		}

		add_action('admin_print_scripts', array(&$this, 'action_admin_print_scripts'));
	}

	// ---------------------------------------------------------------------------
	// hooks
	// ---------------------------------------------------------------------------

	function action_admin_print_scripts()
	{
		$l10n_tab = array(
			'noUserFound' => escape_dquote(__s('No user matching the query.', ExecPhp_PLUGIN_ID)),
			'securityAlertHeading' => escape_dquote(__s('Exec-PHP Security Alert.', ExecPhp_PLUGIN_ID)),
			'securityAlertText' => escape_dquote(__s('The Exec-PHP plugin found a security hole with the configured user rights of this blog. For further information consult the plugin configuration menu or contact your blog administrator.', ExecPhp_PLUGIN_ID)),
			'ajaxScript' => get_option('siteurl'). '/wp-admin/admin-ajax.php',
			'ajaxError' => escape_dquote(__s("Exec-PHP AJAX HTTP error when receiving data: ", ExecPhp_PLUGIN_ID)),
			'l10n_print_after' => 'try{convertEntities('. ExecPhp_ID_L10N_ADMIN. ');}catch(e){};');

		if (function_exists('wp_localize_script'))
			wp_localize_script(ExecPhp_ID_SCRIPT_ADMIN, ExecPhp_ID_L10N_ADMIN, $l10n_tab);
		else
		{
				// WP < 2.2
?>
<script type='text/javascript'>
/* <![CDATA[ */
	<?php echo ExecPhp_ID_L10N_ADMIN; ?> = {
		noUserFound: <?php echo $l10n_tab['noUserFound']; ?>,
		securityAlertHeading: <?php echo $l10n_tab['securityAlertHeading']; ?>,
		securityAlertText: <?php echo $l10n_tab['securityAlertText']; ?>,
		ajaxScript: <?php echo $l10n_tab['ajaxScript']; ?>,
		ajaxError: <?php echo $l10n_tab['ajaxError']; ?>
	}
	try{convertEntities(<?php echo ExecPhp_ID_L10N_ADMIN; ?>);}catch(e){};
/* ]]> */
</script>
<?php
		}
	}

	// ---------------------------------------------------------------------------
	// tools
	// ---------------------------------------------------------------------------

	function print_message($heading, $text)
	{
		$heading = escape_dquote($heading);
		$text = escape_dquote($text);
?>
	<script type="text/javascript">
		//<![CDATA[
		ExecPhp_setMessage("<?php echo $heading; ?>", "<?php echo $text; ?>");
		//]]>
	</script>
<?php
	}
}
endif;

?>