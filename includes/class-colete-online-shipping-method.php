<?php

defined( 'ABSPATH' ) || exit;

require_once COLETE_ONLINE_ROOT . "/lib/api/class-colete-online-authenticate.php";
require_once COLETE_ONLINE_ROOT . "/lib/class-colete-online-client.php";
require_once COLETE_ONLINE_ROOT . "/lib/exceptions/colete-online-http-fail-exception.php";
require_once COLETE_ONLINE_ROOT . "/lib/exceptions/colete-online-server-error-exception.php";
require_once COLETE_ONLINE_ROOT . "/includes/class-colete-online-shipping-method-options.php";

if(!class_exists('WP_List_Table')){
  require_once( ABSPATH . 'wp-admin/includes/screen.php' );
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class ColeteOnline_Shipping_Services_Table extends WP_List_Table {

  private $services;

  public function __construct($services) {
    parent::__construct();
    $this->services = $services;
  }

  public function prepare_items()
  {
      $columns = $this->get_columns();
      $hidden = $this->get_hidden_columns();
      $sortable = $this->get_sortable_columns();

      $data = $this->table_data();

      $this->_column_headers = array($columns, $hidden, $sortable);
      $this->items = $data;
  }

  /**
   * Override the parent columns method. Defines the columns to use in your listing table
   *
   * @return Array
   */
  public function get_columns() {
      $columns = array(
        'id' => '',
        'drag-handle' => '',
        'cb' => '<input type="checkbox"/>',
        'name' => 'Name',
    );

      return $columns;
  }

  /**
   * Define which columns are hidden
   *
   * @return Array
   */
  public function get_hidden_columns()
  {
      return array('id');
  }

  /**
   * Get the table data
   *
   * @return Array
   */
  private function table_data()
  {
    $data = array();

    $col = array_column( $this->services, "courierName" );
    array_multisort( $col, SORT_ASC, $this->services );

    foreach ($this->services as $service) {
      $data[] = array(
        "id" => $service['id'],
        "name" => $service['courierName'] . " " . $service['name'],
        "drag-handle" => "",
        "cb" => ""
      );
    }

    return $data;
  }

  /**
   * Define what data to show on each column of the table
   *
   * @param  Array $item        Data
   * @param  String $column_name - Current column name
   *
   * @return Mixed
   */
  public function column_default( $item, $column_name ) {
    switch( $column_name ) {
      case 'name':
      case 'drag-handle':
      case 'id':
          return $item[ $column_name ];
      default:
          return print_r( $item, true ) ;
    }
  }

  public function column_cb($item) {
    return "<input type='checkbox' id='coleteonline-service-id-${item['id']}'/>";
  }

  protected function get_primary_column_name() {
    return "name";
  }

  protected function get_table_classes() {
    $mode = get_user_setting( 'posts_list_mode', 'list' );
    $mode_class = esc_attr( 'table-view-' . $mode );
    return array( 'widefat','striped',
                  $mode_class,
                  $this->_args['plural'],
                  'shipping-services-table-coleteonline'
                );
  }

}


class ColeteOnline_Shipping_Method extends WC_Shipping_Method
{
  public $is_logged = false;

  /**
   * ColeteOnline_Shipping_Method constructor.
   *
   * @param int $instance_id
   */
  public function __construct($instance_id = 0)
  {
    parent::__construct($instance_id);

    $this->id = 'coleteonline';
    $this->title = __('ColeteOnline', 'coleteonline');
    $this->method_title = __('ColeteOnline', 'coleteonline');
    $this->method_description = __('Shipping Method for ColeteOnline', 'coleteonline');

    $this->configValidation = false;

    $this->supports = array(
      'settings',
      'shipping-zones',
      'instance-settings'
    );

    $this->init();

    $this->connect_colete_online();
  }

  private function init() {
    $this->form_fields = array(
      'client_id' => array(
        'title' => __('Client id', 'coleteonline'),
        'type' => 'text',
        'description' => __('The client_id provided by ColeteOnline', 'coleteonline'),
      ),
      'client_secret' => array(
        'title' => __('Client secret', 'coleteonline'),
        'type' => 'password',
        'description' => __('The client_secret provided by ColeteOnline', 'coleteonline'),
      )
    );

    $this->init_settings();

    add_action(
      'woocommerce_update_options_shipping_' . $this->id,
      array($this, 'process_admin_options')
    );
  }

  private function connect_colete_online() {
    try {
      $s = $this->settings;
      $needs_fetch = false;
      if (!empty($s['client_id']) && !empty($s['client_secret'])) {
        $login_hash = md5($s['client_id'] . $s['client_secret']);
        $stored_login_hash = get_transient("coleteonline_login_hash");
        if ($stored_login_hash === false || $stored_login_hash !== $login_hash) {
          $needs_fetch = true;
        }
        if ($needs_fetch === false) {
          $stored_services = get_transient("coleteonline_courier_services");
          if ($stored_services === false) {
            $needs_fetch = true;
          } else {
            if (count($stored_services)) {
              $this->services = $stored_services;
              $this->is_logged = true;
            } else {
              $needs_fetch = true;
            }
          }
        }
      } else {
        $this->is_logged = false;
      }
      if ($needs_fetch) {
        $this->services = (new \ColeteOnline\ColeteOnlineClient(
          $s['client_id'],
          $s['client_secret']
        ))->get_available_services();
        if (count($this->services)) {
          $this->is_logged = true;
          update_option('coleteonline_logged_in_once', 'yes');
          set_transient('coleteonline_login_hash',
                        $login_hash,
                        HOUR_IN_SECONDS);
          set_transient('coleteonline_courier_services',
                        $this->services,
                        HOUR_IN_SECONDS);
        }
      }
    } catch (Exception $e) {
      $this->is_logged = false;
    }
  }

  public function get_settings(string $section_name = "") {
    return \ColeteOnline\ShippingMethodOptions::get_options($section_name);
  }

  public function admin_options() {
    parent::admin_options();

    if (!$this->is_logged) {
      load_template(
        COLETE_ONLINE_ROOT . "/admin/partials/colete-online-admin-display.php",
        true,
        array(
          "is_logged" => $this->is_logged,
          "checked" => true
        )
      );
    } else {
      {
        $settings = $this->get_settings("service_selection");
        WC_Admin_Settings::output_fields($settings);
      }
      echo "<h2>" . __("Services table", "coleteonline") . "</h2>";
      $table = new ColeteOnline_Shipping_Services_Table($this->services);
      $table->prepare_items();
      $table->display();

      {
        $settings = $this->get_settings("price");
        WC_Admin_Settings::output_fields($settings);
      }
      {
        $settings = $this->get_settings("packaging");
        WC_Admin_Settings::output_fields($settings);
      }
      {
        $settings = $this->get_settings("order");
        WC_Admin_Settings::output_fields($settings);
      }
      {
        $settings = $this->get_settings("advanced");
        WC_Admin_Settings::output_fields($settings);
      }
      {
        $settings = $this->get_settings("delivery_to_fixed_points");
        WC_Admin_Settings::output_fields($settings);
        echo("<div class='coleteonline-change-delivery-to-fixed-points-settings-notice'>");
        echo("<p><b>" . __("You will find the settings for delivery to point services in the Colete-Online platform.", "coleteonline") . "</b></p>");
        echo("<a class='link' href='https://www.colete-online.ro/profile/integration/add?clientId=" . $this->settings["client_id"] . "&type=woocommerce'>" .  __("Click here to change your settings", "coleteonline") . "</a>");
        echo("</div>");
      }
    }
  }

  public function process_admin_options()
  {
    $post_data = $this->get_post_data();
    $s = $this->settings;
    $p = "woocommerce_coleteonline";
    if ($s["client_id"] != $post_data["{$p}_client_id"] ||
        $s["client_secret"] != $post_data["{$p}_client_secret"]) {
      (new \ColeteOnline\ColeteOnlineAuthenticate("", ""))->logout();
    }

    WC_Admin_Settings::save_fields($this->get_settings("service_selection"));
    WC_Admin_Settings::save_fields($this->get_settings("packaging"));
    WC_Admin_Settings::save_fields($this->get_settings("price"));
    WC_Admin_Settings::save_fields($this->get_settings("order"));
    // WC_Admin_Settings::save_fields($this->get_settings("address_validation"));
    $this->maybe_set_checkout_fields_defaults($post_data);

    WC_Admin_Settings::save_fields($this->get_settings("advanced"), $post_data);
    WC_Admin_Settings::save_fields($this->get_settings("delivery_to_fixed_points"));

    return parent::process_admin_options();
  }

  private function maybe_set_checkout_fields_defaults(&$data) {
    if (
      isset($data['coleteonline_checkout_form_type']) &&
      $data['coleteonline_checkout_form_type'] === 'coleteonline'
    ) {
      $settings = $this->get_settings('advanced');
      foreach ($settings as $setting) {
        if (
          isset($setting['parent']) &&
          $setting['parent'] === "coleteonline_checkout_form_type" &&
          isset($setting['default'])) {
            $data[$setting['id']] = $setting['default'];
        }
      }
    }
  }

  /**
   * @param array $package
   */
  public function calculate_shipping($package = array()) {

    $has_free_shipping = false;

    $free_shipping = intval(get_option("coleteonline_price_free_shipping"));
    $free_shipping_min_amount = intval(get_option("coleteonline_price_free_shipping_min_amount"));
    $free_shipping_classes = get_option("coleteonline_price_free_shipping_classes");

    $has_only_free_class = false;
    $has_free_shipping_coupon = false;

    $has_free_class = array();

    if (intval(get_option("coleteonline_price_free_shipping"))) {
      $type = intval(get_option("coleteonline_price_free_shipping"));
      if ($type === 1 || $type === 3 || $type === 5)  {
        $products_amount = WC()->cart->cart_contents_total +
              WC()->cart->tax_total;
        if ($products_amount > $free_shipping_min_amount) {
          $has_free_shipping = true;
        }
      }
      if ($type === 2 || $type === 3)  {
        if ($has_only_free_class) {
          $has_free_shipping = true;
        }
      }
      if ($type === 4 || $type === 5) {
        if ($has_free_shipping_coupon) {
          $has_free_shipping = true;
        }
      }
    }

    $contents = $package['contents'];

    $products = array();

    $has_shippable_products = false;
    $has_not_eligible_for_locker_products = false;

    $order_data = new \ColeteOnline\ColeteOnlineOrderData();

    $applied_coupons = WC()->cart->get_applied_coupons();
    foreach( $applied_coupons as $coupon_code ){
      $coupon = new WC_Coupon($coupon_code);
      if($coupon->get_free_shipping()){
          $has_free_shipping_coupon = true;
      }
    }

    foreach ( $contents as $values ) {
      if (!$values['data']->needs_shipping()) {
        continue;
      }
      $has_shippable_products = true;
      $product = array(
        "weight" => 1,
        "width" => 15,
        "length" => 15,
        "height" => 15
      );
      if ($free_shipping === 2 || $free_shipping === 3) {
        $shipping_class = $values['data']->get_shipping_class_id();
        $has_free_class[] = in_array($shipping_class, $free_shipping_classes);
      }

      if ( $values['data']->has_weight() ) {
        $product["weight"] = wc_get_weight($values['data']->get_weight(), 'kg');
      }
      if ( $values['data']->has_dimensions() ) {
        $dimensions = $values['data']->get_dimensions(false);
        foreach ($dimensions as $key => $dimension) {
          if (!empty($dimension)) {
            $product[$key] = wc_get_dimension($dimension, 'cm');
          }
        }
      }
      $prefix = "coleteonline-shipping-product";
      $product_id = $values['data']->get_id();

      $product_type = get_post_meta($product_id, "$prefix-type", true) ?: "default";
      $product_multiple = get_post_meta($product_id, "$prefix-multiple", true) ?: "1";
      $product_not_eligible_for_locker = get_post_meta($product_id, "$prefix-not-eligible-for-locker", true) ?? "";

      if (get_option("coleteonline_advanced_product_fields", "no") === "no") {
        $product_type = "default";
        $product_multiple = "1";
        $product_not_eligible_for_locker = "";
      }

      if ($product_not_eligible_for_locker === "on") {
        $has_not_eligible_for_locker_products = true;
      }

      $product["metadata"] = [array(
        "type" => $product_type,
        "multiple" => $product_multiple,
        "quantity" => $values['quantity'],
        "price" => wc_get_price_including_tax($values['data']),
        "product" => array(
          "sku" => $values['data']->get_sku()
        )
      )];
      if (get_option("coleteonline_packaging_method") === "each_in_package") {
        $product["quantity"] = 1;
        for ($i = 0; $i < $values['quantity']; ++$i) {
          $products[] = $product;
        }
      } else {
        $products[] = $product;
      }
    }

    if (count(array_unique($has_free_class)) === 1) {
      $has_only_free_class = current($has_free_class);
    }

    if (intval(get_option("coleteonline_price_free_shipping"))) {
      $type = intval(get_option("coleteonline_price_free_shipping"));
      if ($type === 1 || $type === 3 || $type === 5)  {
        if ($products_amount > $free_shipping_min_amount) {
          $has_free_shipping = true;
        }
      }
      if ($type === 2 || $type === 3)  {
        if ($has_only_free_class) {
          $has_free_shipping = true;
        }
      }
      if ($type === 4 || $type === 5) {
        if ($has_free_shipping_coupon) {
          $has_free_shipping = true;
        }
      }
    }

    if (isset(WC()->session)) {
      $key = "coleteonline_prevent_carrier_update";
      $prevent = WC()->session->get($key);
      WC()->session->set($key, 0);
      if ($prevent) {
        if (get_option("coleteonline_display_fallback_price", "no") === "yes") {
          $this->add_rate($this->get_fallback_rate($has_free_shipping));
        }
        return;
      }
    }

    if (get_option("coleteonline_packaging_method") === "all_in_package") {
      $weight = 0;
      $width = 0;
      $length = 0;
      $height = 0;
      $metadata = [];
      $separateProducts = [];
      foreach ($products as $product) {
        if ($product["metadata"][0]["type"] === "white") {
          $separateProducts[] = $product;
          continue;
        }
        $weight += $product["weight"] * $product["metadata"][0]["quantity"];
        $width = max($width, $product["width"]);
        $length = max($length, $product["length"]);
        $height = max($height, $product["height"]);
        $metadata[] = $product["metadata"][0];
      }
      $products = array_merge(
        count($metadata) ? array(
          array(
            "weight" => $weight,
            "width" => $width,
            "length" => $length,
            "height" => $height,
            "metadata" => $metadata
          )
        ) : array(),
        $separateProducts);
    }

    if (!$has_shippable_products) {
      return;
    }

    $dest = $package['destination'];

    $selection = \ColeteOnline\ColeteOnlineWcOrderDataHelper::get_services_selection();
    $order_data->add_service_selection($selection['service_selection'],
                                       $selection['services']);
    $package_type = 2;
    if (get_option("coleteonline_packaging_prefer_envelope") === "yes" &&
        count($products) === 1 &&
        $products[0]['weight'] <= 0.5) {
      $package_type = 1;
    }
    $order_data->add_packages_data($products, $package_type);
    $order_data->add_packages_content('PriceCalculation');

    if ($dest["state"] === "B") {
      $dest["state"] = $dest["city"];
      $dest["city"] = "Bucuresti";
    }

    $products_amount = WC()->cart->cart_contents_total +
              WC()->cart->tax_total;

    if (WC()->session->get('chosen_payment_method') === "cod") {
      $order_data->add_repayment_option($products_amount + WC()->cart->shipping_total);
    }

    $open_option = get_option("coleteonline_order_open_at_delivery");
    if ($open_option === "always" ||
       ($open_option === "user_choice" &&
        WC()->session->get('with_open_package') === "on")) {
      $order_data->add_open_at_delivery_option();
    }

    $insurance_op = get_option('coleteonline_order_insurance');
    if ($insurance_op === 'always' ||
      ($insurance_op === 'when_no_repayment' &&
      WC()->session->get('chosen_payment_method') !== "cod")) {
      $order_data->add_insurance_option($products_amount);
    }

    $currency = \ColeteOnline\ColeteOnlineWcOrderDataHelper::get_base_currency();
    $wc_currency = \ColeteOnline\ColeteOnlineWcOrderDataHelper::get_woocommerce_base_currency();
    $order_data->add_base_currency_option(
      $wc_currency,
      $currency
    );

    $order_data->add_address_id('sender', get_option("coleteonline_default_shipping_address_id"));
    $order_data->add_address_data('recipient', 'countryCode', $dest['country']);
    $order_data->add_address_data('recipient', 'postalCode', $dest['postcode']);
    $order_data->add_address_data('recipient', 'city', $dest['city']);
    $order_data->add_address_data('recipient', 'countyCode',
                                  ($dest['country'] === 'RO') ? $dest['state'] : '');
    $order_data->add_address_data('recipient', 'county',
                                  ($dest['country'] !== 'RO') ? $dest['state'] : '');
    $order_data->add_address_data('recipient', 'street', $dest['address']);
    $order_data->add_validation_strategy('recipient', 'priceMinimal');

    if ($has_not_eligible_for_locker_products === true) {
      $order_data->disable_shipping_points();
    }

    $s = $this->settings;
    try {
      $result = (new \ColeteOnline\ColeteOnlineClient(
        $s['client_id'],
        $s['client_secret']
      ))->get_prices($order_data->get_data());

      $list = $result['list'];
      if ($selection['sort'] === "orderByName" &&
          $selection['selection'] === "allowChoice") {
        uasort($list, function($a, $b) {
          $aName = $a["service"]["courierName"] . " " . $a["service"]["name"];
          $bName = $b["service"]["courierName"] . " " . $b["service"]["name"];
          return strnatcmp($aName, $bName);
        });
      }

      $standardServicesList = array_filter($list, function ($item) {
        $s = $item['service'];
        return !isset($s["shippingPoint"]);
      });

      $shippingPointsServicesList = array_filter($list, function ($item) {
        $s = $item['service'];
        return isset($s["shippingPoint"]);
      });

      $limit = get_option("coleteonline_service_selection_display_count");
      if ($selection['selection'] === "provided") {
        $limit = 1;
      }
      if (is_numeric($limit) && +$limit !== 0) {
        $standardServicesList = array_slice($standardServicesList, 0, +$limit);
      }

      $processedServices = [];

      if (get_option("coleteonline_display_delivery_to_fixed_points_first") === "yes") {
        if ($has_not_eligible_for_locker_products === false) {
          $this->add_delivery_to_fixed_points_services_rates($shippingPointsServicesList, $processedServices);
        }
        $this->add_standard_services_rates($standardServicesList, $processedServices, $has_free_shipping);
      } else {
        $this->add_standard_services_rates($standardServicesList, $processedServices, $has_free_shipping);
        if ($has_not_eligible_for_locker_products === false) {
          $this->add_delivery_to_fixed_points_services_rates($shippingPointsServicesList, $processedServices);
        }
      }

    } catch (\ColeteOnline\ColeteOnlineHttpFailException $e) {
      $opt = get_option('coleteonline_display_fallback_price', 'no');
      if ($opt === 'critical' || $opt === 'yes') {
        $this->add_rate($this->get_fallback_rate($has_free_shipping));
      }
    } catch (\ColeteOnline\ColeteOnlineServerErrorException $e) {
      $opt = get_option('coleteonline_display_fallback_price', 'no');
      if ($opt === 'critical' || $opt === 'yes') {
        $this->add_rate($this->get_fallback_rate($has_free_shipping));
      }
    } catch (Exception $e) {
      $opt = get_option('coleteonline_display_fallback_price', 'no');
      if ($opt === 'yes') {
        $this->add_rate($this->get_fallback_rate($has_free_shipping));
      }
    }
  }

  private function add_standard_services_rates($standardServicesList, $processedServices, $has_free_shipping) {
    foreach ($standardServicesList as $idx => $service) {
      $service_selections = "";
      if (isset($service["service"]["selections"])) {
        $service_selections = json_encode($service["service"]["selections"]);
      }
      $label = isset($service['displayName']) ? $service['displayName'] :
                $service["service"]["courierName"] . " " . $service["service"]["name"];
      $cost = isset($service['price']['displayPrice']) ? $service['price']['displayPrice'] :
                                                         $service['price']['noVat'];
      $rate = array(
        'id' => $this->id . "." . $service["service"]['id'] . "." .
                $service["service"]["courierName"] . $idx,
        'label' => $label,
        'cost' => $cost,
        'meta_data' => array(
          'service_id' => $service["service"]["id"],
          'service_name' => $service["service"]["courierName"] . " " .
                            $service["service"]["name"],
          'co_service_selections' => $service_selections
        )
      );

      if (get_option("coleteonline_service_selection_custom_name_toggle") === "yes") {
        $name = str_replace(
          array("[courierName]", "[serviceName]"),
          array($service["service"]["courierName"],
                $service["service"]["name"]),
          get_option("coleteonline_service_selection_custom_name"));
        $rate["label"] = $name;
      }

      if ($idx === 0) {
        if (get_option("coleteonline_service_selection_first_custom_name_toggle") === "yes") {
          $name = str_replace(
            array("[courierName]", "[serviceName]"),
            array($service["service"]["courierName"],
                  $service["service"]["name"]),
            get_option("coleteonline_service_selection_custom_name_first"));
          $rate["label"] = $name;
        }
      }

      if (get_option("coleteonline_advanced_aggregated_services_custom_name", "") !== "") {
        if (+$rate["meta_data"]["service_id"] === 0) {
          if ($service["service"]["name"] === "Combined") {
            $rate["label"] = get_option("coleteonline_advanced_aggregated_services_custom_name");
          }
        }
      }

      if (get_option("coleteonline_price_type") === "fixed_price") {
        $amount = floatval(get_option("coleteonline_price_fixed_price_amount"));
        if ($amount) {
          $rate["cost"] = $amount;
        }
      } else {
        $opt = get_option("coleteonline_price_add_fixed_amount");
        if ($opt) {
          $amount = floatval($opt);
          if ($amount) {
            $rate["cost"] += $amount;
          }
        }
        $opt = get_option("coleteonline_price_add_percent_amount");
        if ($opt) {
          $amount = floatval($opt);
          if ($amount) {
            $rate["cost"] = $rate["cost"] + ($rate["cost"] * ($amount / 100));
          }
        }
      }

      $opt = get_option("coleteonline_price_round_before_tax");
      if ($opt) {
        $round = floatval($opt);
        $rate["cost"] = ceil($rate["cost"] / $round) * $round;
      }

      if ($has_free_shipping) {
        $rate["cost"] = 0;
        if ($rate["cost"] === 0) {
          $text = get_option("coleteonline_price_free_shipping_after_name_text");
          if ($text) {
            $rate["label"] .= " " . $text;
          }
        }
      }

      $this->add_rate($rate);
    }
  }

  private function add_delivery_to_fixed_points_services_rates($shippingPointsServicesList, $processedServices) {
    $restrictedPaymentGateways = get_option("coleteonline_delivery_to_fixed_points_restricted_payment_gateways", array());
    $chosenPaymentMethod = WC()->session->get('chosen_payment_method');
    if (!in_array($chosenPaymentMethod, $restrictedPaymentGateways)) {
      foreach ($shippingPointsServicesList as $idx => $service) {
        $l = $shippingPointsServicesList;
        $serviceId = $service['service']['id'];
        if (in_array($serviceId, $processedServices)) {
          continue;
        }
        $processedServices[] = $serviceId;
        $rate = array(
          'id' => $this->id . "." . $service["service"]['id'] . "." .
                  $service["service"]["courierName"] . $idx,
          'label' => $service["service"]["displayName"],
          'cost' => $service["price"]["displayPrice"],
          'meta_data' => array(
            'service_id' => $service["service"]["id"],
            'service_name' => $service["service"]["courierName"] . " " .
                              $service["service"]["name"],
            'shipping_points_list' => array_map(function($s) {
              return $s['service']['shippingPoint'];
            },
              array_filter($l, function ($s) use ($serviceId) {
                return $s['service']['id'] === $serviceId;
              }))
          )
        );
        $this->add_rate($rate);
      }
    }
  }

  function get_fallback_rate($has_free_shipping = false) {
    $free_shipping_text = get_option("coleteonline_price_free_shipping_after_name_text");
    $fallback_service_name = get_option('coleteonline_fallback_service_name');
    $fallback_price = get_option('coleteonline_fallback_price_amount');
    $rate = array(
      'id' => $this->id . '.0.ColeteOnlineFallback',
      'label' => $has_free_shipping ? "$fallback_service_name $free_shipping_text" : $fallback_service_name,
      'cost' => $has_free_shipping ? 0 : $fallback_price,
      'meta_data' => array(
        'service_id' => '0',
        'service_name' => 'ColeteOnlineFallback'
      )
    );
    return $rate;
  }

}
