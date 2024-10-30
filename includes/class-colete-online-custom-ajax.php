<?php


define('SHORTINIT', true );
define('DOING_AJAX', true);
// define( 'WP_DEBUG', true );
// define( 'WP_DEBUG_DISPLAY', true );

if (!isset( $_REQUEST['action'])) {
  die('-1');
}

$wp_root_path = dirname( dirname( dirname( __FILE__ ) ) );
require( $wp_root_path . '/../../wp-load.php' );

//Typical headers
header('Content-Type: text/html');
send_nosniff_header();

//Disable caching
header('Cache-Control: no-cache');
header('Pragma: no-cache');


$action = esc_attr(trim($_REQUEST['action']));

//A bit of security
$allowed_actions = array(
  'coleteonline_autocomplete_city_state_merged',
  'coleteonline_reverse_postal_code_search',
  'coleteonline_postal_code_search',
  'coleteonline_validate_postal_code',
  'coleteonline_autocomplete_city',
  'coleteonline_autocomplete_street',
  'coleteonline_phone_number_check'
);

$wp_plugin_paths = array();

require ABSPATH . WPINC . '/theme.php';
require ABSPATH . WPINC . '/general-template.php';
require ABSPATH . WPINC . '/link-template.php';

require ABSPATH . WPINC . '/http.php';
require ABSPATH . WPINC . '/class-wp-http.php';
require ABSPATH . WPINC . '/class-wp-http-streams.php';
require ABSPATH . WPINC . '/class-wp-http-curl.php';
require ABSPATH . WPINC . '/class-wp-http-proxy.php';
require ABSPATH . WPINC . '/class-wp-http-cookie.php';
require ABSPATH . WPINC . '/class-wp-http-encoding.php';
require ABSPATH . WPINC . '/class-wp-http-response.php';
require ABSPATH . WPINC . '/class-wp-http-requests-response.php';
require ABSPATH . WPINC . '/class-wp-http-requests-hooks.php';

wp_plugin_directory_constants();

// require ABSPATH . WPINC . '/kses.php';
// require ABSPATH . WPINC . '/capabilities.php';
// require ABSPATH . WPINC . '/class-wp-roles.php';
// require ABSPATH . WPINC . '/class-wp-role.php';
// require ABSPATH . WPINC . '/class-wp-user.php';
// require ABSPATH . WPINC . '/class-wp-query.php';
// require ABSPATH . WPINC . '/user.php';

require_once "../colete-online.php";

function wc_clean( $var ) {
  if ( is_array( $var ) ) {
    return array_map( 'wc_clean', $var );
  } else {
    return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
  }
}

if (in_array($action, $allowed_actions)) {
  do_action('colete_online_custom_ajax_handler_nopriv_' . $action);
} else {
  die('-1');
}