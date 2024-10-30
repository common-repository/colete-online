<?php

defined( 'ABSPATH' ) || exit;

require_once COLETE_ONLINE_ROOT . "/lib/class-colete-online-client.php";

class ColeteOnlineAddressFilters {

  public function __construct() {}

  private function change_fields($type, $fields, $country, $county, $city) {
    $countyName = "";
    try {
      if ($country === "RO") {
        if (strlen($city) === 2) {
          // TODO move in helper function
          if ($city[0] === "S" && is_numeric($city[1]) && $county === "B") {
            $countyName = "Sectorul " . $city[1];
            $city = "Bucuresti";
          } else {
            if (isset(WC()->countries->get_states()[$country])) {
              if (isset(WC()->countries->get_states()[$country][$county])) {
                $countyName = WC()->countries->get_states()[$country][$county];
              }
            }
          }
        } else {
          if (isset(WC()->countries->get_states()[$country])) {
            if (isset(WC()->countries->get_states()[$country][$county])) {
              $countyName = WC()->countries->get_states()[$country][$county];
            }
          }
        }
      }
    } catch (Exception $e) {}

    $separate_fields = get_option("coleteonline_address_separate_fields") === "yes";
    if ($separate_fields) {
      $cityOptions = array("" => "");
      if (
        isset($city) && isset($countyName) && $city === "Bucuresti" &&
        $countyName && $country === "RO"
      ) {
        $cityOptions = array($countyName => $countyName);
      } else if (isset($city) && $city && $country === "RO") {
        $cityOptions = array($city => $city);
      }

      if (get_option("coleteonline_address_city_field_type") === "coleteonline") {
        $fields[$type . '_locality'] = array(
          'type' => 'select',
          'label'     => __('City / Locality', 'coleteonline'),
          'placeholder'   => _x('City / Locality', 'placeholder', 'coleteonline'),
          'required'  => $country === 'RO',
          'class' => array('form-row-wide wc-enhanced-select hidden-field'),
          'priority' => 45,
          'options' => $cityOptions
        );
      }
    } else {
      $cityStateOptions = array("" => "");
      if (
        isset($countyName) && $countyName && isset($city) && $city &&
        $country === "RO"
      ) {
        $op = "$city, $countyName";
        $cityStateOptions = array($op => $op);
      }

      $fields[$type . '_city_state'] = array(
        'type' => 'select',
        'type' => 'select',
        'label'     => __('City and county', 'coleteonline'),
        'placeholder'   => _x('City and county', 'placeholder', 'coleteonline'),
        'required'  => $country === "RO",
        'class' => array('form-row-wide wc-enhanced-select hidden-field'),
        'priority' => 44,
        'options' => $cityStateOptions
      );
      if ($type === 'shipping') {
        $fields['shipping_state']['required'] = false;
      }
    }

    $street_op = get_option('coleteonline_address_street_field_type', 'displayWithAutocomplete');
    if ($street_op === 'displayWithAutocomplete') {
      $fields[$type . '_street'] = array(
        'type' => 'select',
        'label'     => __('Street', 'coleteonline'),
        'placeholder'   => _x('Street', 'placeholder', 'coleteonline'),
        'required'  => $country === "RO",
        'class' => array('form-row-wide wc-enhanced-select hidden-field'),
        'priority' => 50,
        'options' => array("" => ""),
        'autocomplete' => "address-level1"
      );
    } else {
      $fields[$type . '_address_1']['label'] = __('Street', 'coleteonline');
      $fields[$type . '_address_1']['placeholder'] = _x('Street', 'placeholder', 'coleteonline');
      $fields[$type . '_address_1']['required'] = $country === "RO";
    }

    $street_number_op = get_option('coleteonline_address_street_number_field_type', 'displayMandatory');
    if ($street_number_op !== "no" || is_admin()) {
      $fields[$type . '_street_number'] = array(
        'label'     => __('Number', 'coleteonline'),
        'maxlength' => 10,
        'placeholder'   => _x('Number', 'placeholder', 'coleteonline'),
        'required'  => ($country === "RO" && $street_number_op === "displayMandatory") ? true : false,
        'class' => array('form-row-last hidden-field'),
        'priority' => 51,
        'autocomplete' => 'none'
      );
    }

    if ($type === "billing") {
      if (get_option("coleteonline_address_email_show_first") === "yes") {
        $fields['billing_email']['priority'] = 10;
      }
    }

    if ($type === "shipping") {
      if (get_option(("coleteonline_address_shipping_show_phone")) === "yes") {
        $fields = $this->add_shipping_phone_to_checkout($fields);
      }
    }

    return $fields;
  }

