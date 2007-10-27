<?php

require_once(dirname(__FILE__).'/ajax.php');
require_once(dirname(__FILE__).'/cache.php');
require_once(dirname(__FILE__).'/config_ui.php');
require_once(dirname(__FILE__).'/const.php');
require_once(dirname(__FILE__).'/option.php');
require_once(dirname(__FILE__).'/runtime.php');

// -----------------------------------------------------------------------------
// the ExecPhp_Manager class handles the plugin initialization phase,
// assuring the infrastructure is set up properly
// -----------------------------------------------------------------------------

class ExecPhp_Manager
{
	var $m_status = ExecPhp_STATUS_UNINITIALIZED;
	var $m_cache = NULL;
	var $m_runtime = NULL;
	var $m_config_ui = NULL;
	var $m_ajax = NULL;

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
		load_plugin_textdomain(ExecPhp_PLUGIN_ID, ExecPhp_DIR. '/languages');
		add_filter('init', array(&$this, 'filter_init'));
	}

	function filter_init()
	{
		$this->m_cache =& new ExecPhp_Cache();
		$option =& $this->m_cache->get_option();
		$this->m_status = $option->get_status();
		$this->m_runtime =& new ExecPhp_Runtime($this->m_cache, $this->m_status);
		$this->m_config_ui =& new ExecPhp_ConfigUi($this->m_cache, $this->m_status);
		$this->m_ajax =& new ExecPhP_Ajax();
	}
}

?>