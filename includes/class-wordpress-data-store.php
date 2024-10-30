<?php

namespace ColeteOnline;

defined( 'ABSPATH' ) || exit;

require_once COLETE_ONLINE_ROOT . "/lib/store/interface-colete-online-data-store.php";

class WordpressDataStore implements InterfaceDataStore {
  public function get(string $key) {
    return get_transient($key);
  }

  public function set(string $key, $data): void {
    set_transient($key, $data);
  }

}