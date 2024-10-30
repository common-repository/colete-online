<?php

use Automattic\WooCommerce\Utilities\OrderUtil;

defined('ABSPATH') || exit;

require_once COLETE_ONLINE_ROOT . '/admin/class-colete-online-admin-user-guard.php';

class ColeteOnlineAdminOrder {

  public function __construct() {}

  public function change_admin_shipping_address_fields($fields) {
    $fields['address_1']['label'] = __('Street', 'coleteonline');
    $street_number = array(
      'label' => __('Street number', 'coleteonline'),
      'show' => false
    );
    $offset = array_search('address_2', array_keys($fields));
    $fields = array_merge(
      array_slice($fields, 0, $offset),
      array('street_number' => $street_number),
      array_slice($fields, $offset, null)
    );
    return $fields;
  }


  public function get_services_list() {
    if (!ColeteOnlineAdminUserGuard::check()) {
      wp_die(null, 401);
    }

    $data = wc_clean(wp_unslash($_POST['data']));
    $order = wc_get_order($data['orderId']);

    $order_data = new \ColeteOnline\ColeteOnlineOrderData();
    $order_data_helper = new \ColeteOnline\ColeteOnlineWcOrderDataHelper(null, $order);

    $order_data->add_address_id('sender', $data['fromAddressId']);
    $order_data_helper->fill_recipient_shipping_data($order_data);

    $repaymentAmount = floatval($data['extraOptions']['repaymentAmount']);
    if ($repaymentAmount > 0) {
      $order_data->add_repayment_option($repaymentAmount);
    }

    $insuranceAmount = floatval($data['extraOptions']['insuranceAmount']);
    if ($insuranceAmount > 0) {
      $order_data->add_insurance_option($insuranceAmount);
    }

    $order_data->add_base_currency_option(
      $order_data_helper->get_order_currency(),
      "RON"
    );

    if ($data['extraOptions']['openAtDelivery'] === 'true') {
      $order_data->add_open_at_delivery_option();
    }

    $order_data->add_packages_content("PriceCalculation");
    $order_data->add_packages_data($data['packages'], $data['packageType']);

    $order_data_helper->fill_service_selection($order_data);

    try {
      $s = get_option("woocommerce_coleteonline_settings");
      $result = (new \ColeteOnline\ColeteOnlineClient(
        $s['client_id'],
        $s['client_secret']
      ))->get_prices($order_data->get_data());
      echo wp_json_encode($result);

    } catch (\ColeteOnline\ColeteOnlineServerErrorException $e) {
      echo wp_json_encode(array(
        'error' => 'ServerError',
        'message' => __('Server Error. Please try again or contact us if the error persists', 'coleteonline')
      ));
    } catch (\ColeteOnline\ColeteOnlineBadRequestException $e) {
      $validationErrors = $e->maybeGetValidationErrors();
      if ($validationErrors === false) {
        echo wp_json_encode(array(
          'error' => 'BadRequestError',
          'message' => __('The request is invalid', 'coleteonline')
        ));
      } else {
        echo wp_json_encode(array(
          'error' => 'BadRequestError',
          'message' => __('The request is invalid', 'coleteonline'),
          'validationErrors' => $validationErrors
        ));
      }
    } catch (Exception $e) {
      echo wp_json_encode(array(
        'error' => 'Error',
        'message' => __('An unexpected error occured', 'coleteonline')
      ));
    }

    wp_die();
  }

  private function respond_with_fragment($order, $meta_key) {
    $coleteonline_shipping_meta = $order->get_meta('_coleteonline_courier_order', false);
    foreach ($coleteonline_shipping_meta as $shipping_meta) {
      $shipping_meta = $shipping_meta->get_data()['value'];
      if (isset($shipping_meta["shippingKey"])) {
        if ($shipping_meta["shippingKey"] === $meta_key) {
          $order_result = $shipping_meta["result"];
        }
      } else {
        $order_result = $shipping_meta;
      }
    }

    ob_start();
    require COLETE_ONLINE_ROOT . "/admin/views/html-order-shipping-meta-box-courier-orders.php";
    $fragment = ob_get_clean();

    echo wp_json_encode(array(
      "fragments" => array(
        "courierOrders" => $fragment
      )
    ));
  }

