<?php

namespace ColeteOnline;

defined( 'ABSPATH' ) || exit;

require_once COLETE_ONLINE_ROOT . "/lib/http/interface-colete-online-http-request.php";

class HttpRequest {
  private static ?InterfaceHttpRequest $http = null;

  public static function set($http) {
    self::$http = $http;
  }

  public static function get() {
    return self::$http;
  }
}