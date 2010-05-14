<?php

require_once(dirname(__FILE__).'/const.php');

// -----------------------------------------------------------------------------
// the ExecPhp_Option class handles the loading and storing of the
// plugin options including all needed conversion routines during upgrade
// -----------------------------------------------------------------------------

if (!class_exists('ExecPhp_Option')) :

define('ExecPhp_OPTION_VERSION', 'version');
define('ExecPhp_OPTION_WIDGET_SUPPORT', 'widget_support');
define('ExecPhp_OPTION_HAS_OLD_STYLE', 'exec-php_has_old_style');
define('ExecPhp_OPTION_IGNORE_OLD_STYLE_WARNING', 'exec-php_ignore_old_style_warning');

class ExecPhp_Option
{
	var $m_status = ExecPhp_STATUS_UNINITIALIZED;
	var $m_version = ExecPhp_VERSION;

	// default option values will be set during load()
	var $m_widget_support = true;

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
		while (version_compare($old_version, ExecPhp_VERSION) < 0) {
			$this->load();
			if (version_compare($old_version, '4.0.dev') < 0) {
				$this->upgrade_to_4_0();
				$old_version = '4.0';
			} else if (version_compare($old_version, '4.1.dev') < 0) {
				$old_version = '4.1';
			} else if (version_compare($old_version, '4.2.dev') < 0) {
				$this->upgrade_to_4_2();
				$old_version = '4.2';
			} else if (version_compare($old_version, '4.3.dev') < 0) {
				$old_version = '4.3';
			} else if (version_compare($old_version, '4.4.dev') < 0) {
				$old_version = '4.4';
			} else if (version_compare($old_version, '4.5.dev') < 0) {
				$old_version = '4.5';
			} else if (version_compare($old_version, '4.6.dev') < 0) {
				$old_version = '4.6';
			} else if (version_compare($old_version, '4.7.dev') < 0) {
				$old_version = '4.7';
			} else if (version_compare($old_version, '4.8.dev') < 0) {
				$old_version = '4.8';
			} else if (version_compare($old_version, '4.9.dev') < 0) {
				$old_version = '4.9';
			} else if (version_compare($old_version, '4.10.dev') < 0) {
				$old_version = '4.10';
			} else {
				die('Exec-PHP: There is no upgrade path to your current Exec-PHP version. Please upload the plugin again or contact the author.');
			}
			$this->m_version = $old_version;
			$this->save();
		}
		$this->load();
		return ExecPhp_STATUS_OKAY;
	}

	function uninstall()
	{
		delete_option(ExecPhp_PLUGIN_ID, $option);

		$roles = new WP_Roles();
		foreach ($roles->role_objects as $role) {
			$role->remove_cap(ExecPhp_CAPABILITY_EXECUTE_ARTICLES);
			$role->remove_cap(ExecPhp_CAPABILITY_EDIT_OTHERS_PHP);
		}
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
		$option = get_option(ExecPhp_PLUGIN_ID);

		// introduced in 4.0
		if (isset($option[ExecPhp_OPTION_WIDGET_SUPPORT])) {
			$this->m_widget_support = $option[ExecPhp_OPTION_WIDGET_SUPPORT];
		} else {
			$this->m_widget_support = true;
		}
	}

	// ---------------------------------------------------------------------------
	// tools
	// ---------------------------------------------------------------------------

	function detect_plugin_version()
	{
		$option = get_option(ExecPhp_PLUGIN_ID);
		if ($option === false) {
			$version = '0';
		} else {
			$version = $option[ExecPhp_OPTION_VERSION];
		}
		// we are allowing downgrading; because we do not know if any upgrade
		// between this and the downgraded version changed or deleted any options
		// we are starting from scratch
		if (version_compare(ExecPhp_VERSION, $version) < 0) {
			$version = 0;
		}
		return $version;
	}

	function upgrade_to_4_0()
	{
		// this is first installation of the plugin or upgrade from a version
		// prior to 4.0;
		// still needed for deletion from the database - these are obsolete
		// since version 3.1
		delete_option(ExecPhp_OPTION_HAS_OLD_STYLE);
		delete_option(ExecPhp_OPTION_IGNORE_OLD_STYLE_WARNING);

		// be sure standard roles are available, these may be deleted or
		// renamed by the blog administrator
		$role = get_role('administrator');
		if ($role !== NULL) {
			$role->add_cap(ExecPhp_CAPABILITY_EXECUTE_ARTICLES);
		}
	}

	function upgrade_to_4_2()
	{
		// be sure standard roles are available, these may be deleted or
		// renamed by the blog administrator
		$role = get_role('administrator');
		if ($role !== NULL) {
			$role->add_cap(ExecPhp_CAPABILITY_EDIT_OTHERS_PHP);
		}
	}

	// ---------------------------------------------------------------------------
	// access
	// ---------------------------------------------------------------------------

	function set_from_POST()
	{
		$this->m_widget_support = isset($_POST[ExecPhp_POST_WIDGET_SUPPORT]);
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
endif;

?>