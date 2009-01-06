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
	var $m_l10n_tab = NULL;

	function ExecPhp_Script()
	{
		$this->m_l10n_tab = array(
			'noUserFound' => escape_dquote(__s('No user matching the query.', ExecPhp_PLUGIN_ID)),
			'securityAlertHeading' => escape_dquote(__s('Exec-PHP Security Alert.', ExecPhp_PLUGIN_ID)),
			'securityAlertText' => escape_dquote(__s('The Exec-PHP plugin found a security hole with the configured user rights of this blog. For further information consult the plugin configuration menu or contact your blog administrator.', ExecPhp_PLUGIN_ID)),
			'requestFile' => get_option('siteurl'). '/wp-admin/admin-ajax.php',
			'ajaxError' => escape_dquote(__s("Exec-PHP AJAX HTTP error when receiving data: ", ExecPhp_PLUGIN_ID)),
			'l10n_print_after' => 'try{convertEntities('. ExecPhp_ID_L10N_ADMIN. ');}catch(e){};');

		if (function_exists('wp_enqueue_script'))
			wp_enqueue_script(ExecPhp_ID_SCRIPT_COMMON, ExecPhp_HOME_URL. '/js/common.js');
		else
			// WP < 2.1
			add_action('admin_head', array(&$this, 'action_admin_head_script'));

		global $wp_version;
		if (version_compare($wp_version, '2.1.dev') < 0)
			return;

		if (current_user_can(ExecPhp_CAPABILITY_EDIT_PLUGINS)
			|| current_user_can(ExecPhp_CAPABILITY_EDIT_USERS))
		{
			wp_enqueue_script(ExecPhp_ID_SCRIPT_ADMIN, ExecPhp_HOME_URL. '/js/admin.js', array('sack'));
		}
		add_action('wp_print_scripts', array(&$this, 'action_wp_print_scripts'));
	}

	// ---------------------------------------------------------------------------
	// hooks
	// ---------------------------------------------------------------------------

	function action_wp_print_scripts()
	{
		if (function_exists('wp_localize_script'))
			wp_localize_script(ExecPhp_ID_SCRIPT_ADMIN, ExecPhp_ID_L10N_ADMIN, $this->m_l10n_tab);
		else
			// WP < 2.2
			add_action('admin_head', array(&$this, 'action_admin_head_l10n'));
	}

	function action_admin_head_l10n()
	{
?>
<script type='text/javascript'>
/* <![CDATA[ */
	<?php echo ExecPhp_ID_L10N_ADMIN; ?> = {
		noUserFound: "<?php echo $this->m_l10n_tab['noUserFound']; ?>",
		securityAlertHeading: "<?php echo $this->m_l10n_tab['securityAlertHeading']; ?>",
		securityAlertText: "<?php echo $this->m_l10n_tab['securityAlertText']; ?>",
		requestFile: "<?php echo $this->m_l10n_tab['requestFile']; ?>",
		ajaxError: "<?php echo $this->m_l10n_tab['ajaxError']; ?>" }
	try{convertEntities(<?php echo ExecPhp_ID_L10N_ADMIN; ?>);}catch(e){};
/* ]]> */
</script>
<?php
	}

	function action_admin_head_script()
	{
?>
<script type='text/javascript' src='<?php echo ExecPhp_HOME_URL; ?>/js/common.js'></script>
<?php
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