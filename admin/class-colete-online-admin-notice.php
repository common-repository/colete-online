<?php

defined( 'ABSPATH' ) || exit;

class ColeteOnlineAdminNotice {

  public function __construct() {}


  public function admin_notice() {
    ?>
    <?php
      $colete_online_woo_commerce_exists = class_exists( 'WooCommerce' );
      $settings = get_option("woocommerce_coleteonline_settings");
      $colete_online_set_up = isset($settings['client_id']) &&
                              strlen($settings['client_id']) &&
                              isset($settings['client_secret']) &&
                              strlen($settings['client_secret']);
      if ($colete_online_woo_commerce_exists && $colete_online_set_up) {
        return;
      }
    ?>

    <div class="notice-colete-online notice notice-warning is-dismissible"
      style="padding: 20px;"
    >
      <div class="coleteonline-notice-logo" style="max-width: 120px; max-height: 120px;">
        <?php
          echo file_get_contents(COLETE_ONLINE_ROOT . "/admin/assets/logo-small.svg");
        ?>
      </div>
      <div class="coleteonline-notice-text">
        <h2><?php _e('Send parcels anywhere using ColeteOnline', 'coleteonline') ?></h2>
        <p><?php _e('Thank you for installing the ColeteOnline plugin!', 'coleteonline'); ?></p>
        <?php if (!$colete_online_woo_commerce_exists): ?>
          <p><?php _e('We detected that WooCommerce is inactive. To use our plugin you first need to enable woocommerce!', 'coleteonline'); ?></p>
        <?php elseif (!$colete_online_set_up): ?>
          <?php
          ?>
          <p><?php _e('There is only one step left! Please go to our settings page and set up ColeteOnline!', 'coleteonline'); ?></p>
          <a
            class="button button-primary"
            href="<?php echo network_admin_url('admin.php?page=wc-settings&tab=shipping&section=coleteonline'); ?>"
          >
            <?php _e('Set up colete online', 'coleteonline'); ?>
        </a>
        <?php endif; ?>
      </div>
    </div>
  <?php
  }

}
