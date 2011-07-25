<?php
/**

  * Wordpress helper functions
  *
  * @author Roel van der Ven
  * @version .94
  * @copyright Roel van der Ven, August 2010

**/

// Remove from wp_head:
function rv_remove_x_pingback($headers) {
  unset($headers['X-Pingback']);
  $headers['X-Credits'] = 'Roelvanderven.com';
  return $headers;
}

// Remove scripts from the wp script queue:
// credit http://justintadlock.com/archives/2009/08/06/how-to-disable-scripts-and-styles
function rv_deregister_javascript() {
  // comment-reply is loaded with the default Twenty-ten template, might not be needed when using own
  wp_deregister_script('comment-reply');
  wp_deregister_script('l10n');
}

function rv_disable_widgets($sidebars_widgets) {
  $sidebars_widgets = array(false);
  return $sidebars_widgets;
}

// Get body of a specific page, optional: get excerpt in stead of page_content
function rv_getPage($pageID, $excerpt = false){
  // Put page id in variable, otherwise wordpress chokes
  $page_data = get_page($pageID);
  if ($excerpt) {
    // Apply content filters to keep page markup
    $content = apply_filters('the_content', $page_data->post_excerpt);
  } else {
    // Apply content filters to keep page markup
    $content = apply_filters('the_content', $page_data->post_content);
  }
  return $content;
}

// Print the sanitized page title as #id in the body tag:
function rv_body_id() {
  global $post;
  if (!empty($post)) {
    $title = get_the_title($post->post_parent);
    $result = 'id="wp-'.strtolower(sanitize_title($title)).'" ';
    return $result;
  }
}

// A generic function to cut off a sentence and add '...'.
function rv_wordCut($text, $limit, $end) {
  if (strlen($text) > $limit) {
    // $text = strip_tags($text);
    $txt1 = wordwrap($text, $limit, '[cut]');
    $txt2 = explode('[cut]', $txt1);
    $ourTxt = $txt2[0];
    $finalTxt = $ourTxt.$end;
  } else {
    $finalTxt = $text;
  }
  return $finalTxt;
};

// Create debug info to render in HTML
function rv_debuginfo($post) {
  global $post;
  global $current_user;
  $rv_ref = @$_SERVER['HTTP_REFERER'];
  print "<!-- \n\n";
  print "     RV DEBUG INFO    \n\n";
  print "     Database: ".get_num_queries()." queries, ".timer_stop()." seconds\n";
  if ($rv_ref) {
    print "     Referrer: ".$rv_ref."\n";
  }
  if ($current_user->user_login) {
    print "     Logged in as: ".$current_user->user_login."\n";
  }
  print "\n";
  print "  -->\n";
}

// Add credits to <head>
function rv_head() {
  print "\n";
  print "   <!-- \n\n";
  print "     Theme & plugin by    \n";
  print "     Roel van der Ven    \n";
  print "     http://roelvanderven.com/    \n\n";
  print "   -->\n\n";
}

// Get the existing Wordpress rewrite rules, usage: print_r(rv_get_rewrite_urls());
function rv_get_rewrite_urls() {  
  global $wp_rewrite;  
  return $wp_rewrite->wp_rewrite_rules();
}

// Flush rewrite rules, trigger on plugin activation
function rv_rewrite_flush() {
  global $wp_rewrite;
  $wp_rewrite->flush_rules();
}

// Add support for reading $_GET like URL variables, inspired by:
// http://youngdutchdesign.com/rewrite-multiple-get-variables-for-wordpress-plugins
function rv_newsletter_var($public_query_vars) {
  $public_query_vars[] = 'rv_urlvar';
  return $public_query_vars;
}

// Add a rewrite rule
// Used it here for /newsletter on a site, can be for anything!
function rv_rewrite_newsletter() {
  add_rewrite_rule('newsletter/([^/]+)/?$', 'index.php?pagename=newsletter&rv_urlvar=$matches[1]','top');
}

// Check if a result is odd, to use in zebra design lists.
function rv_is_odd($number) {
   return $number & 1; // 0 = even, 1 = odd
}

// Have a better and more flexible next / previous navigation for use in the Loop:
// use print rv_customnav(get_permalink()) within the loop
// WARNING: this is looking only for posts within the same category!
function rv_customnav($current) {
	$prevURL = get_permalink(get_previous_post(true)->ID);
	$nextURL = get_permalink(get_next_post(true)->ID);
	$output = '';
	// Print previous link if available
	if ($current != $prevURL) {
		$output .= '<a href="'.$prevURL.'" title="'.__('zurück', 'rv_theme').'" class="btn_previous" hidefocus="hidefocus">'.__('zurück', 'rv_theme').'</a>'."\n";
	}
	// Print next link if available
	if ($current != $nextURL) {
		$output .= '<a href="'.$nextURL.'" title="'.__('nächste', 'rv_theme').'" class="btn_next" hidefocus="hidefocus">'.__('nächste', 'rv_theme').'</a>';
	}
	return $output;
}

// Create a valid date
function rv_makedate($day, $month, $year, $hour = '', $minute = '') {
  if ($day && $month && $year) {
    if($hour && $minute) {
      $time = $hour.':'.$minute;
    }
    $rv_date = strtotime(sprintf('%02d', $day).'-'.sprintf('%02d', $month).'-'.$year.' '.$time);
    error_log($rv_date);
    if ($rv_date != '0') {
      return $rv_date;
    } else {
      return false;
    }
  } else {
    return false;
  }
}

