<?php

namespace ColeteOnline;

defined( 'ABSPATH' ) || exit;

require_once COLETE_ONLINE_ROOT . "/lib/store/interface-colete-online-data-store.php";

class DataStore {
  private static ?InterfaceDataStore $store = null;

  public static function set($store) {
    self::$store = $store;
  }

  public static function get() {
    return self::$store;
  }
}