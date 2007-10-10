What does this plugin do?

The Exec-PHP plugin allows <?php ?> tags inside the content or excerpt of your posts and pages to be executed just as in usual PHP files.
Make it quick. Where can I download it?

Download Exec-PHP 3.0 here!

Be warned: Version 3.0 is not for everyone! Read the description below!
How do I install the plugin?

As with the most Wordpress plugins, installation is easy:

    * Download the Exec-PHP plugin and extract it to your Wordpress wp-content directory.
    * Activate the plugin in your Wordpress admin panel.

Ready. It couldn’t be easier. The rest is self-explanatory. ;)
Why is there so much text below?

Because I hate cool plugins that are badly documented. Even the smallest piece of code needs some documentation. The following text is (hopefully) pretty exhaustive and structured as FAQ. Feel free to skip the questions you are not interested in.

    * I want to test it. What do I need?
    * How do I update the plugin from a version older than 3.0?
    * What is the difference to other similar plugins?
    * How do I use the plugin?
    * Is using this plugin secure?
    * How do I report a bug?
    * Why does my included PHP script spits out parsing errors?
    * Why does WP 2.x. mess’ up my <?php ?> tags after saving the post?
    * How can I just print out PHP code and don’t execute it?
    * Is it possible to restrict the PHP execution to specific users?
    * Which capabilities do a post author need to work with the plugin?
    * Are there currently any known issues about this plugin?
    * Which versions of the Exec-PHP plugin do exist and what are their features?
    * Which tests were made to verfiy the plugin is working?

I want to test it. What do I need?

You need the following software to be installed on your webserver:

    * PHP 4.1. or higher
    * Wordpress 2.x
    * the Exec-PHP plugin ;)

How do I update the plugin from a version older than 3.0?

Because directory layout has changed, you have to remove your old exec-php.php from your plugins folder manually and afterwards extract the files from the archive to your plugins directory. That’s it.

Anyway, please notice that this version behaves much more restictive than previous versions but allows literally all valid PHP syntax to be executed.
What is the difference to other similar plugins?

I know of the a few other plugins. In comparison to all, this is the only plugin which allows the PHP code to be written as you are used to. For example some plugins search for a complete XHTML tag set like <phpcode> </phpcode> and execute the code between. This differs from the usual way you write PHP code. Often the plugins evaluate the code after Wordpress applied some filters like texturize. Because this also texturizes the PHP code, the plugins have to undo the texturize just for the code part. For more complex code this can not be done correctly and often may lead into a parsing error even if the code is syntactically correct. Also some plugins do have significant problems in parsing a page containing escaped strings.
Plugin 	Difference/Disadvantage
RunPHP by Mark Somerville

    * uses XML tag syntax to separate code from HTML
    * does strange conversions to "fix" texturized posts
    * does not support user restrictions

RunPHP by James Van Lommel

    * creates parsing errors with most of the test code below

PHP Exec by Priyadi Iman Nurcahyo

    * uses XML tag syntax to separate code from HTML
    * does strange conversions to "fix" texturized posts

EzStatic 3 by Owen Winkler

    * does not execute test #16 (see below)

How do I use the plugin?

To execute code inside of your post or page, just type in the PHP code as you would do in the usual way. To have the plugin work properly do both of the following things:

    * Disable the WYSIWYG rich editor
    * Disable "WordPress should correct invalidly nested XHTML automatically"

E.g. for validating, that the plugin works as expected, create a new post and write the following text:

<?php echo "This is the Exec-PHP 'Hello World'"; ?>

Is using this plugin secure?

Well, the short answer: No. Allowing your users to include PHP code in posts or pages expose your Wordpress API in specific and your server configuration in general to this user. By that a user can easily include code into the posts that alters his security level to administrator rights and so can take over your blog or just read out your database password, etc.. If in doubt, don’t allow a user to execute PHP code. This can be easily adjusted on a per user base.
How do I report a bug?

You can post bug encounters here in the comments. Before doing this make sure your PHP script is running properly in a separate file. If it does, assure that you did not hit the ‘globals’ issue. If you still think it’s a bug, keep in mind that Wordpress’ commenting system is not build to write unescaped code, so better convert it to the correct XHTML entities, point to it using an external link or send it to me by email by using the contact form of my author page.
Why does my included PHP script spits out parsing errors?

Assume your included code is working outside a post and the path to the include file is correct. PHP may still spit out error messages even if everything seems to be correct. This can happen when your included file assumes it runs on global level and does not use the keyword global to mark its global variables. As example create a new post or page with the following code:

<?php include('test.php'); ?>

After that copy the following code into a new file named test.php:

<?php
$g_text = 'Hello World';
function hello()
{
  global $g_text;
  echo $g_text;
}
?>

