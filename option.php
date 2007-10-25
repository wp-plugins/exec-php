<?php

require_once(dirname(__FILE__).'/const.php');

define('ExecPhp_OPTION_VERSION', 'version');
define('ExecPhp_OPTION_WIDGET_SUPPORT', 'widget_support');
define('ExecPhp_OPTION_HAS_OLD_STYLE', 'exec-php_has_old_style');
define('ExecPhp_OPTION_IGNORE_OLD_STYLE_WARNING', 'exec-php_ignore_old_style_warning');

// -----------------------------------------------------------------------------
// the ExecPhp_Option class handles the loading and storing of the
// plugin option including all needed conversion routines during upgrade
// -----------------------------------------------------------------------------

class ExecPhp_Option
{
	var $m_status = ExecPhp_STATUS_UNINITIALIZED;
	var $m_version = ExecPhp_VERSION;

	// default option values will be set during load()
	var $m_widget_support;

	// ---------------------------------------------------------------------------
	// init
	// ---------------------------------------------------------------------------

	function ExecPhp_Option()
	{
		$this->m_status = $this->upgrade();
	}

	// ---------------------------------------------------------------------------
	// option handling
	// ---------------------------------------------------------------------------

	// Upgrades plugin from previous versions or even installs it
	function upgrade()
	{
		$old_version = $this->detect_plugin_version();
		while ($old_version != ExecPhp_VERSION)
		{
			$this->load();
			if ($old_version == '0')
			{
				// this is first installation of the plugin or upgrade from a version
				// prior to 4.0;
				// still needed for deletion from the database - these are obsolete
				// since version 3.1
				delete_option(ExecPhp_OPTION_HAS_OLD_STYLE);
				delete_option(ExecPhp_OPTION_IGNORE_OLD_STYLE_WARNING);

				// install capabilities only at the first installation
				$this->install_capability();
				$old_version = '4.0';
			}
			else
			{
				// very bad; programming error or system is messed up
				$this->m_version = $old_version;
				return ExecPhp_STATUS_PLUGIN_VERSION_MISMATCH;
			}
			$this->m_version = $old_version;
			$this->save();
		}
		$this->load();
		return ExecPhp_STATUS_OKAY;
	}

	function save()
	{
		// introduced in 4.0
		$option[ExecPhp_OPTION_VERSION] = $this->m_version;

		// introduced in 4.0
		$option[ExecPhp_OPTION_WIDGET_SUPPORT] = $this->m_widget_support;

		update_option(ExecPhp_PLUGIN_ID, $option);
	}

	function load()
	{
		global $wp_version;

		$option = get_option(ExecPhp_PLUGIN_ID);

		// introduced in 4.0
		if (isset($option[ExecPhp_OPTION_WIDGET_SUPPORT]))
			$this->m_widget_support = $option[ExecPhp_OPTION_WIDGET_SUPPORT];
		else
			$this->m_widget_support = true;
	}

	// ---------------------------------------------------------------------------
	// tools
	// ---------------------------------------------------------------------------

	function detect_plugin_version()
	{
		$option = get_option(ExecPhp_PLUGIN_ID);
		if ($option === false)
			$version = '0';
		else
			$version = $option[ExecPhp_OPTION_VERSION];
		return $version;
	}

	function install_capability()
	{
		// be sure standard roles are available, these may be deleted or
		// renamed by the blog administrator
		$role = get_role('administrator');
		if ($role !== NULL)
			$role->add_cap(ExecPhp_CAPABILITY);
	}

	// ---------------------------------------------------------------------------
	// access
	// ---------------------------------------------------------------------------

	function set_from_POST()
	{
		$this->m_widget_support
			= isset($_POST[ExecPhp_POST_WIDGET_SUPPORT]);
	}

	function get_status()
	{
		return $this->m_status;
	}

	function get_version()
	{
		return $this->m_version;
	}

	function get_widget_support()
	{
		return $this->m_widget_support;
	}
}

?>