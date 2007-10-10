<?php
/*
Plugin Name: Exec PHP
Plugin URI: http://www.soeren-weber.net/post/2005/08/18/50
Description: Allows &lt;?PHP ?&gt; tags inside of your posts.
Version: 1.1
Author: S&ouml;ren Weber
Author URI: http://soeren-weber.net
*/

function execphp_replace($match)
{
  // to be compatible with older PHP4 installations
  // don't use fancy ob_XXX shortcut functions
  ob_start();
  eval($match['2']);
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}

function execphp_apply($content)
{
  // for debugging also group unimportant components with ()
  // to check them with a print_r($matches)
  $pattern = '/'.
    '(<[\s]*\?php)'. // the opening of the <?php tag
    '(((([\'\"])([^\\\5]|\\.)*?\5)|(.*?))*)'. // ignore content of PHP quoted strings
    '(\?>)'. // the closing ? > tag
    '/is';

  return preg_replace_callback($pattern, 'execphp_replace', $content);
}

function execphp_init()
{
  add_filter('the_content', 'execphp_apply', 3);
}

if (function_exists('get_currentuserinfo'))
{
  // WP 1.5, get_currentuserinfo already loaded
  execphp_init();
}
else
{
  // Need to wait until pluggable functions have been loaded
  // e.g. the Gravatars plugin
  add_action('init', 'execphp_init');
}
?>