  public function create_order() {
    if (!ColeteOnlineAdminUserGuard::check()) {
      wp_die(null, 401);
    }

    $data = wc_clean(wp_unslash($_POST['data']));

    $order = wc_get_order($data['orderId']);

    $order_data = new \ColeteOnline\ColeteOnlineOrderData();
    $order_data_helper = new \ColeteOnline\ColeteOnlineWcOrderDataHelper(null, $order);

    if ($order_data_helper->has_courier_order($data["shippingOrderKey"])) {
      $this->respond_with_fragment($order, $data["shippingOrderKey"]);
      wp_die();
    }

    $order_data->add_address_id('sender', $data['fromAddressId']);
    $order_data_helper->fill_recipient_shipping_data($order_data);

    $repaymentAmount = floatval($data['extraOptions']['repaymentAmount']);
    if ($repaymentAmount > 0) {
      $order_data->add_repayment_option($repaymentAmount);
    }

    $insuranceAmount = floatval($data['extraOptions']['insuranceAmount']);
    if ($insuranceAmount > 0) {
      $order_data->add_insurance_option($insuranceAmount);
    }

    $order_data->add_client_reference_option(
      $order_data_helper->get_client_reference()
    );

    if ($data['extraOptions']['openAtDelivery'] === 'true') {
      $order_data->add_open_at_delivery_option();
    }

    $order_data->add_base_currency_option(
      $order_data_helper->get_order_currency(),
      "RON"
    );

    $order_data->add_packages_content($data['content']);
    $order_data->add_packages_data($data['packages'], $data['packageType']);

    if ($data['selectedFromShippingPointId']) {
      $order_data->add_shipping_point('sender',
                                      $data['selectedFromShippingPointId']);
    }

    if ($data['selectedToShippingPointId']) {
      $order_data->add_shipping_point('recipient',
                                      $data['selectedToShippingPointId']);
    }

    $order_data->add_service_selection('directId', [$data['selectedCourierId']]);

    try {
      $s = get_option("woocommerce_coleteonline_settings");
      $result = (new \ColeteOnline\ColeteOnlineClient(
        $s['client_id'],
        $s['client_secret']
      ))->create_order($order_data->get_data());

      if (substr($data["shippingOrderKey"], 0, 6) === "extra-") {
        $count = $order->get_meta('_coleteonline_courier_extra_order_count', true);
        if (empty($count)) {
          $count = 0;
        }
        $data["shippingOrderKey"] = "extra-" . $count;
        $res = update_post_meta($data['orderId'],
                        '_coleteonline_courier_extra_order_count',
                        $count + 1);
      }

      $data = array(
        "shippingKey" => $data["shippingOrderKey"],
        "result" => $result
      );

      $order->add_meta_data("_coleteonline_courier_order", $data);
      $order->save_meta_data();

      $awb = $result['awb'];
      $estimated_pick_up = $result['estimatedPickUpDate'];
      $order->add_order_note(
        __("Order AWB ($awb) created. Estimated pick up date: $estimated_pick_up.", "coleteonline")
      );

      if (get_option('coleteonline_order_add_custom_shipping_status') === 'yes' &&
          get_option('coleteonline_order_change_to_shipping_status') === 'yes') {
        $order->update_status(
          'wc-col-shipping-init',
          __("Order status changed to Shipping", "coleteonline"),
          true
        );
      }

      $this->respond_with_fragment($order, $data["shippingKey"]);

    } catch (\ColeteOnline\ColeteOnlineServerErrorException $e) {
      echo wp_json_encode(array(
        'error' => 'ServerError',
        'message' => __('Server Error. Please try again or contact us if the error persists', 'coleteonline')
      ));
    } catch (\ColeteOnline\ColeteOnlineBadRequestException $e) {
      $validationErrors = $e->maybeGetValidationErrors();
      if ($validationErrors === false) {
        echo wp_json_encode(array(
          'error' => 'BadRequestError',
          'message' => __('The request is invalid', 'coleteonline')
        ));
      } else {
        echo wp_json_encode(array(
          'error' => 'BadRequestError',
          'message' => __('The request is invalid', 'coleteonline'),
          'validationErrors' => $validationErrors
        ));
      }
    } catch (Exception $e) {
      echo wp_json_encode(array(
        'error' => 'Error',
        'message' => __('An unexpected error occured', 'coleteonline')
      ));
    }

    wp_die();
  }

