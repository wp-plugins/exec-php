<?php

require_once(dirname(__FILE__).'/ajax.php');
require_once(dirname(__FILE__).'/cache.php');
require_once(dirname(__FILE__).'/const.php');
require_once(dirname(__FILE__).'/config_ui.php');
require_once(dirname(__FILE__).'/l10n.php');
require_once(dirname(__FILE__).'/script.php');
require_once(dirname(__FILE__).'/style.php');
require_once(dirname(__FILE__).'/user_ui.php');
require_once(dirname(__FILE__).'/write_ui.php');

// -----------------------------------------------------------------------------
// the ExecPhp_Admin class provides functionality common to all displayed
// admin menus
// -----------------------------------------------------------------------------

// use this guard to avoid error messages in WP admin panel if plugin
// is disabled because of a version conflict but you still try to reload
// the plugins config interface
if (!class_exists('ExecPhp_Admin')) :
class ExecPhp_Admin
{
	var $m_cache = NULL;
	var $m_ajax = NULL;
	var $m_script = NULL;
	var $m_style = NULL;
	var $m_write_ui = NULL;
	var $m_user_ui = NULL;
	var $m_config_ui = NULL;

	// ---------------------------------------------------------------------------
	// init
	// ---------------------------------------------------------------------------

	function ExecPhp_Admin(&$cache)
	{
		global $wp_version;

		if (version_compare($wp_version, '2.1.dev') < 0)
			return;

		$this->m_cache =& $cache;

		// ajax server needs to be installed without is_admin() check
		$this->m_ajax =& new ExecPhp_Ajax($this->m_cache);
		if (!is_admin())
			return;

		if (version_compare($wp_version, '2.6.dev') >= 0)
			load_plugin_textdomain(ExecPhp_PLUGIN_ID, false, ExecPhp_HOMEDIR. '/languages');
		else
			load_plugin_textdomain(ExecPhp_PLUGIN_ID, ExecPhp_PLUGINDIR. '/'. ExecPhp_HOMEDIR. '/languages');

		$this->m_script =& new ExecPhp_Script();
		$this->m_style =& new ExecPhp_Style();
		$this->m_write_ui =& new ExecPhp_WriteUi($this->m_cache, $this->m_script);
		$this->m_user_ui =& new ExecPhp_UserUi($this->m_cache);
		$this->m_config_ui =& new ExecPhp_ConfigUi($this->m_cache, $this->m_script);

		add_action('admin_notices', array(&$this, 'action_admin_notices'), 5);
		add_action('admin_footer', array(&$this, 'action_admin_footer'));
	}

	// ---------------------------------------------------------------------------
	// hooks
	// ---------------------------------------------------------------------------

	function action_admin_notices()
	{
?>
<div id="<?php echo ExecPhp_ID_MESSAGE; ?>"></div>
<?php
	}

	function action_admin_footer()
	{
		if (current_user_can(ExecPhp_CAPABILITY_EDIT_PLUGINS)
			|| current_user_can(ExecPhp_CAPABILITY_EDIT_USERS))
		{
?>
	<script type="text/javascript">
		//<![CDATA[
		ExecPhp_requestUser();
		//]]>
	</script>
<?php
		}
	}
}
endif;

?>