<?php
/*
 * README
 * This module contains custom helpers and small fixes
 */

/**
 * This is used to add notifications for users
 */
add_action('admin_notices', '_seravo_notification');
function _seravo_notification() {

  // get notification
  if ( false === ( $response = get_transient( 'seravo_notification' ) ) || ( isset($_SERVER['HTTP_PRAGMA']) && $_SERVER['HTTP_PRAGMA'] == 'no-cache' ) ) { 
    $response = json_decode( file_get_contents('https://wp-palvelu.fi/ilmoitus/') );
    set_transient( 'seravo_notification', $response, HOUR_IN_SECONDS );
    // allow some html tags but strip most
    $message = ''; 
    if( isset($response->message) ) { 
      $message = $response->message;
      $message = strip_tags( trim($message),"<br><br/><a><b><strong><i>" );
    }   
    // control alert type
    $type = ''; 
    if( isset($response->type) ) { 
      $type = $response->type;
    }   
  }
  if (!empty($message) ) { 
  ?>  
    <div class="<?php esc_attr_e($type) ?> notice is-dismissible">
      <p><?php echo $message; ?> <button type="button" class="notice-dismiss"></button></p>
    </div>
  <?php
  }
}


/**
 * Removes core update nags
 */
add_action('admin_menu','_seravo_hide_update_nag');
function _seravo_hide_update_nag() {
  remove_action( 'admin_notices', 'update_nag', 3 );
}

/**
 * Removes update bubbles
 */
add_filter('wp_get_update_data', '_seravo_hide_update_data');
function _seravo_hide_update_data($update_data, $titles='') {
  return array (
    'counts' => array(
      'plugins' => 0,
      'themes' => 0,
      'wordpress' => 0,
      'translations' => 0,
      'total' => 0
    ),
    'title' => ''
  );
}

/**
 * Return better http status code (401 unauthorized) after failed login.
 * Then failed login attempts (brute forcing) can be noticed in access.log
 * WP core ticket: https://core.trac.wordpress.org/ticket/25446
 */
add_action( 'wp_login_failed', '_seravo_login_failed_http_code' );
function _seravo_login_failed_http_code() {
    status_header( 401 );
}