  public function add_extra_courier_order() {
    if (!ColeteOnlineAdminUserGuard::check()) {
      wp_die(null, 401);
    }

    $data = wc_clean(wp_unslash($_POST['data']));
    $order = wc_get_order($data['orderId']);
    $count = $order->get_meta('_coleteonline_courier_extra_order_count', true);
    if (empty($count)) {
      $count = 0;
    }

    if ($count > 5) {
      echo array("error" => true);
      wp_die();
    }

    $is_extra = true;
    ob_start();
    require COLETE_ONLINE_ROOT .
      "/admin/views/html-order-shipping-meta-box.php";
    $fragment = ob_get_clean();

    echo wp_json_encode(array(
      "fragments" => array(
        "extraOrder" => $fragment
      )
    ));
    wp_die();
  }

  public function get_order_label() {
    if (!ColeteOnlineAdminUserGuard::check()) {
      wp_die(null, 401);
    }

    $data = wc_clean(wp_unslash($_GET['data']));
    try {
      $s = get_option("woocommerce_coleteonline_settings");
      $result = (new \ColeteOnline\ColeteOnlineClient(
        $s['client_id'],
        $s['client_secret']
      ))->get_order_file($data['uniqueId'], array("formatType" => $data['formatType']));
      echo json_encode($result);

    } catch (Exception $e) {
      echo wp_json_encode(array(
        'error' => 'Error',
        'message' => __('An unexpected error occurred', 'coleteonline')
      ));
    }
    wp_die();
  }

  public function register_custom_statuses($status_list) {
    if (get_option("coleteonline_order_add_custom_shipping_status" ,'no') !== 'yes') {
      return $status_list;
    }
    $shipping_status = array(
      'label'                     => _x('Shipping', 'Order status', 'coleteonline'),
      'public'                    => false,
      'exclude_from_search'       => false,
      'show_in_admin_all_list'    => true,
      'show_in_admin_status_list' => true,
      'label_count'               => _n_noop('Shipping <span class="count">(%s)</span>', 'Shipping<span class="count">(%s)</span>', 'coleteonline')
    );
    $offset = array_search('wc-on-hold', array_keys($status_list)) + 1;

    $status_list = array_merge(
      array_slice($status_list, 0, $offset),
      array('wc-col-shipping-init' => $shipping_status),
      array_slice($status_list, $offset, null)
    );

    return $status_list;
  }

  function hide_shipping_meta($hidden) {
    $hidden[] = "co_service_selections";
    return $hidden;
  }

  function add_custom_order_statuses($order_statuses) {
    if (get_option("coleteonline_order_add_custom_shipping_status" ,'no') !== 'yes') {
      return $order_statuses;
    }

    $offset = array_search('wc-on-hold', array_keys($order_statuses)) + 1;

    $order_statuses = array_merge(
      array_slice($order_statuses, 0, $offset),
      array('wc-col-shipping-init' => _x('Shipping', 'Order status', 'coleteonline')),
      array_slice($order_statuses, $offset, null)
    );

    return $order_statuses;
  }

  function auto_generate_order_status_on_hold_to_processing($order_id, $order) {
    if (get_option('coleteonline_order_auto_create_order', 'no') !== 'yes' ) {
      return;
    }

    if (get_option('coleteonline_order_auto_create_on_status_on-hold_to_processing', 'no') !== 'yes' ) {
      return;
    }

    $this->auto_generate_order($order_id, $order);
  }

