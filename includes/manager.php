<?php

require_once(dirname(__FILE__).'/admin_ui.php');
require_once(dirname(__FILE__).'/ajax.php');
require_once(dirname(__FILE__).'/cache.php');
require_once(dirname(__FILE__).'/config_ui.php');
require_once(dirname(__FILE__).'/const.php');
require_once(dirname(__FILE__).'/option.php');
require_once(dirname(__FILE__).'/runtime.php');
require_once(dirname(__FILE__).'/user_ui.php');
require_once(dirname(__FILE__).'/write_ui.php');

// -----------------------------------------------------------------------------
// the ExecPhp_Manager class handles the plugin initialization phase,
// assuring the infrastructure is set up properly
// -----------------------------------------------------------------------------

if (!class_exists('ExecPhp_Manager')) :
class ExecPhp_Manager
{
	var $m_status = ExecPhp_STATUS_UNINITIALIZED;
	var $m_cache = NULL;
	var $m_runtime = NULL;
	var $m_ajax = NULL;
	var $m_admin_ui = NULL;
	var $m_write_ui = NULL;
	var $m_user_ui = NULL;
	var $m_config_ui = NULL;

	// ---------------------------------------------------------------------------
	// init
	// ---------------------------------------------------------------------------

	function ExecPhp_Manager()
	{
		// Just in case someone's loaded up the page standalone for whatever reason,
		// make sure it doesn't crash in an too ugly way
		global $wp_version;
		if (!isset($wp_version))
			die('This page must be loaded as part of WordPress');
		add_action('init', array(&$this, 'action_init'));
	}

	// ---------------------------------------------------------------------------
	// hooks
	// ---------------------------------------------------------------------------

	function action_init()
	{
		global $wp_version;

		load_plugin_textdomain(ExecPhp_PLUGIN_ID, ExecPhp_DIR. '/languages');

		$this->m_cache =& new ExecPhp_Cache();
		$option =& $this->m_cache->get_option();
		$this->m_status = $option->get_status();
		$this->m_runtime =& new ExecPhp_Runtime($this->m_cache);

		if (version_compare($wp_version, '2.1') < 0)
			return;

		$this->m_ajax =& new ExecPhP_Ajax($this->m_cache);
		$this->m_admin_ui =& new ExecPHP_AdminUi($this->m_cache, $this->m_status);
		$this->m_write_ui =& new ExecPhp_WriteUi($this->m_cache, $this->m_admin_ui);
		$this->m_user_ui =& new ExecPhp_UserUi($this->m_cache);
		$this->m_config_ui =& new ExecPhp_ConfigUi($this->m_cache, $this->m_admin_ui);
	}
}
endif;

?>