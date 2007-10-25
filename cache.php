<?php

require_once(dirname(__FILE__).'/option.php');

// -----------------------------------------------------------------------------
// the ExecPhp_Cache serves as a cache for the option
// -----------------------------------------------------------------------------

class ExecPhp_Cache
{
	var $m_option = NULL;

	// ---------------------------------------------------------------------------
	// init
	// ---------------------------------------------------------------------------

	function ExecPhp_Cache()
	{
	}

	// ---------------------------------------------------------------------------
	// access
	// ---------------------------------------------------------------------------

	function &get_option()
	{
		if (!isset($this->m_option))
			// this will generate warnings with error_reporting(E_STRICT) using PHP5
			// see http://www.php.net/manual/en/language.references.whatdo.php
			$this->m_option =& new ExecPhp_Option();
		return $this->m_option;
	}
}

?>