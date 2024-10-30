<?php

namespace ColeteOnline;

defined( 'ABSPATH' ) || exit;

class Url {
  private static $auth_url = 'https://auth.colete-online.ro/token';
  private static $api_url = 'https://api.colete-online.ro';

  private static bool $is_testing = false;

  public static function setTesting(bool $is_testing) {
    self::$is_testing = $is_testing;
  }

  public static function getAuth($trailing_slash = true) {
    $slash = $trailing_slash ? '/' : '';
    return self::$auth_url . $slash;
  }

  public static function getApi($trailing_slash = true) {
    $slash = $trailing_slash ? '/' : '';
    if (self::$is_testing) {
      return self::$api_url . "/staging" . $slash;
    }
    return self::$api_url . $slash;
  }

}
