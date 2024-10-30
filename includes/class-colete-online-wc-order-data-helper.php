<?php

namespace ColeteOnline;

defined( 'ABSPATH' ) || exit;

class ColeteOnlineWcOrderDataHelper {

  public function __construct($order_id, $order) {
    if (isset($order_id)) {
      $order = wc_get_order($order_id);
    }
    $this->order = $order;
  }

  public function is_colete_online_order() {
    foreach ( $this->order->get_shipping_methods() as $shipping_method ) {
      if ($shipping_method->get_method_id() === "coleteonline") {
        $meta_data = $shipping_method->get_formatted_meta_data();
      }
    }
    return isset($meta_data);
  }

  public function get_selected_service() {
    foreach ( $this->order->get_shipping_methods() as $shipping_method ) {
      if ($shipping_method->get_method_id() === "coleteonline") {
        $meta_data = $shipping_method->get_formatted_meta_data();
      }
    }
    if (isset($meta_data)) {
      foreach ($meta_data as $meta) {
        if ($meta->key === 'service_id') {
          return $meta->value;
        }
      }
    }
    return false;
  }


  public function get_selected_shipping_point() {
    foreach ( $this->order->get_shipping_methods() as $shipping_method ) {
      if ($shipping_method->get_method_id() === "coleteonline") {
        $meta_data = $shipping_method->get_formatted_meta_data();
      }
    }
    if (isset($meta_data)) {
      foreach ($meta_data as $meta) {
        if ($meta->key === 'shipping_point_id') {
          return $meta->value;
        }
      }
    }
    return false;
  }

  public function get_packages($extended = false) {
    $packages = array();
    $packaging_option = get_option("coleteonline_packaging_method");
    $line_items = $this->order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );
    if ($packaging_option === "all_in_package") {
      $packages[1] = array();
      foreach ( $line_items as $item_id => $item ) {
        // for ($i = 0; $i < $item->get_quantity(); ++$i) {
        $packages[1][] = $item;
        // }
      }
    } else if ($packaging_option === "each_in_package") {
      $cnt = 0;
      foreach ( $line_items as $item_id => $item ) {
        for ($i = 0; $i < $item->get_quantity(); ++$i) {
          $packages[$cnt++] = array($item);
        }
      }
    }

    $final_packages = array();
    $products = array();
    $stock_products = array();
    foreach ($packages as $contents) {
      $weight = 0;
      $width = 0;
      $length = 0;
      $height = 0;
      foreach ($contents as $item) {
        $product = $item->get_product();
        $weight += wc_get_weight($product->get_weight(), "kg") * $item->get_quantity();
        $width = max($width, wc_get_dimension($product->get_dimensions(false)["width"], "cm"));
        $length = max($length, wc_get_dimension($product->get_dimensions(false)["length"], "cm"));
        $height = max($height, wc_get_dimension($product->get_dimensions(false)["height"], "cm"));
        $products = array_merge($products,
                                array_fill(0, $item->get_quantity(), trim($item->get_name())));
        $stock_products[] = trim($product->get_sku() ? $product->get_sku() : '#' . $product->get_id());

        $prefix = "coleteonline-shipping-product";
        $product_id = $product->get_id();

        $product_type = get_post_meta($product_id, "$prefix-type", true) ?: "default";
        $product_multiple = get_post_meta($product_id, "$prefix-multiple", true) ?: "1";

        if (get_option("coleteonline_advanced_product_fields", "no") === "no") {
          $product_type = "default";
          $product_multiple = "1";
        }

        $metadata[] = array(
          "type" => $product_type,
          "multiple" => $product_multiple,
          "quantity" => $item->get_quantity(),
          "price" => wc_get_price_including_tax($product),
          "product" => array(
            "sku" => $product->get_sku()
          )
        );
      }

      $final_packages[] = array(
        'weight' => $weight,
        'width' => $width,
        'length' => $length,
        'height' => $height,
        'metadata' => $metadata
      );
    }
    $counts = array_count_values($products);
    $contents = array();
    foreach ($counts as $name => $count) {
      $multiplier = ($count > 1 ? $count . ' x ' : '');
      $contents[] =  $multiplier . $name;
    }

    $extended_info = array();
    if ($extended) {
      $counts = array_count_values($stock_products);
      $stock_contents = array();
      foreach ($counts as $name => $count) {
        $multiplier = ($count > 1 ? $count . ' x ' : '');
        $stock_contents[] =  $multiplier . $name;
      }
      $extended_info['stock_contents'] = implode(", ", $stock_contents);
    }

    $contentsString = implode(", ", $contents);
    $autocompleteType = get_option("coleteonline_packaging_content_autocomplete");

    if ($autocompleteType === "fixed_on_limit" && strlen($contentsString) > 50) {
      $contentsString = get_option("coleteonline_packaging_content_autocomplete_fixed_string");
    } else if ($autocompleteType === "fixed") {
      $contentsString = get_option("coleteonline_packaging_content_autocomplete_fixed_string");
    }

    $package_type = 2;
    if (get_option("coleteonline_packaging_prefer_envelope") === "yes" &&
        count($final_packages) === 1 &&
        $final_packages[0]['weight'] <= 0.5) {
      $package_type = 1;
    }

