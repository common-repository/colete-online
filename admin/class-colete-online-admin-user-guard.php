<?php

defined( 'ABSPATH' ) || exit;

class ColeteOnlineAdminUserGuard {

  public static function check() {
    $user = wp_get_current_user();
    $allowed = array('administrator', 'shop_manager', 'editor');
    if( ( empty( $user ) ||
          !count(array_intersect( $allowed, (array) $user->roles ) ) )) {
      return false;
    }
    return true;
  }

}