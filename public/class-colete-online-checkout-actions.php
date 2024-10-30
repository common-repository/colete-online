<?php

defined( 'ABSPATH' ) || exit;

require_once COLETE_ONLINE_ROOT . "/lib/class-colete-online-client.php";

class ColeteOnlineCheckoutActions {

  public function __construct() {}

  public function update_session($post_data) {

    $changed = false;
    $open_package = WC()->session->get('with_open_package');
    parse_str($post_data, $vars);
    WC()->session->set(
      'with_open_package',
      empty($vars['open_package']) ?
        'off' :
        wc_clean(wp_unslash($vars['open_package']))
    );

    if ($open_package !== WC()->session->get('with_open_package')) {
      $changed = true;
    }

    if ($_POST["payment_method"] !== WC()->session->get('chosen_payment_method')) {
      $changed = true;
    }

    if ($changed) {
      foreach (WC()->cart->get_shipping_packages() as $package_key => $package) {
        WC()->session->set('shipping_for_package_' . $package_key, null);
      }
    }
  }

  public function modify_shipping_option($method, $index) {
    if (!is_checkout()) {
      return;
    }
    $method_meta = $method->get_meta_data();
    if (isset($method_meta['shipping_points_list'])) {
      if (is_array(WC()->session->get('chosen_shipping_methods'))) {
        $m = WC()->session->get('chosen_shipping_methods')[0];
        if ($m !== $method->get_id()) {
          return;
        }
      }
      $select_options = array(
        'type'          => 'select',
        'class'         => array('wc-enhanced-select', 'coleteonline_service_shipping_point_select'),
        // 'label'         => _x('Shipping point select', "coleteonline"),
        'placeholder'   => _x('Click to choose shipping point', "coleteonline"),
        'options'       => array_reduce(
          $method_meta['shipping_points_list'],
          function ($options, $point) {
            $options[$point['id']] = $point['name'];
            if (
              isset($point['extendedData']) &&
              isset($point['extendedData']['approximateDistance'])
            ) {
              $options[$point['id']] .= ' - la approx. '
              . round($point['extendedData']['approximateDistance'], 2) . ' km';
            }
            return $options;
          },
          array(
            0 => _x('Choose shipping point', "checkout select", "coleteonline")
          )
        )
      );
      $service_id = $method_meta['service_id'];
      $user_id = get_current_user_id();
      if ($user_id && $service_id) {
        $fav_sp_for_method = get_user_meta(
          $user_id,
          "coleteonline_fav_sp_{$service_id}",
          true
        );
        if ( array_key_exists($fav_sp_for_method, $select_options['options'])) {
          $select_options['default'] = $fav_sp_for_method;
        }
      }
      woocommerce_form_field(
        'coleteonline_service_shipping_point-' . $method_meta['service_id'],
        $select_options
      );
    }
  }

  public function display_before_shipping_options() {
    if (is_checkout()) {
      $product_not_eligible_for_locker = false;
      $advanced_product_fields_enabled = get_option("coleteonline_advanced_product_fields");
      $cart_items = WC()->cart->get_cart();
      foreach ($cart_items as $cart_item) {
          $product_id = $cart_item['product_id'];
          $not_eligible_for_locker =
            get_post_meta($product_id, 'coleteonline-shipping-product-not-eligible-for-locker', true);
          if ($not_eligible_for_locker === 'on') {
              $product_not_eligible_for_locker = true;
              break;
          }
      }
      if ($product_not_eligible_for_locker && $advanced_product_fields_enabled === "yes") {
        if (get_option("coleteonline_delivery_to_fixed_points_restricted_products_message_toggle") === "yes") {
          echo '<tr class="coleteonline-products-fixed-delivery-not-available">';
          echo '<td colspan="2">';
          if (strlen(get_option("coleteonline_delivery_to_fixed_points_restricted_products_message_text")) > 0) {
            echo get_option("coleteonline_delivery_to_fixed_points_restricted_products_message_text");
          } else {
            _e("Shipping point delivery not available because the order contain products that cannot be delivered to fixed points.", "coleteonline");
          }
          echo "</td>";
          echo "</tr>";
        }
      } else {
        $forbiddenPaymentGateways = get_option("coleteonline_delivery_to_fixed_points_restricted_payment_gateways");
        $chosenPaymentMethod = WC()->session->get('chosen_payment_method');
        if (is_array($forbiddenPaymentGateways) &&
            in_array($chosenPaymentMethod, $forbiddenPaymentGateways)) {
          ?>
          <?php
            if (get_option("coleteonline_delivery_to_fixed_points_restricted_message_toggle") === "yes") {
              echo '<tr class="coleteonline-fixed-delivery-not-available">';
              echo '<td colspan="2">';
              if (strlen(get_option("coleteonline_delivery_to_fixed_points_restricted_message_text")) > 0) {
                echo get_option("coleteonline_delivery_to_fixed_points_restricted_message_text");
              } else {
                _e("Shipping point delivery not available for this payment method", "coleteonline");
              }
              echo "</td>";
              echo "</tr>";
            }
          ?>
          <?php
        }
      }
    }
  }