// Function to get content in a particular language when dependant on qTranslate
// qTranslate plugin for Wordpress: http://www.qianqin.de/qtranslate/
// Inspired by http://stackoverflow.com/questions/1853406/simple-regular-expression-to-return-text-from-wordpress-title-qtranslate-plugin
function rv_translatethis($content) {
  $regexp = '/<\!--:(\w+?)-->([^<]+?)<\!--:-->/i';
  if(preg_match_all($regexp, $content, $matches)) {
    $output = array();
    $count = count($matches[0]);
    for($i = 0; $i < $count; $i++) {
      $output[$matches[1][$i]] = $matches[2][$i];
    }
    return $output;
  } else {
    return 'no matches';
  }
}

// Remove this plugin from the Wordpress automatic update check:
function rv_dontupdate_check_plugin($r, $url) {
  if (0 !== strpos($url, 'http://api.wordpress.org/plugins/update-check'))
    return $r;
  $plugins = unserialize($r['body']['plugins']);
  unset($plugins->plugins[plugin_basename(__FILE__)]);
  unset($plugins->active[array_search(plugin_basename( __FILE__), $plugins->active)]);
  $r['body']['plugins'] = serialize($plugins);
  return $r;
}

function rv_dontupdate_check_theme($r, $url) {
  if (0 !== strpos($url, 'http://api.wordpress.org/themes/update-check'))
    return $r;
  $themes = unserialize($r['body']['themes']);
  unset($themes[get_option('template')]);
  unset($themes[get_option('stylesheet')]);
  $r['body']['themes'] = serialize($themes);
  return $r;
}

// Check permissions on wp-content folder
function rv_check_permissions($name, $path, $perm) {
  clearstatcache();
  $configmod = @substr(sprintf(".%o", fileperms($path)), -4);
  if ($configmod != $perm) {
    return '<div class="error"><p>Warning, the permissions of your directory are now '.$configmod.'. You should set them to <strong>'.$perm.'</strong></p></div>';
  }
}

// Check if x table exists
function rv_mysql_table_exists($table_name) {
  global $wpdb;
  foreach ($wpdb->get_col("SHOW TABLES", 0) as $table) {
    if ($table == $table_name) {
      return true;
    }
  }
  return false;
}

// Get attachment custom function
function getAttachment($ID) {
  $args = array(
    'post_type' => 'attachment',
    'numberposts' => -1,
    'post_status' => null,
    'orderby' => 'menu_order',
    'order' => 'ASC',
    'post_parent' => $ID
    ); 
  $attachments = get_posts($args);
  return $attachments;
}

// Enrich media edit form to get id for reference
function rv_image_attachment_fields_to_edit($form_fields, $post) {
  if (substr($post->post_mime_type, 0, 5) == 'image' ) {
    $alt = get_post_meta($post->ID, '_wp_attachment_image_alt', true);
    if (empty($alt) )
      $alt = '';

    $form_fields['post_title']['required'] = true;

    $form_fields['attachment_link'] = array(
      'input'      => 'html',
      'html'       => "<input type='text' class='text attachment_link' readonly='readonly' name='attachment_link' value='" . esc_attr($post->ID) . "' /><br />",
      'label' => __('Attachment ID'),
      'helps' => __('Attachment ID for upload reference')
    );

    $form_fields['image_alt'] = array(
      'value' => $alt,
      'label' => __('Alternate text'),
      'helps' => __('Alt text for the image, e.g. &#8220;The Mona Lisa&#8221;')
    );

    $form_fields['align'] = array(
      'label' => __('Alignment'),
      'input' => 'html',
      'html'  => image_align_input_fields($post, get_option('image_default_align')),
    );

    $form_fields['image-size'] = image_size_input_fields($post, get_option('image_default_size', 'medium'));

  } else {
    unset($form_fields['image_alt']);
  }
  return $form_fields;
}

// On plugin activate; flush Wordpress rewrites:
function rv_plugin_activate() {
  // Flush rewrite rules:
  rv_rewrite_flush();
}

/*! Menu */

// Get custom styles in plugin on admin
function rv_register_style() {
  wp_register_style('rv_styles', WP_PLUGIN_URL . '/RV_extras/css/style.css');
  wp_enqueue_style('rv_styles');
}

// Replace admin footer
function rv_print_admin_footer() {
  print '<span id="footer-thankyou">' . __('Template &amp; plugin created by <a href="http://roelvanderven.com/">Roel van der Ven</a>.').'</span> | '. __('Powered by <a href="http://wordpress.org/">WordPress</a>.');
}

// Resize oEmbed settings
function rv_resizeOembed($markup) {
  $w = get_option('embed_size_w');
  $h = get_option('embed_size_h');
  $patterns = array();
  $replacements = array();
  if(!empty($w)) {
    $patterns[] = '/width="([0-9]+)"/';
    $patterns[] = '/width:([0-9]+)/';
    $replacements[] = 'width="'.$w.'"';
    $replacements[] = 'width:'.$w;
  }
  if(!empty($h)) {
    $patterns[] = '/height="([0-9]+)"/';
    $patterns[] = '/height:([0-9]+)/';
    $replacements[] = 'height="'.$h.'"';
    $replacements[] = 'height:'.$h;
  }
  return preg_replace($patterns, $replacements, $markup);
}

?>