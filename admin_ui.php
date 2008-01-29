<?php

require_once(dirname(__FILE__).'/const.php');

// -----------------------------------------------------------------------------
// the ExecPhp_AdminUi class provides functionality common to all displayed
// admin menus
// -----------------------------------------------------------------------------

// use this guard to avoid error messages in WP admin panel if plugin
// is disabled because of a version conflict but you still try to reload
// the plugins config interface
if (!class_exists('ExecPhp_AdminUi')) :
class ExecPhp_AdminUi
{
	var $m_cache;

	// ---------------------------------------------------------------------------
	// init
	// ---------------------------------------------------------------------------

	function ExecPhp_AdminUi(&$cache, $status)
	{
		$this->m_cache = $cache;
		$this->toggle_action($status);
		add_action('admin_head', array(&$this, 'action_admin_head'));
		add_action('admin_footer', array(&$this, 'action_admin_footer'));
	}

	// ---------------------------------------------------------------------------
	// hooks
	// ---------------------------------------------------------------------------

	function action_admin_head()
	{
		if (function_exists('wp_print_scripts'))
			wp_print_scripts(array('sack'));
?>
	<script type="text/javascript">
		//<![CDATA[
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
		if (current_user_can(ExecPhp_CAPABILITY_EDIT_PLUGINS)
			|| current_user_can(ExecPhp_CAPABILITY_EDIT_USERS))
		{
?>
	<script type="text/javascript">
		//<![CDATA[
		var g_execphp_ajax = new sack("<?php bloginfo('wpurl'); ?>/wp-admin/admin-ajax.php");

		function ExecPhp_ajaxCompletion()
		{
			var edit_others_php;
			var switch_themes;
			var exec_php;
			var container;
			var security_hole = true;

			eval(g_execphp_ajax.response);

			container = document.getElementById("<?php echo ExecPhp_ID_INFO_EXECUTE_ARTICLES; ?>");
			try {
				if (!exec_php.length)
					exec_php = "<p><?php _e('No user matching the query.', ExecPhp_PLUGIN_ID); ?></p>";
				container.innerHTML = exec_php;
			} catch (e) {;}

			container = document.getElementById("<?php echo ExecPhp_ID_INFO_WIDGETS; ?>");
			try {
				if (!switch_themes.length)
					switch_themes = "<p><?php _e('No user matching the query.', ExecPhp_PLUGIN_ID); ?></p>";
				container.innerHTML = switch_themes;
			} catch (e) {;}

			if (!edit_others_php.length)
			{
				edit_others_php = "<p><?php _e('No user matching the query.', ExecPhp_PLUGIN_ID); ?></p>";
				security_hole = false;
			}
			container = document.getElementById("<?php echo ExecPhp_ID_INFO_SECURITY_HOLE; ?>");
			try {
				container.innerHTML = edit_others_php;
				if (security_hole)
				{
					container = document.getElementById("<?php echo ExecPhp_ID_INFO_SECURITY_HOLE; ?>-container");
					container.style.color = "#000000";
					container.style.backgroundColor = "red";
				}
			} catch (e) {;}

			// finally warn admin blog wide about security hole
			if (security_hole)
			{
				heading = "<?php _e('Exec-PHP Security Alert.', ExecPhp_PLUGIN_ID); ?>";
				text = "<?php _e('The Exec-PHP plugin found a security hole with the configured user rights of this blog. For further information consult the plugin options menu or contact your blog administrator.', ExecPhp_PLUGIN_ID); ?>";
				ExecPhp_setMessage("adminmenu", heading, text)
			}
		}

		function ExecPhp_getUsersOfCapability()
		{
			g_execphp_ajax.setVar("cookie", document.cookie);
			g_execphp_ajax.setVar("action", "<?php echo ExecPhp_ACTION_REQUEST_USERS; ?>");
			g_execphp_ajax.onError = function() {alert('<?php _e("Exec-PHP AJAX HTTP error", ExecPhp_PLUGIN_ID); ?>')};
			g_execphp_ajax.onCompletion = ExecPhp_ajaxCompletion;
			g_execphp_ajax.runAJAX();
		}
		//]]>
	</script>

	<style type="text/css">
		#<?php echo ExecPhp_ID_INFO_SECURITY_HOLE; ?> li,
		#<?php echo ExecPhp_ID_INFO_WIDGETS; ?> li,
		#<?php echo ExecPhp_ID_INFO_EXECUTE_ARTICLES; ?> li {
			float: left;
			width: 20em;
			line-height: 1em;
		}

		#<?php echo ExecPhp_ID_INFO_SECURITY_HOLE; ?> p,
		#<?php echo ExecPhp_ID_INFO_WIDGETS; ?> p,
		#<?php echo ExecPhp_ID_INFO_EXECUTE_ARTICLES; ?> p {
			text-align: center;
		}

		#<?php echo ExecPhp_ID_INFO_SECURITY_HOLE; ?> p *,
		#<?php echo ExecPhp_ID_INFO_WIDGETS; ?> p *,
		#<?php echo ExecPhp_ID_INFO_EXECUTE_ARTICLES; ?> p * {
			vertical-align: middle;
		}
	</style>
<?php
		}
	}

	function action_admin_footer()
	{
		if (current_user_can(ExecPhp_CAPABILITY_EDIT_PLUGINS)
			|| current_user_can(ExecPhp_CAPABILITY_EDIT_USERS))
		{
?>
	<script type="text/javascript">
		//<![CDATA[
		ExecPhp_getUsersOfCapability();
		//]]>
	</script>
<?php
		}
	}

	function action_admin_footer_plugin_version()
	{
		$option =& $this->m_cache->get_option();
		$heading = __('Exec-PHP Error.', ExecPhp_PLUGIN_ID);
		$text = sprintf(__('No necessary upgrade of the the Exec-PHP plugin could be performed. PHP code in your articles or widgets may be viewable to your blog readers. This is plugin version %1$s, previously there was version %2$s installed. Downgrading from a newer version to an older version of the plugin is not supported.', ExecPhp_PLUGIN_ID)
			, ExecPhp_VERSION, $option->get_version());
		$this->print_admin_message($heading, $text);
	}

	function action_admin_footer_unknown()
	{
		$option =& $this->m_cache->get_option();
		$heading = __('Exec-PHP Error.', ExecPhp_PLUGIN_ID);
		$text = sprintf(__('An unknown error (%s) occured during execution of the Exec-PHP plugin. PHP code in your articles or widgets may be viewable to your blog readers. This error should never happen if you use the plugin with a compatible WordPress version and installed it as described in the documentation.', ExecPhp_PLUGIN_ID)
			, $option->get_status());
		$this->print_admin_message($heading, $text);
	}

	function toggle_action($status)
	{
		if ($status == ExecPhp_STATUS_PLUGIN_VERSION_MISMATCH)
			add_action('admin_footer', array(&$this, 'action_admin_footer_plugin_version'));
		else
			remove_action('admin_footer', array(&$this, 'action_admin_footer_plugin_version'));

		if ($status != ExecPhp_STATUS_OKAY
			&& $status != ExecPhp_STATUS_PLUGIN_VERSION_MISMATCH)
			add_action('admin_footer', array(&$this, 'action_admin_footer_unknown'));
		else
			remove_action('admin_footer', array(&$this, 'action_admin_footer_unknown'));
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
}
endif;

?>