  public function display_shipping_options() {
    if (is_checkout()) {
      $isChecked = WC()->session->get('with_open_package') === 'on' ? 'checked' : '';
      if (get_option("coleteonline_order_open_at_delivery") === "user_choice") {
      ?>
        <tr class="shipping-open-package">
          <th><strong><?php echo __('Open package', 'coleteonline') ?></strong></th>
          <td>
            <ul id="shipping_method" class="woocommerce-shipping-methods" style="list-style-type:none;">
              <li>
                <input type="checkbox" name="open_package" id="open_package" <?php echo $isChecked; ?>>
                <label for="open_package">
                  <?php echo get_option("coleteonline_order_open_at_delivery_text"); ?>
                </label>
              </li>
            </ul>
          </td>
        </tr>
    <?php
      }
    }
  }

  public function create_order_update_customer_note($order, $data) {
    try {
      $order_helper = new \ColeteOnline\ColeteOnlineWcOrderDataHelper(null, $order);
      if ($order_helper->is_colete_online_order()) {
        $packages = $order_helper->get_packages(true);
        $note = get_option('coleteonline_order_custom_note');
        $note = str_replace(
          array('[content]', '[skuContent]'),
          array($packages['content'],
                $packages['stock_contents']),
          $note
        );
        $order->set_customer_note(
          $order->get_customer_note('view') . ' ' . $note
        );
      }
    } catch (Exception $e) {}
  }

  public function create_order_set_shipping_options($order, $data) {
    $open_package = "off";
    if (isset($_POST["open_package"])) {
      $open_package = wc_clean(wp_unslash($_POST["open_package"]));
    }
    $order->add_meta_data(
      '_coleteonline_open_package',
      $open_package,
      true
    );
  }

  public function checkout_create_order_shipping_item_handler($item, $package_key, $package, $order) {
    if ($item->get_type() === "shipping" &&
        $item->get_method_id() === "coleteonline") {
      $method_id = $item->get_meta("service_id");
      if (isset($_POST['coleteonline_service_shipping_point-' . $method_id])) {
        $shipping_point_id = $_POST['coleteonline_service_shipping_point-' . $method_id];
        $item->add_meta_data(
          'shipping_point_id',
          wc_clean(wp_unslash($shipping_point_id)),
          true
        );
        $user_id = $order->get_user_id();
        update_user_meta(
          $user_id,
          "coleteonline_fav_sp_{$method_id}",
          wc_clean(wp_unslash($shipping_point_id))
        );
        $shipping_points = $item->get_meta('shipping_points_list');
        $shipping_point = array_filter(
          $shipping_points,
          function ($point) use ($shipping_point_id) {
            return (int)$point['id'] === (int)$shipping_point_id;
          }
        );
        if (count($shipping_point) === 1) {
          $first_key = key($shipping_point);
          $item->add_meta_data(
            'shipping_point_name',
            wc_clean(wp_unslash($shipping_point[$first_key]['name'])),
            true
          );
        }
      }
      $item->delete_meta_data('shipping_points_list');
    }
  }

  private function validate_shipping_method($posted) {
    $show_notice = false;
    foreach ($_POST as $key => $value) {
      if (strpos($key, "coleteonline_service_shipping_point") === 0) {
        $found = true;
        $value = wc_get_post_data_by_key($key);
        if (empty($value)) {
          $show_notice = true;
        }
        break;
      }
    }
    if ($show_notice) {
      wc_add_notice(__("A shipping point location should be selected.",
                        'coleteonline'),
                        'error');
    }
  }

  public function add_to_cart_action( $cart_item_key, $product_id, $quantity,
                                      $variation_id, $variation, $cart_item_data) {
    if (isset(WC()->session)) {
      WC()->session->set("coleteonline_prevent_carrier_update", 1);
    }
  }

  private function get_enabled_payment_methods() {
    $enabled_payment_methods = array();
    $payment_gateways = WC()->payment_gateways->get_available_payment_gateways();

    foreach ($payment_gateways as $gateway) {
      if ($gateway->is_available()) {
        $enabled_payment_methods[$gateway->id] = $gateway->get_title();
      }
    }

    return $enabled_payment_methods;
  }

  public function validate_checkout($posted) {
    $this->validate_shipping_method($posted);
  }

}