  private function add_shipping_phone_to_checkout( $fields ) {
    $defaults = array(
      'label'        => __( 'Phone', 'coleteonline' ),
      'type'         => 'tel',
      'required'     => false,
      'class'        => array( 'form-row-wide' ),
      'clear'        => true,
      'validate'     => array( 'phone' ),
      'autocomplete' => 'tel',
    );

    // Use existing settings if the field exists.
    $field = isset( $fields['shipping_phone'] )
      ? array_merge( $defaults, $fields['shipping_phone'] )
      : $defaults;

    // Enforce phone type, autocomplete, and validation.
    $field['type']         = 'tel';
    $field['autocomplete'] = 'tel';
    if ( ! in_array( 'tel', $field['validate'], true ) ) {
      $field['validate'][] = 'tel';
    }

    // Add to the list.
    $fields['shipping_phone'] = $field;
    return $fields;
  }

  public function change_billing_fields($fields) {
    try {
      $country = "";
      if ($fields["billing_state"]["country"]) {
        $country = $fields["billing_state"]["country"];
      } else if (!is_null(WC()->customer)) {
        $country = WC()->customer->get_billing_country();
      }
    } catch (Exception $err) {
      $country = "";
    }
    try {
      $city = "";
      if (!is_null(WC()->customer)) {
        $city = WC()->customer->get_billing_city();
      }
    } catch (Exception $err) {
      $city = "";
    }
    try {
      $county = "";
      if (!is_null(WC()->customer)) {
        $county = WC()->customer->get_billing_state();
      }
    } catch (Exception $err) {
      $county = "";
    }

    return $this->change_fields("billing", $fields, $country, $county, $city);
  }

  public function change_shipping_fields($fields) {
    try {
      $country = "";
      if ($fields["shipping_state"]["country"]) {
        $country = $fields["shipping_state"]["country"];
      } else if (!is_null(WC()->customer)) {
        $country = WC()->customer->get_shipping_country();
      }
    } catch (Exception $err) {
      $country = "";
    }
    try {
      $city = "";
      if (!is_null(WC()->customer)) {
        $city = WC()->customer->get_shipping_city();
      }
    } catch (Exception $err) {
      $city = "";
    }
    try {
      $county = "";
      if (!is_null(WC()->customer)) {
        $county = WC()->customer->get_shipping_state();
      }
    } catch (Exception $err) {
      $county = "";
    }

    return $this->change_fields("shipping", $fields, $country, $county, $city);
  }

  public function change_country_locale($fields) {
    $fields['RO']['state']['priority'] = 44;

    if (get_option("coleteonline_address_postal_code_show") === "before") {
      $fields['RO']['postcode']['priority'] = 43;
    } else if (get_option("coleteonline_address_postal_code_show") === "no") {
      $fields['RO']['postcode']['class'][] = 'hidden-field';
    }

    return $fields;
  }

  public function localize_address_format($formats) {
    if (get_option('coleteonline_address_street_number_field_type') !== 'no' ||
      is_admin()) {
      $formats['RO'] = "{name}\n{company}\n{address_1}, {street_number}\n" .
                       "{address_2}\n{city}\n{state}\n{postcode}\n{country}";
    }
    return $formats;
  }

  public function formatted_address_replacements($replace, $args) {
    if (get_option('coleteonline_address_street_number_field_type') !== 'no' ||
      is_admin()) {
      $replace['{street_number}'] = isset($args['street_number']) ?
      $args['street_number'] : '';
    }
    $city = $args['city'];
    if (in_array($city, array('S1', 'S2', 'S3', 'S4', 'S5', 'S6'))) {
      $city = str_replace("S", "Sectorul ", $city);
    }
    $replace['{city}'] = $city;
    return $replace;
  }

  public function complete_order_address_data($fields, $type, WC_Order $order) {
    $fields['street'] = $order->get_meta("_{$type}_street");
    if (get_option('coleteonline_address_street_number_field_type') !== 'no' ||
        is_admin()) {
      $fields['street_number'] = $order->get_meta("_{$type}_street_number");
    }
    return $fields;
  }

  public function complete_my_account_address_data($fields, $customer_id, $type) {

    if (get_option('coleteonline_address_street_number_field_type') !== 'no') {
      $fields['street_number'] = get_user_meta($customer_id, "{$type}_street_number", true);
    }

    return $fields;
  }

  public function complete_billing_address_field($value, WC_Order $order) {
    if ($order->get_meta("_shipping_street_number")) {
      $value .= ", " . $order->get_meta("_shipping_street_number");
    }
    return $value;
  }

}
