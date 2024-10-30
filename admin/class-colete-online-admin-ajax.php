<?php

defined( 'ABSPATH' ) || exit;

require_once COLETE_ONLINE_ROOT . '/admin/class-colete-online-admin-user-guard.php';

class ColeteOnlineAdminAjax {

  public function __construct() {}

  public function get_all_addresses() {

    if (!ColeteOnlineAdminUserGuard::check()) {
      wp_die(null, 401);
    }

    try {
      $settings = get_option("woocommerce_coleteonline_settings");
      $addresses = (new \ColeteOnline\ColeteOnlineClient(
        $settings['client_id'],
        $settings['client_secret']
      ))->get_all_addresses();
      echo json_encode(
        array(
          "addresses" => $addresses,
          "selected" => get_option("coleteonline_default_shipping_address_id", 0)
        )
      );
    } catch (Exception $e) {
      echo json_encode(
        array(
          "addresses" => [],
          "selected" => 0
        )
      );
    }
    wp_die();
  }

}