  function auto_generate_order($order_id, $order) {
    if (!$order) {
      return;
    }

    if (get_option('coleteonline_order_auto_create_order', 'no') !== 'yes' ) {
      return;
    }

    $this->generate_order($order_id, $order);
  }

  private function generate_order($order_id, $order) {
    if (!isset($order)) {
      if (!isset($order_id)) {
        return false;
      }
      $order = wc_get_order($order_id);
    }

    $order_data = new \ColeteOnline\ColeteOnlineOrderData();
    $order_data_helper = new \ColeteOnline\ColeteOnlineWcOrderDataHelper(null, $order);

    if (!$order_data_helper->is_colete_online_order()) {
      return false;
    }

    if ($order_data_helper->has_courier_order("main-0")) {
      return false;
    }

    $order_data->add_address_id('sender',
      get_option('coleteonline_default_shipping_address_id'));
    $order_data_helper->fill_recipient_shipping_data($order_data);

    $repaymentAmount = $order_data_helper->get_repayment_amount();
    if ($repaymentAmount > 0) {
      $order_data->add_repayment_option($repaymentAmount);
    }

    $insuranceAmount = $order_data_helper->get_insurance_amount();
    if ($insuranceAmount > 0) {
      $order_data->add_insurance_option($insuranceAmount);
    }

    if ($order_data_helper->get_open_at_delivery()) {
      $order_data->add_open_at_delivery_option();
    }

    $order_data->add_client_reference_option(
      $order_data_helper->get_client_reference()
    );

    $order_data_helper->set_packages($order_data);
    $order_data_helper->set_final_service_selection($order_data);


    try {
      $s = get_option("woocommerce_coleteonline_settings");
      $result = (new \ColeteOnline\ColeteOnlineClient(
        $s['client_id'],
        $s['client_secret']
      ))->create_order($order_data->get_data());

      $order->add_meta_data("_coleteonline_courier_order", $result);
      $order->save_meta_data();

      $awb = $result['awb'];
      $estimated_pick_up = $result['estimatedPickUpDate'];
      $order->add_order_note(
        __("Order AWB ($awb) created automatically. Estimated pick up date: $estimated_pick_up.", "coleteonline")
      );

      if (get_option('coleteonline_order_add_custom_shipping_status') === 'yes' &&
          get_option('coleteonline_order_change_to_shipping_status') === 'yes') {
        if ($order->get_status() !== 'wc-col-shipping-init') {
          $order->update_status(
            'wc-col-shipping-init',
            '',
            true
          );
        }
      }
      return array(
        'awb' => $result['awb'],
        'estimated_pick_up' => $result['estimatedPickUpDate']
      );
    } catch (Exception $e) {
      $order->add_order_note(
        __("Failed creating order automatically.", "coleteonline")
      );
      wc_get_logger()->error('Failed creating order automatically ' . $e->getMessage());
      return false;
    }
  }

  public function add_order_list_actions($actions, $order) {
    $order_data_helper = new \ColeteOnline\ColeteOnlineWcOrderDataHelper(null, $order);

    if (!$order_data_helper->has_courier_order("main-0")) {
      return $actions;
    }
    $actions['download_label'] = array(
      'url'    => '#order_unique_id=' . $order_data_helper->get_unique_id(),
      'name'   => __( 'Download', 'woocommerce' ),
      'action' => 'download_label',
    );
    return $actions;
  }

  public function bulk_actions_list_edit($actions) {
    $actions['coleteonline_create_orders'] =
          __("Create ColeteOnline courier orders", "coleteonline");

    return $actions;
  }

