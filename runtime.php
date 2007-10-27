<?php

require_once(dirname(__FILE__).'/cache.php');
require_once(dirname(__FILE__).'/const.php');

// -----------------------------------------------------------------------------
// the ExecPhp_Runtime class handles the execution of PHP code during
// access to the articles content or widget including checks against
// the exec_php capability or plugin options respectivly
// -----------------------------------------------------------------------------

class ExecPhp_Runtime
{
	var $m_cache = NULL;

	// ---------------------------------------------------------------------------
	// init
	// ---------------------------------------------------------------------------

	function ExecPhp_Runtime(&$cache, $status)
	{
		$this->m_cache = $cache;

		if ($status != ExecPhp_STATUS_OKAY)
			return;

		add_filter('the_content', array(&$this, 'filter_user_content'), 1);
		add_filter('the_content_rss', array(&$this, 'filter_user_content'), 1);
		add_filter('the_excerpt', array(&$this, 'filter_user_content'), 1);
		add_filter('the_excerpt_rss', array(&$this, 'filter_user_content'), 1);
		add_filter('widget_text', array(&$this, 'filter_widget_content'), 1);
	}

	// ---------------------------------------------------------------------------
	// tools
	// ---------------------------------------------------------------------------

	function eval_php($content)
	{
		// to be compatible with older PHP4 installations
		// don't use fancy ob_XXX shortcut functions
		ob_start();
		eval("?>$content<?php ");
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}

	// ---------------------------------------------------------------------------
	// runtime filter
	// ---------------------------------------------------------------------------

	function filter_user_content($content)
	{
		global $post;

		// check whether the article author is allowed to execute PHP code
		if (!isset($post) || !isset($post->post_author))
			return $content;
		$poster = new WP_User($post->post_author);
		if (!$poster->has_cap(ExecPhp_CAPABILITY_EXECUTE_ARTICLES))
			return $content;

		return $this->eval_php($content);
	}

	function filter_widget_content($content)
	{
		// check whether the admin has configured widget support
		$option =& $this->m_cache->get_option();
		if (!$option->get_widget_support())
			return $content;

		return $this->eval_php($content);
	}
}

?>