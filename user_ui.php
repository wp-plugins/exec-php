<?php

require_once(dirname(__FILE__).'/cache.php');
require_once(dirname(__FILE__).'/const.php');

// -----------------------------------------------------------------------------
// the ExecPhp_UserUi class shows user specific options in the user profile
// -----------------------------------------------------------------------------

// use this guard to avoid error messages in WP admin panel if plugin
// is disabled because of a version conflict but you still try to reload
// the plugins config interface
if (!class_exists('ExecPhp_UserUi')) :
class ExecPhp_UserUi
{
	var $m_cache;

	// ---------------------------------------------------------------------------
	// init
	// ---------------------------------------------------------------------------

	function ExecPhp_UserUi(&$cache)
	{
		$this->m_cache = $cache;
		add_action('show_user_profile', array(&$this, 'action_user_profile'));
		add_action('edit_user_profile', array(&$this, 'action_user_profile'));
		add_action('profile_update', array(&$this, 'action_profile_update'));
	}

	// ---------------------------------------------------------------------------
	// hooks
	// ---------------------------------------------------------------------------

	function action_user_profile()
	{
		global $profileuser;

		if ($profileuser->has_cap(ExecPhp_CAPABILITY_EXECUTE_ARTICLES))
		{
			$usermeta =& $this->m_cache->get_usermeta($profileuser->ID);
?>
<fieldset>
<legend><?php _e('Exec-PHP Options', ExecPhp_PLUGIN_ID); ?></legend>
<p class="desc"><?php _e('Disable WYSIWYG Conversion Warning:', ExecPhp_PLUGIN_ID); ?></p>
<p><label for="<?php echo ExecPhp_POST_WYSIWYG_WARNING; ?>">
	<input style="width: auto;" type="checkbox" name="<?php echo ExecPhp_POST_WYSIWYG_WARNING; ?>" id="<?php echo ExecPhp_POST_WYSIWYG_WARNING; ?>" value="true" <?php if ($usermeta->hide_wysiwyg_warning()) : ?>checked="checked" <?php endif; ?>/>
	<?php _e('Select this option to turn off the WYSIWYG Conversion Warning in the Write menu. Nevertheless the recommended way is to switch off the WYSIWYG editor so you can be sure not to break existing PHP code by accident.', ExecPhp_PLUGIN_ID); ?>

</label></p>
</fieldset>
<?php
		}
	}

	function action_profile_update($user_id)
	{
		$user = new WP_User($user_id);
		if ($user->has_cap(ExecPhp_CAPABILITY_EXECUTE_ARTICLES))
		{
			$usermeta =& $this->m_cache->get_usermeta($user_id);
			$usermeta->set_from_POST();
			$usermeta->save();
		}
	}
}
endif;

?>