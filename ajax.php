<?php

define('ExecPhp_AJAX_ACTION_USERS_OF_CAPABILITY', 'users_of_capability');
define('ExecPhp_AJAX_POST_CAPABILITY', 'capability');

// -----------------------------------------------------------------------------
// the ExecPhp_Ajax class handles the AJAX communication incoming from the
// ConfigUi for requesting which users are allowed to execute PHP in widgets
// and articles
// -----------------------------------------------------------------------------

class ExecPhp_Ajax
{
	// ---------------------------------------------------------------------------
	// init
	// ---------------------------------------------------------------------------

	function ExecPhp_Ajax()
	{
		add_filter('wp_ajax_'. ExecPhp_AJAX_ACTION_USERS_OF_CAPABILITY,
			array(&$this, 'filter_ajax_users_for_capability'));
	}

	// ---------------------------------------------------------------------------
	// query
	// ---------------------------------------------------------------------------

	function filter_ajax_users_for_capability()
	{
		if (!current_user_can(ExecPhp_CAPABILITY_MANAGE))
			die('-1');

		$capability = attribute_escape(stripslashes($_POST[ExecPhp_AJAX_POST_CAPABILITY]));

		global $wpdb;
		$query = "SELECT ID AS user_id FROM {$wpdb->users} ORDER BY display_name";
		$wpdb->query($query);
		$s = $wpdb->get_results($query);
		if (!is_array($s))
			$s = array();

		$output = '';
		foreach ($s as $i)
		{
			$u =& new WP_User($i->user_id);
			if ($u->has_cap($capability))
			{
				$display_name = $u->data->display_name;
				$output .= "<li>{$display_name}</li>";
			}
		}
		if (empty($output))
			$output = '<p>'. __('No user has this capability assigned to.', ExecPhp_PLUGIN_ID). '</p>';
		else
			$output = "<ul>{$output}</ul>";
		die("$output");
	}
}

?>