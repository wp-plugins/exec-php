<?php
/*
Plugin Name: Exec PHP
Plugin URI: http://www.soeren-weber.net/post/2005/08/18/50
Description: Allows &lt;?PHP ?&gt; tags inside of your posts.
Version: 1.2
Author: S&ouml;ren Weber
Author URI: http://soeren-weber.net
*/

function execphp_replace($match)
{
  // replacing WPs strange PHP tag handling with a functioning tag pair
  $output = '<?php'. $match[2]. '?>';
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
  $content = preg_replace_callback($pattern, 'execphp_replace', $content);

  // to be compatible with older PHP4 installations
  // don't use fancy ob_XXX shortcut functions
  ob_start();
  eval(" ?> $content <?php ");
  $output = ob_get_contents();
  ob_end_clean();
  return $output;
}

function execphp_init()
{
  add_filter('the_content', 'execphp_apply', 1);
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