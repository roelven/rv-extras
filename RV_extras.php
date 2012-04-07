<?php
/*

  Plugin Name:    Roelvens' Extras
  Plugin URI:     http://roelvanderven.com
  Description:    Provides additional functionality, security, debug options and settings.
  Version:        0.9.2
  Author:         Roel van der Ven
  Author URI:     http://roelvanderven.com

*/

// Optional force debugging on screen:
//
// ini_set('display_errors','1');
// ERROR_REPORTING(E_ALL);

// Disallow direct access to the plugin file
if (basename($_SERVER['PHP_SELF']) == basename (__FILE__)) {
  die('Sorry, but you cannot access this page directly.');
}

// Include function library
include_once(WP_PLUGIN_DIR.'/rv_extras/functions/functions.php');

// Only run on wp-admin pages:
if(is_admin()) {
  add_action('admin_init', 'rv_register_style');
  add_action('admin_init', 'rv_rewrite_flush');
    
  // Add field 'attachment ID' at uploads for extra reference.
  remove_action('attachment_fields_to_edit', 'image_attachment_fields_to_edit', 10, 2);
  add_action('attachment_fields_to_edit', 'rv_image_attachment_fields_to_edit', 10, 2);
  
  // Admin footer credits
  remove_action('admin_footer_text', 10, 1);
  add_action('admin_footer_text', 'rv_print_admin_footer');
}

// Remove this plugin and theme from automatic update array:
add_action('http_request_args', 'rv_dontupdate_check_plugin', 5, 2);
add_filter('http_request_args', 'rv_dontupdate_check_theme', 5, 2);

// Add support for $_GET like URI variables: (use /newsletter/yourvariable)
  //add_action('init', 'rv_rewrite_newsletter');
  //add_filter('query_vars', 'rv_newsletter_var');

// Remove unnecessary bloat in wp header
remove_action('wp_head',        'wp_generator');
remove_action('wp_head',        'wlwmanifest_link');
remove_action('wp_head',        'rsd_link');
remove_action('wp_head',        'feed_links', 2);
remove_action('wp_head',        'feed_links_extra', 3);
remove_action('wp_head',        'start_post_rel_link');
remove_action('wp_head',        'index_rel_link');
remove_action('wp_head',        'adjacent_posts_rel_link');

// Disable widgets (removes template option + menu page widgets)
remove_action('plugins_loaded', 'wp_maybe_load_widgets', 0);

add_action('show_admin_bar',    false);
add_action('wp_print_scripts',  'rv_deregister_javascript', 100);

add_filter('wp_headers',        'rv_remove_x_pingback');
add_filter('sidebars_widgets',  'rv_disable_widgets');

// Remove uncool qTranslate stuff (use when qTranslate is activated)
// remove_action('wp_head',        'qtrans_header', 10, 1);

// Disable Wordpress' default Flash uploader:
  //add_filter('flash_uploader', create_function('$a', "return null;"), 1);

// Add query info to theme footer (disable on production)
add_action('wp_footer',         'rv_debuginfo', 1);
add_action('wp_footer',           'rv_head', 1);
?>