<?php

require_once(dirname(__FILE__).'/admin_ui.php');
require_once(dirname(__FILE__).'/cache.php');
require_once(dirname(__FILE__).'/const.php');

// -----------------------------------------------------------------------------
// the ExecPhp_WriteUi class displays the user warnings in case of false
// configuration
// -----------------------------------------------------------------------------

// use this guard to avoid error messages in WP admin panel if plugin
// is disabled because of a version conflict but you still try to reload
// the plugins config interface
if (!class_exists('ExecPhp_WriteUi')) :
class ExecPhp_WriteUi
{
	var $m_cache;
	var $m_admin_ui;

	// ---------------------------------------------------------------------------
	// init
	// ---------------------------------------------------------------------------

	function ExecPhp_WriteUi(&$cache, &$admin_ui)
	{
		$this->m_cache = $cache;
		$this->m_admin_ui = $admin_ui;
		add_filter('edit_form_advanced', array(&$this, 'filter_edit_form_advanced'));
		add_filter('sidebar_admin_page', array(&$this, 'filter_sidebar_admin_page'));
	}

	// ---------------------------------------------------------------------------
	// filter
	// ---------------------------------------------------------------------------

	function filter_edit_form_advanced()
	{
		if ($this->rtfm_article())
		{
			$heading = __('Exec-PHP Conversion Warning.', ExecPhp_PLUGIN_ID);
			$text = sprintf(__('Saving this article will render all contained PHP code permanently unuseful. Ignore this warning in case this article does not contain PHP code. <a href="%s">Read the Exec-PHP documentation if you are unsure what to do next</a>.', ExecPhp_PLUGIN_ID)
				, get_option('siteurl'). '/'. ExecPhp_DIR. '/docs/readme.html#execute_php');
			$this->m_admin_ui->print_user_message($heading, $text);
		}
	}

	function filter_sidebar_admin_page()
	{
		if ($this->rtfm_widget())
		{
			$heading = __('Exec-PHP Conversion Warning.', ExecPhp_PLUGIN_ID);
			$text = sprintf(__('Saving the widgets will render all contained PHP code permanently unuseful. Ignore this warning in case the text widgets do not contain PHP code. <a href="%s">Read the Exec-PHP documentation if you are unsure what to do next</a>.', ExecPhp_PLUGIN_ID)
				, get_option('siteurl'). '/'. ExecPhp_DIR. '/docs/readme.html#execute_php');
			$this->m_admin_ui->print_user_message($heading, $text);
		}
	}

	// ---------------------------------------------------------------------------
	// tools
	// ---------------------------------------------------------------------------

	// checks whether the author / editor has read the documentation
	function rtfm_article()
	{
		global $post;

		$current_user = wp_get_current_user();
		if (!isset($post->author) || $post->post_author == $current_user->ID)
		{
			// the editor is equal to the writer of the article
			if (!current_user_can(ExecPhp_CAPABILITY_EXECUTE_ARTICLES))
				return false;
			if (!current_user_can(ExecPhp_CAPABILITY_WRITE_PHP))
				return true;
		}
		else
		{
			// the editor is different to the writer of the article
			$poster = new WP_User($post->post_author);
			if (!$poster->has_cap(ExecPhp_CAPABILITY_EXECUTE_ARTICLES))
				return false;
			// no check for posters write cap because, the editor may want to
			// insert code after the poster created the article
		}
		if (!current_user_can(ExecPhp_CAPABILITY_WRITE_PHP))
			return true;
		if (user_can_richedit())
			return true;
		if (get_option('use_balanceTags'))
			return true;
		return false;
	}

	// checks whether the admin has read the documentation
	function rtfm_widget()
	{
		$option =& $this->m_cache->get_option();
		if (!$option->get_widget_support())
			return false;
		if (!current_user_can(ExecPhp_CAPABILITY_WRITE_PHP))
			return true;
		return false;
	}
}
endif;

?>