Your test will end up in unexpected behaviour because assigning a value to the $g_text variable hasn’t taken place in global scope in terms of the used Wordpress hook to execute your code. This is because of how Wordpress works and there is no way to handle this in the plugin. You can work around this problem by adding the following PHP code into your post before the include statement or into the file you want to include at the very beginning:

global $g_text;

No need to say, you have to do this for each global variable where this wasn’t already done by the original programmer of the code. Another way would be to contact the original programmer and kindly ask him to change his code.
Why does WP mess’ up my <?php ?> tags after saving the post?

This is an issues of the WYSIWYG rich editor. You must deactivate the rich editor in your user settings and must turn off "WordPress should correct invalidly nested XHTML automatically".
How can I just print out PHP code and don’t execute it?

If you just want to print out code and don’t want to execute it, like it is done for the examples here on this page, you have to make sure to escape each of the ?php tags to the correct XHTML entity form. So you have to change your tags from <?php ?> to &lt;?php ?&gt;.
Is it possible to restrict the PHP execution to specific users?

After installation execution of PHP code is limited to the “Administrator” or “Editor” role by default. By assigning the capability “exec_php” to another role or user will allow them to also include PHP code in their posts. Assigning capabilities to roles or users is out of the scope of this plugin. Because Wordpress has no built-in configuration page in the admin panel to assign roles/capabilities, you need to install one of the available role/capability manager plugins. There may be more such plugins available as shown in the following list:

    * Role Manager by Owen Winkler

Which capabilities do a post author need to work with the plugin?

The following matrix shows which capability a post or page author of Wordpress 2.x. needs to perform specific tasks with the plugin:
Task 	exec_php
capability 	unfiltered_html
capability
Write or edit posts and pages including PHP syntax 	  	X
Execute PHP code in posts and pages 	X

To make things clear: If an author wants to write a new post or page and want to execute PHP code inside of it, he needs to have assigned both capabilities. Otherwise the PHP code will get messed up during saving the post or the raw PHP code will be displayed instead of executing it.
Are there currently any known issues about this plugin?

Besides of limitations with the WYSIWYG rich editor mentioned above, there currently are no known issues.
Which versions of the Exec-PHP plugin do exist and what are their features?

    * 2005-08-18 Version 1.0: Plugin
          o Feature: Allows <?php ?> tags inside your posts and pages to execute the code inside of it
    * 2005-08-19 Version 1.1: Plugin
          o Bugfix: Escaped string delimiters in PHP strings are now parsed correctly
    * 2005-12-04 Version 1.2: Plugin
          oBugfix: Reparing issue with reopening PHP tags (Test #16)
    * 2005-12-22 Version 2.0: Plugin
          o Feature: For WP 2.0 execution of PHP is now restricted to Administrators or Editors
          o Feature: Supporting alternative PHP tags [?php ?]
    * 2006-08-06 Version 3.0: Plugin
          o Feature: Removing all alternative PHP tag styles like [?php ?] and < ?php ?>, because regex was buggy and to tough to support
          o Feature: Removing support for WP 1.x, because regex was buggy and to tough to support
          o Feature: Moving plugin files to plugins subfolder
          o Feature: Adding tag style converter
          o Feature: Adding support for excerpt field
          o Bugfix: Because of changes to PHP tag handling, the bug reported in comment 84 is fixed

Which tests were made to verfiy the plugin is working?

The following tests were made. On the left side the PHP code taken directly from the tests is written. On the right side the live output generated by the Exec-PHP plugin is shown. Because of the content of this test, this page will not verify as XHTML. If you think, your favorite PHP plugin is better than this one, try out all the tests below and see if this works correctly.
# 	Code 	Output
1 	<?php ?>
2 	<?php echo "a?>1"; ?> 	a?>1
3 	<?php echo 'b?>1'; ?> 	b?>1
4 	<?php echo "a?>2"; ?> 	a?>2
5 	<?php echo 'b?>2'; ?> 	b?>2
6 	<?php?>
7 	<?php echo"a?>3";?> 	a?>3
8 	<?php echo'b?>3';?> 	b?>3
9 	<?php echo"a?>4";?> 	a?>4
10 	<?php echo'b?>4';?> 	b?>4
11 	<?php echo "c";?>1";?> 	c1?;?>
12 	<?php echo 'd';?>1';?> 	d1';?>
13 	<?php echo "c';?>2";?> 	c’;?>2
14 	<?php echo 'd";?>3';?> 	d”;?>3
15

<?php
echo "impressive '";
echo 'string' "';
echo "handling\"";
?>

	impressive ’string’ “handling”
16

<?php if (1) { ?>
<b>Handle THIS!</b>
<?php } ?>

	Handle THIS!