    return array_merge(
      $extended_info,
      array(
        "content" => $contentsString,
        "type" => $package_type,
        "packages" => $final_packages
      )
    );
  }

  public function get_open_at_delivery() {
    switch (get_option('coleteonline_order_open_at_delivery', 'no')) {
      case 'no':
        return false;
      case 'always':
        return true;
      case 'user_choice':
        if ($this->order->get_meta('_coleteonline_open_package') === 'on') {
          return true;
        }
    }
    return false;
  }

  public function get_repayment_amount() {
    if ($this->order->get_payment_method() === 'cod') {
      return $this->get_total();
    }
    return false;
  }

  public function get_insurance_amount() {
    switch (get_option('coleteonline_order_insurance', 'no')) {
      case 'no':
        return false;
      case "always":
        return $this->get_total();
      case "when_no_repayment":
        if ($this->order->get_payment_method() === "cod") {
          return false;
        } else {
          return $this->get_total();
        }
    }
    return false;
  }

  public function get_client_reference() {
    $reference = '';
    if (get_option('coleteonline_order_client_reference') !== '') {
      $reference = get_option('coleteonline_order_client_reference');

      $reference = str_replace(array('[orderId]'), array($this->order->get_id()), $reference);
    }

    return $reference;
  }

  public static function get_woocommerce_base_currency() {
    return get_woocommerce_currency();
  }

  public static function get_base_currency() {
    if (get_option('coleteonline_price_currency_type', 'shop_base') === 'custom') {
      return get_option('coleteonline_price_base_currency', 'RON');
    }
    return get_woocommerce_currency();
  }

  public function get_order_currency() {
    return $this->order->get_currency();
  }

  public function has_courier_order($meta_key) {
    $shipping_meta = $this->order->get_meta('_coleteonline_courier_order', false);
    foreach ($shipping_meta as $meta) {
      $meta = $meta->get_data()['value'];
      if (isset($meta["shippingKey"])) {
        if ($meta["shippingKey"] === $meta_key) {
          return true;
        }
      } else {
        return true;
      }
    }
    return false;
  }

  public function get_unique_id() {
    $shipping_meta = $this->order->get_meta('_coleteonline_courier_order', false);
    foreach ($shipping_meta as $meta) {
      $meta = $meta->get_data()['value'];
      if (isset($meta["shippingKey"])) {
        return $meta["result"]["uniqueId"];
      } else {
        return $meta["uniqueId"];
      }
    }
  }

  public function fill_recipient_shipping_data($order_data,
                                               $validation_strategy = 'minimal') {
    $shipping = $this->order->get_address('shipping');

    $order_data->add_contact_data('recipient', 'name',
      $this->order->get_formatted_shipping_full_name());
    $order_data->add_contact_data('recipient', 'company', $shipping['company']);
    if (method_exists($this->order, "get_shipping_phone")) {
      $phone = $this->order->get_shipping_phone() ?
        $this->order->get_shipping_phone() :
        $this->order->get_billing_phone();
      $order_data->add_contact_data('recipient', 'phone', $phone);
    } else {
      $order_data->add_contact_data('recipient', 'phone',
        $this->order->get_billing_phone());
    }

    if (get_option('coleteonline_order_send_email_to_recipient', 'yes') === 'yes') {
      $order_data->add_contact_data('recipient', 'email',
        $this->order->get_billing_email());
    }

    if ($shipping["state"] === "B") {
      $shipping["state"] = $shipping["city"];
      $shipping["city"] = "Bucuresti";
    }

    $order_data->add_address_data('recipient', 'countryCode', $shipping['country']);
    $order_data->add_address_data('recipient', 'postalCode', $shipping['postcode']);
    $order_data->add_address_data('recipient', 'city', $shipping['city']);
    if ($shipping['country'] === "RO") {
      $order_data->add_address_data('recipient', 'countyCode', $shipping['state']);
    } else {
      $order_data->add_address_data('recipient', 'county', $shipping['state']);
    }
    $order_data->add_address_data('recipient', 'street', $shipping['address_1']);
    if (strlen($shipping['street_number']) > 10) {
      $order_data->add_address_data('recipient', 'number', '-');
      $shipping['address_2'] = $shipping['street_number'] . ' ' . $shipping['address_2'];
    } else {
      $order_data->add_address_data('recipient', 'number', $shipping['street_number']);
    }
    $order_data->add_address_data('recipient', 'landmark', $shipping['address_2']);
    $order_data->add_validation_strategy('recipient', $validation_strategy);

    $shipping_point_id = $this->get_selected_shipping_point();
    if ($shipping_point_id) {
      if (!strlen($shipping['street_number'])) {
        $order_data->add_address_data('recipient', 'number', '-');
      }
      // $order_data->add_shipping_point('recipient', $shipping_point_id);
    }

    $post = get_post($this->order->get_id());
    $order_data->add_address_data(
      'recipient',
      'additionalInfo',
      substr($post->post_excerpt, 0, 100)
    );
  }

  public static function get_services_selection() {
    $selection = get_option("coleteonline_courier_selection_choice_type");
    $sort = get_option("coleteonline_courier_display_order_type");
    $service_selection = "bestPrice";
    if ($selection === "allowChoice") {
      if ($sort === "orderByPrice") {
        $service_selection = "bestPrice";
      } elseif ($sort === "orderByGrade") {
        $service_selection = "grade";
      } else {
        $service_selection = "directId";
      }
    } else if ($selection == "provided") {
      $service_selection = get_option("coleteonline_service_selection_type");
    }
    $services = json_decode(get_option("coleteonline_service_list_hidden"));
    return array(
      'service_selection' => $service_selection,
      'services' => $services,
      'sort' => $sort,
      'selection' => $selection
    );
  }

  public function fill_service_selection($order_data) {
    $result = self::get_services_selection();
    $order_data->add_service_selection($result['service_selection'], $result['services']);
  }

  public function set_final_service_selection($order_data) {
    $order_data->add_service_selection('directId',
                                       array($this->get_selected_service()));
  }

  public function set_packages($order_data) {
    $packages = $this->get_packages();
    $order_data->add_packages_content($packages['content']);
    $order_data->add_packages_data($packages['packages'], $packages['type']);
  }

  private function get_total() {
    return $this->order->get_total();
  }

}