  public function handle_bulk_actions_list_edit($redirect_to, $action, $ids) {

    if ($action === 'coleteonline_create_orders') {
      $success = 0;
      $failure = 0;
      foreach ($ids as $id) {
        $created = $this->generate_order($id, null);
        if ($created !== false) {
          ++$success;
        } else {
          ++$failure;
        }
      }

      parse_str(substr($redirect_to, strpos($redirect_to, '?') + 1), $query);
      $redirect_to = add_query_arg(
        array(
          'post_type' => $query['post_type'],
          'page' => $query['page'],
          'bulk_action' => 'coleteonline_bulk_order_creation_handled',
          'success' => $success,
          'failure' => $failure,
          'ids' => join( ',', $ids ),
        ),
        substr($redirect_to, 0, strpos($redirect_to, '?'))
      );
    }

    return esc_url_raw( $redirect_to );
  }

  public function notice_after_bulk_action() {
    global $post_type, $pagenow;

    if (OrderUtil::custom_orders_table_usage_is_enabled()) {
      if (!isset( $_GET['page'])) { // WPCS: input var ok, CSRF ok.
        return;
      }
      $page = wc_clean( wp_unslash( $_GET['page']) );
      if ($page !== "wc-orders" ||
          !isset( $_REQUEST['bulk_action'] ) ) { // WPCS: input var ok, CSRF ok.
        return;
      }
    } else {
      if ( 'edit.php' !== $pagenow || 'shop_order' !== $post_type ||
        ! isset( $_REQUEST['bulk_action'] ) ) { // WPCS: input var ok, CSRF ok.
        return;
      }
    }

    $bulk_action = wc_clean( wp_unslash( $_REQUEST['bulk_action'] ) );

    if ($bulk_action === 'coleteonline_bulk_order_creation_handled') {
      $success = isset( $_REQUEST['success'] ) ? absint( $_REQUEST['success'] ) : 0;
      $failure = isset( $_REQUEST['failure'] ) ? absint( $_REQUEST['failure'] ) : 0;
      $success_message = '';
      if ($success > 0) {
        $success_message = sprintf( _n( 'Created AWB for %d order.', 'Created AWB for %d orders.', $success, 'coleteonline' ), number_format_i18n( $success ) );
      }
      $failure_message = '';
      if ($failure > 0) {
        $failure_message = sprintf( _n( 'Failed creating AWB for %d order.', 'Failed creating AWB for %d orders.', $failure, 'coleteonline' ), number_format_i18n( $failure ) );
      }

      echo "<div class='updated'>" .
           "<p class='coleteonline-success'>" . esc_html($success_message) . "</p>" .
           "<p class='coleteonline-failure'>" . esc_html($failure_message) . "</p>" .
           "</div>";
    }

  }

  function add_fixed_point_to_order_email($order, $sent_to_admin, $plain_text, $email) {
    if (!is_a($order, 'WC_Order')) {
      return;
    }

    foreach ( $order->get_shipping_methods() as $shipping_method ) {
      if ($shipping_method->get_method_id() === "coleteonline") {
        $meta_data = $shipping_method->get_formatted_meta_data();
      }
    }
    if (isset($meta_data)) {
      foreach ($meta_data as $meta) {
        if ($meta->key === 'shipping_point_name') {
          echo sprintf(__('Delivery to fixed point: %s', 'coleteonline'),
            $meta->value
          );
        }
      }
      $op = get_option("colete_online_add_mail_tracking_link", "no");
      if ($op === "no") {
        return;
      }
      $coleteonline_shipping_meta = $order->get_meta('_coleteonline_courier_order', false);
      foreach ($coleteonline_shipping_meta as $shipping_meta) {
        $shipping_meta = $shipping_meta->get_data()['value'];
        if (isset($shipping_meta) && isset($shipping_meta["result"]) &&
            isset($shipping_meta["result"]["uniqueId"])) {
          $url = 'https://colete-online.ro/track/status/' . $shipping_meta['result']['uniqueId'];
          $tracking_text = __(' Track the shipping status: ', 'coleteonline');
          $tracking_href = ': <a href="' . $url . ' ">
          ' . $shipping_meta['result']['awb'] .'
          </a>';

            if ($plain_text) {
              echo $tracking_text . ' ' . $url . ' \n';
            } else {
              echo $tracking_text . $tracking_href . '<br>';
            }
        }
      }
    }
  }

}

