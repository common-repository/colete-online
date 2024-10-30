<?php

namespace ColeteOnline;

defined( 'ABSPATH' ) || exit;

class ShippingMethodOptions {

  public static function get_options($section_name) {
    if ($section_name == "service_selection") {
      $settings =
        array(
          array(
            'title' => __('Service selection', 'coleteonline' ),
            'desc'  => __('The options to choose how the shipping method is displayed to the user',
                          'coleteonline'),
            'type'  => 'title',
            'id'    => 'service_selection_options',
          ),
          array(
            'title'    => __('Testing mode', 'coleteonline'),
            'desc'     => __('Use the testing server to create orders.',
                            'coleteonline'),
            'id'       => 'coleteonline_courier_testing',
            'type'     => 'select',
            'default'  => 'no',
            'class'    => 'wc-enhanced-select',
            'css'      => 'min-width:300px;',
            'options' => array(
              'no' => __('No', 'coleteonline'),
              'yes' => __('Yes', 'coleteonline')
            )
          ),
          array(
            'title'    => __('Selection type', 'coleteonline'),
            'desc'     => __('Provide a shipping method or allow the user to choose',
                            'coleteonline'),
            'id'       => 'coleteonline_courier_selection_choice_type',
            'type'     => 'select',
            'default'  => '',
            'class'    => 'wc-enhanced-select',
            'css'      => 'min-width:300px;',
            'options' => array(
              'allowChoice' => __('Allow user choice', 'coleteonline'),
              'provided' => __('Provide a shipping method', 'coleteonline')
            )
          ),
          array(
            'title'    => __('Display order', 'coleteonline'),
            'desc'     => __('Configure the order in which the services are displayed to the client',
                            'coleteonline'),
            'id'       => 'coleteonline_courier_display_order_type',
            'type'     => 'select',
            'default'  => '',
            'class'    => 'wc-enhanced-select',
            'css'      => 'min-width:300px;',
            'options' => array(
              'orderByPrice' => __('Order by price', "coleteonline"),
              'orderByGrade' => __('Order by grade', "coleteonline"),
              'orderByName' => __('Order by name', "coleteonline"),
              'orderByServiceTable' => __('Use the order in the services table below', "coleteonline"),
            )
          ),
          array(
            'title'    => __('Service selection', 'coleteonline'),
            'desc'     => __('Configure how the services are displayed',
                            'coleteonline'),
            'id'       => 'coleteonline_service_selection_type',
            'type'     => 'select',
            'default'  => '',
            'class'    => 'wc-enhanced-select',
            'css'      => 'min-width:300px;',
            'options' => array(
              'directId' => __('Direct select', "coleteonline"),
              'bestPrice' => __('Best price', "coleteonline"),
              'grade' => __('Grade', "coleteonline")
            )
          ),
          array(
            'title'    => __('Display only the first services', 'coleteonline'),
            'desc'     => __('Indicate how many courier services should be presented to the user. For example if the previous column has "bestPrice" and here the input is 2, only the first 2 cheapest couriers will be shown. Leave 0 or empty for all',
                            'coleteonline'),
            'id'       => 'coleteonline_service_selection_display_count',
            'type'     => 'text',
            'default'  => '',
            'class'    => 'wc-enhanced-input',
            'css'      => 'min-width:300px;'
          ),
          array(
            'title'    => __('Show custom name for available services', 'coleteonline'),
            'desc'     => __('Enable the display of a custom name for the available shipping services',
                            'coleteonline'),
            'id'       => 'coleteonline_service_selection_custom_name_toggle',
            'type'     => 'checkbox',
            'default'  => '',
            'class'    => 'wc-enhanced-input',
            'css'      => 'min-width:300px;'
          ),
          array(
            'title'    => __('Custom display name', 'coleteonline'),
            'desc'     => __('For the first matched service display a custom name. [courierName] and [serviceName] can be used and it will be replaced with the corresponding values',
                            'coleteonline'),
            'id'       => 'coleteonline_service_selection_custom_name',
            'type'     => 'text',
            'default'  => '',
            'class'    => 'wc-enhanced-input',
            'css'      => 'min-width:300px;'
          ),
          array(
            'title'    => __('Show custom name for first shown service', 'coleteonline'),
            'desc'     => __('Enable the display of a custom name for the first matched service',
                            'coleteonline'),
            'id'       => 'coleteonline_service_selection_first_custom_name_toggle',
            'type'     => 'checkbox',
            'default'  => '',
            'class'    => 'wc-enhanced-input',
            'css'      => 'min-width:300px;'
          ),
          array(
            'title'    => __('Custom display name', 'coleteonline'),
            'desc'     => __('For the first matched service display a custom name. [courierName] and [serviceName] can be used and it will be replaced with the corresponding values',
                            'coleteonline'),
            'id'       => 'coleteonline_service_selection_custom_name_first',
            'type'     => 'text',
            'default'  => '',
            'class'    => 'wc-enhanced-input',
            'css'      => 'min-width:300px;'
          ),
          array(
            'id'       => 'coleteonline_service_list_hidden',
            'type'     => 'text',
            'css'      => 'display: none;'
          ),
          array(
            'type' => 'sectionend',
            'id'   => 'account_endpoint_options',
          ),
      );
    } else if ($section_name == "price") {

      $currency_code_options = get_woocommerce_currencies();

      foreach ( $currency_code_options as $code => $name ) {
        $currency_code_options[ $code ] = $name . ' (' . get_woocommerce_currency_symbol( $code ) . ')';
      }
      $settings =
        array(
          array(
            'title' => __('Price settings', 'coleteonline' ),
            'desc'  => __('Configure how the price is calculated',
                          'coleteonline'),
            'type'  => 'title',
            'id'    => 'service_quotation_fallback_price',
          ),
          array(
            'title'    => __('Display price', 'coleteonline'),
            'desc'     => __('Show a fixed price or a price computed from the address',
                            'coleteonline'),
            'id'       => 'coleteonline_price_type',
            'type'     => 'select',
            'default'  => 'calculated_price',
            'class'    => 'wc-enhanced-select',
            'options'   => array(
              "fixed_price" => __("Fixed price", "coleteonline"),
              "calculated_price" => __("Calculated price", "coleteonline")
            )
          ),
          array(
            'title'    => __('Fixed price', 'coleteonline'),
            'desc'     => __('Price without tax', 'coleteonline'),
            'id'       => 'coleteonline_price_fixed_price_amount',
            'type'     => 'text',
            'default'  => '',
            'select'   => array(
              "fixed_price" => __("Fixed price", "coleteonline"),
              "calculated_price" => __("Calculated price", "coleteonline")
            )
          ),
          array(
            'title'    => __( 'Calculate in custom currency', 'coleteonline' ),
            'desc'     => __( 'This controls whether to use the shop base currency for calculation or a custom currency', 'coleteonline' ),
            'id'       => 'coleteonline_price_currency_type',
            'default'  => 'shop_base',
            'type'     => 'select',
            'class'    => 'wc-enhanced-select',
            'options'  => array(
              'shop_base' => __('Use shop base currency', 'coleteonline'),
              'custom' => __('Use custom currency (advanced option, use with care)', 'coleteonline')
            )
          ),
          array(
            'title'    => __( 'Custom base currency', 'coleteonline' ),
            'desc'     => __( 'This controls in what currency prices should be calculated.', 'coleteonline' ),
            'id'       => 'coleteonline_price_base_currency',
            'default'  => 'RON',
            'type'     => 'select',
            'class'    => 'wc-enhanced-select',
            'options'  => $currency_code_options,
          ),
          array(
            'title'    => __('Display a fallback price', 'coleteonline'),
            'desc'     => __('Display a fallback price, if a quote cannot be calculated',
                            'coleteonline'),
            'id'       => 'coleteonline_display_fallback_price',
            'type'     => 'select',
            'class'    => 'wc-enhanced-select',
            'default'  => 'no',
            'options'  => array(
              'no'        => __('Do not display a fallback price', 'coleteonline'),
              'critical'  => __('Display a fallback price if there are issues', 'coleteonline'),
              'yes'       => __('Display a fallback always (even if wrong address)', 'coleteonline'),
            )
          ),
          array(
            'title'    => __('Fallback price', 'coleteonline'),
            'desc'     => __('Price to display if a quotation cannot be calculated (without tax)',
                            'coleteonline'),
            'id'       => 'coleteonline_fallback_price_amount',
            'type'     => 'text',
            'default'  => '',
            'class'    => 'wc-enhanced-input',
            'css'      => 'min-width:300px;'
          ),
          array(
            'title'    => __('Fallback service name', 'coleteonline'),
            'desc'     => __('The display name of the fallback service',
                            'coleteonline'),
            'id'       => 'coleteonline_fallback_service_name',
            'type'     => 'text',
            'default'  => 'ColeteOnline',
            'class'    => 'wc-enhanced-input',
            'css'      => 'min-width:300px;'
          ),
          array(
            'title'    => __('Add fixed amount to price', 'coleteonline'),
            'desc'     => __('Add a fixed price to shipping costs (before tax)',
                            'coleteonline'),
            'id'       => 'coleteonline_price_add_fixed_amount',
            'type'     => 'text',
            'default'  => '0'
          ),
          array(
            'title'    => __('Add percent amount to price', 'coleteonline'),
            'desc'     => __('Add a percent amount to shipping costs (0 - 100%, before tax)',
                            'coleteonline'),
            'id'       => 'coleteonline_price_add_percent_amount',
            'type'     => 'text',
            'default'  => '0'
          ),
          array(
            'title'    => __('Round the price', 'coleteonline'),
            'desc'     => __('Round the price to nearest amount (before tax). For example if price is 13.2 and "round price" is 1, the amount will be 14. If "round" price is 5, the displayed amount will be 15.',
                            'coleteonline'),
            'id'       => 'coleteonline_price_round_before_tax',
            'type'     => 'select',
            'default'  => '0',
            'class'    => 'wc-enhanced-select',
            'options'  => array(
              "0" => "0",
              "0.5" => "0.5",
              "1" => "1",
              "5" => "5",
              "10" => "10"
            )
          ),
          array(
            'title'    => __('Allow free shipping', 'coleteonline'),
            'desc'     => __('Allow free shipping and configure the conditions for it',
                            'coleteonline'),
            'id'       => 'coleteonline_price_free_shipping',
            'type'     => 'select',
            'default'  => '0',
            'class'    => 'wc-enhanced-select',
            'options'  => array(
              "0" => __("No free shipping", "coleteonline"),
              "1" => __("By amount", "coleteonline"),
              "2" => __("By shipping class", "coleteonline"),
              "3" => __("By amount or shipping class", "coleteonline"),
              "4" => __("By Coupon", "coleteonline"),
              "5" => __("By amount, shipping class or coupon", "coleteonline"),
            )
          ),
          array(
            'title'    => __('For order amount bigger than', 'coleteonline'),
            'desc'     => __('Free shipping if order price (without transport) is bigger than amount',
                            'coleteonline'),
            'id'       => 'coleteonline_price_free_shipping_min_amount',
            'type'     => 'text',
            'default'  => '0',
            'class'    => 'wc-enhanced-input',
            'show_if_checked' => 'no'
          ),
          array(
            'title'    => __('For shipping classes', 'coleteonline'),
            'desc'     => __('For products with specific shipping class',
                            'coleteonline'),
            'id'       => 'coleteonline_price_free_shipping_classes',
            'type'     => 'multiselect',
            'class'    => 'wc-enhanced-select',
            'options' => self::get_shipping_classes_options()
          ),
          array(
            'title'    => __('Append text for free shipping', 'coleteonline'),
            'desc'     => __('Text to be added after courier name for free shipping',
                            'coleteonline'),
            'id'       => 'coleteonline_price_free_shipping_after_name_text',
            'type'     => 'text'
          ),
          array(
            'type' => 'sectionend',
            'id'   => 'coleteonline_price_options',
          ),
      );
    } else if ($section_name == "packaging") {
      $settings =
        array(
          array(
            'title' => __('Packaging settings', 'coleteonline' ),
            'desc'  => __('Configure the packaging settings',
                          'coleteonline'),
            'type'  => 'title',
            'id'    => 'service_quotation_fallback_price',
          ),
          array(
            'title'    => __('Packaging method', 'coleteonline'),
            'desc'     => __('How the products will be packaged',
                            'coleteonline'),
            'id'       => 'coleteonline_packaging_method',
            'type'     => 'radio',
            'default'  => 'all_in_package',
            'options'   => array(
              "all_in_package" => __("All in a package", "coleteonline"),
              "each_in_package" => __("Each in a package", "coleteonline")
            )
          ),
          array(
            'title'    => __('Packaging type', 'coleteonline'),
            'desc'     => __('If prefering envelope for small packages. Envelope option is used for sending documents usualy so it may result in the inability to compensate your package if it is lost or destroyed. Use this option only if you understand the possible problems. ',
                            'coleteonline'),
            'id'       => 'coleteonline_packaging_prefer_envelope',
            'type'     => 'select',
            'default'  => 'no',
            'options'   => array(
              "no" => __("Use parcel", "coleteonline"),
              "yes" => __("Prefer envelope", "coleteonline")
            )
          ),
          array(
            'title'    => __('Packages content', 'coleteonline'),
            'desc'     => __('How the content of the packages will be sent to the couriers',
                            'coleteonline'),
            'id'       => 'coleteonline_packaging_content_autocomplete',
            'type'     => 'select',
            'default'  => 'product_names',
            'options'   => array(
              "product_names" => __("Automatically from the product names", "coleteonline"),
              "fixed_on_limit" => __("Use a fixed content string when automatically generated string is too big", "coleteonline"),
              "fixed" => __("Use a fixed content string always", "coleteonline")
            )
          ),
          array(
            'title'    => __('Packages content fixed string', 'coleteonline'),
            'desc'     => __('How the content of the packages will be sent to the couriers',
                            'coleteonline'),
            'id'       => 'coleteonline_packaging_content_autocomplete_fixed_string',
            'type'     => 'text'
          ),
          array(
            'type' => 'sectionend',
            'id'   => 'coleteonline_packaging_options',
          ),
        );
    } else if ($section_name == "order") {
      $settings =
        array(
          array(
            'title' => __('Order settings', 'coleteonline' ),
            'desc'  => __('Order specific settings',
                          'coleteonline'),
            'type'  => 'title',
            'id'    => 'service_quotation_fallback_price',
          ),
          array(
            'title'    => __('Trigger courier order on customer order', 'coleteonline'),
            'desc'     => __('Automatically create the AWB for a order when the customer completes the checkout',
                            'coleteonline'),
            'id'       => 'coleteonline_order_auto_create_order',
            'type'     => 'select',
            'class'    => 'wc-enhanced-select',
            'default'  => 'no',
            'options'   => array(
              'no' => __('No', 'coleteonline'),
              'yes' => __('Yes', 'coleteonline')
            )
          ),
          array(
            'title'    => __('Trigger courier order on change from On Hold to Processing', 'coleteonline'),
            'desc'     => __('If an order has a payment method that needs manual processing, when the state is changed, automatically create the courier order',
                            'coleteonline'),
            'id'       => 'coleteonline_order_auto_create_on_status_on-hold_to_processing',
            'type'     => 'select',
            'class'    => 'wc-enhanced-select',
            'default'  => 'no',
            'options'   => array(
              'no' => __('No', 'coleteonline'),
              'yes' => __('Yes', 'coleteonline')
            )
          ),
          array(
            'title'    => __('Create a custom Shipping status for orders', 'coleteonline'),
            'desc'     => __('Add a new Shipping status that orders can use.',
                            'coleteonline'),
            'id'       => 'coleteonline_order_add_custom_shipping_status',
            'type'     => 'select',
            'class'    => 'wc-enhanced-select',
            'default'  => 'yes',
            'options'   => array(
              'no' => __('No', 'coleteonline'),
              'yes' => __('Yes', 'coleteonline')
            )
          ),
          array(
            'title'    => __('Change order status when AWB is created', 'coleteonline'),
            'desc'     => __('When the courier order is created, change the status to Shipping from Processing.',
                            'coleteonline'),
            'id'       => 'coleteonline_order_change_to_shipping_status',
            'type'     => 'select',
            'class'    => 'wc-enhanced-select',
            'default'  => 'yes',
            'options'   => array(
              'no' => __('No', 'coleteonline'),
              'yes' => __('Yes', 'coleteonline')
            )
          ),
          array(
            'title'    => __('Default pick up location', 'coleteonline'),
            'desc'     => __('Set the default pick up location',
                            'coleteonline'),
            'id'       => 'coleteonline_default_shipping_address',
            'type'     => 'select',
            'class'    => 'wc-enhanced-select',
            'options'   => array()
          ),
          array(
            'title'    => "",
            'desc'     => "",
            'id'       => 'coleteonline_default_shipping_address_id',
            'type'     => 'text'
          ),
          array(
            'title'    => "",
            'desc'     => "",
            'id'       => 'coleteonline_default_shipping_address_full_data',
            'type'     => 'text'
          ),
          // array(
          //   'title'    => __('Allow per product pick up locaiton', 'coleteonline'),
          //   'desc'     => __('Set different product locations for each product (if not set the default will be used)',
          //                   'coleteonline'),
          //   'id'       => 'coleteonline_price_amount_add_id',
          //   'type'     => 'checkbox',
          //   'default'  => 'no'
          // ),
          // array(
          //   'title'    => __('Calculate shipping as', 'coleteonline'),
          //   'desc'     => __('Set how to calculate the shipment price if the cart has products from multiple locaitons',
          //                   'coleteonline'),
          //   'id'       => 'coleteonline_price_amount_add_id',
          //   'type'     => 'select',
          //   'class'    => 'wc-enhanced-slect',
          //   'options'  => array(
          //     "all" => "Total of all shipments",
          //     "cheapest" => "Display the cheapest option",
          //     "most_expensive" => "Display the most expensive option"
          //   )
          // ),
          array(
            'title'    => __('Open at delivery', 'coleteonline'),
            'desc'     => __('Set open at delivery settings',
                            'coleteonline'),
            'id'       => 'coleteonline_order_open_at_delivery',
            'type'     => 'select',
            'default'  => 'calculated_price',
            'options'   => array(
              "no" => __("No", "coleteonline"),
              "always" => __("Always", "coleteonline"),
              "user_choice" => __("Let the user decide", "coleteonline")
            )
          ),
          array(
            'title'    => __('Open at delivery user text', 'coleteonline'),
            'desc'     => __('Set open at delivery user text',
                            'coleteonline'),
            'id'       => 'coleteonline_order_open_at_delivery_text',
            'type'     => 'text',
            'default'  => 'Deschidere la livrare'
          ),
          array(
            'title'    => __('Insurance', 'coleteonline'),
            'desc'     => __('Add insurance for orders',
                            'coleteonline'),
            'id'       => 'coleteonline_order_insurance',
            'type'     => 'select',
            'default'  => 'calculated_price',
            'options'   => array(
              "no" => __("No", "coleteonline"),
              "always" => __("Yes", "coleteonline"),
              "when_no_repayment" => __("Only when no repayment", "coleteonline"),
            )
          ),
          array(
            'title'    => __('Send email to recipient', 'coleteonline'),
            'desc'     => __('When the order is created send an email to the recipient with tracking information and order datails',
                            'coleteonline'),
            'id'       => 'coleteonline_order_send_email_to_recipient',
            'type'     => 'select',
            'class'    => 'wc-enhanced-select',
            'default'  => 'yes',
            'options'   => array(
              'no' => __('No', 'coleteonline'),
              'yes' => __('Yes', 'coleteonline')
            )
          ),
          array(
            'title'    => __('Client order reference', 'coleteonline'),
            'desc'     => __('Add a client reference to the order. ([orderId] will be replaced with the order id)',
                            'coleteonline'),
            'id'       => 'coleteonline_order_client_reference',
            'type'     => 'text',
            'default'  => '#[orderId]'
          ),
          array(
            'title'    => __('Add custom order note', 'coleteonline'),
            'desc'     => __('Add a custom note on order creation. ([content] or [skuContent] can be used)',
                            'coleteonline'),
            'id'       => 'coleteonline_order_custom_note',
            'type'     => 'text',
            'default'  => ''
          ),
          array(
            'title'    => __('Add order tracking link to mail', 'coleteonline'),
            'desc'     => __('Include the order tracking link in the email sent to the client',
                            'coleteonline'),
            'id'       => 'colete_online_add_mail_tracking_link',
            'type'     => 'select',
            'class'    => 'wc-enhanced-select',
            'default'  => 'no',
            'options'   => array(
              'yes' => __('Yes', 'coleteonline'),
              'no' => __('No', 'coleteonline')
            )
          ),
          array(
            'type' => 'sectionend',
            'id'   => 'coleteonline_order_options',
          ),
        );
    } else if ($section_name == "advanced") {
      $settings =
      array(
        array(
          'title' => __('Advanced options', 'coleteonline' ),
          'desc'  => __('Use this options only if you know that they apply to you. Ideally you should ask the colete online staff before using these options. Using the wrong configuration might result in invalid shipping orders.', 'coleteonline'),
          'type'  => 'title',
          'id'    => 'coleteonline_advanced_options_title',
        ),
        array(
          "title"   => __("Order checkout form", "coleteonline"),
          "desc"    => __("Use this select to choose the desired order checkout form for your website.", "coleteonline"),
          "id"      => "coleteonline_checkout_form_type",
          "type"    => "select",
          "class"   => "wc-enhanced-select",
          "default" => "coleteonline",
          "options" => array(
            "coleteonline"   => __("Use Colete Online form", "coleteonline"),
            "woocommerceDefault" => __("Use default woocommerce form (no Colete-Online autocomplete for checkout)", "coleteonline"),
            "custom"    => __("Use a customized form", "coleteonline")
          )
        ),
        array(
          'title'    => __('Show postal code', 'coleteonline'),
          'parent'   => "coleteonline_checkout_form_type",
          'desc'     => __('Where to show the postal code on the cart and checkout page',
                          'coleteonline'),
          'id'       => 'coleteonline_address_postal_code_show',
          'type'     => 'select',
          'class'    => 'wc-enhanced-select',
          'default'  => 'before',
          'options'   => array(
            "before" =>  __("Before address", "coleteonline"),
            "after"  => __("After address", "coleteonline"),
            "no"     => __("Don't show", "coleteonline")
          )
        ),
        array(
          'title'    => __('Validate postal code, city and county combination before submit', 'coleteonline'),
          'parent'   => "coleteonline_checkout_form_type",
          'desc'     => __('Existing users that have the address completed might be able to place orders with an incorrect address. Using this option, ensures they enter a valid address',
                          'coleteonline'),
          'id'       => 'coleteonline_address_validate_address_checkout',
          'type'     => 'select',
          'class'    => 'wc-enhanced-select',
          'default'  => 'yes',
          'options'   => array(
            "no" =>  __("Don't validate", "coleteonline"),
            "yes"  => __("Validate", "coleteonline"),
          )
        ),
        array(
          'title'    => __('Use separate fields for county and city', 'coleteonline'),
          'desc'     => __('ColeteOnline uses the same field for city and county, but if you want to use separate dropdowns enable this option',
                          'coleteonline'),
          'id'       => 'coleteonline_address_separate_fields',
          'parent'   => "coleteonline_checkout_form_type",
          'type'     => 'select',
          'class'    => 'wc-enhanced-select',
          'default'  => 'no',
          'options'   => array(
            "no" =>  __("No", "coleteonline"),
            "yes" =>  __("Yes", "coleteonline")
          )
        ),
        array(
          "title"   => __("Display city field", "coleteonline"),
          'parent'   => "coleteonline_checkout_form_type",
          "desc"    => __("This setting allows you to specify how the city field should be displayed within the order form.", "coleteonline"),
          "id"      => "coleteonline_address_city_field_type",
          "type"    => "select",
          "class"   => "wc-enhanced-select",
          "default" => "coleteonline",
          "options" => array(
            "coleteonline"      => __("Display with autocomplete", "coleteonline"),
            "woocommerce"       => __("Display Woocommerce default field", "wordpress"),
          )
        ),
        array(
          'title'    => __('Auto select city on search close', 'coleteonline'),
          'parent'   => "coleteonline_checkout_form_type",
          'desc'     => __('If the city select is closed, automatically select the highlighted option, otherwise the user needs to explicitly select it',
                          'coleteonline'),
          'id'       => 'coleteonline_address_auto_select_city',
          'type'     => 'select',
          'class'    => 'wc-enhanced-select',
          'default'  => 'yes',
          'options'   => array(
            "no" =>  __("No", "coleteonline"),
            "yes" =>  __("Yes", "coleteonline")
          )
        ),
        array(
          "title"   => __("Street field type", "coleteonline"),
          'parent'   => "coleteonline_checkout_form_type",
          "desc"    => __("This setting allows you to specify the functionality of the street field.", "coleteonline"),
          "id"      => "coleteonline_address_street_field_type",
          "type"    => "select",
          "class"   => "wc-enhanced-select",
          "default" => "displayWithAutocomplete",
          "options" => array(
            "displayWithAutocomplete"     => __("Display with autocomplete", "coleteonline"),
            "displayWithoutAutocomplete"  => __("Display without autocomplete", "coleteonline"),
            "displayWoocommerce"          => __("Display Woocommerce field", "coleteonline")
          )
        ),
        array(
          'title'    => __('Auto select street on search close', 'coleteonline'),
          'parent'   => "coleteonline_checkout_form_type",
          'desc'     => __('If the street select is closed, automatically select the highlighted option, otherwise the user needs to explicitly select it',
                          'coleteonline'),
          'id'       => 'coleteonline_address_auto_select_street',
          'type'     => 'select',
          'class'    => 'wc-enhanced-select',
          'default'  => 'yes',
          'options'   => array(
            "no" =>  __("No", "coleteonline"),
            "yes" =>  __("Yes", "coleteonline")
          )
        ),
        array(
          "title"   => __("Show street number", "coleteonline"),
          'parent'   => "coleteonline_checkout_form_type",
          "desc"    => __("This setting allows you to specify how the street number should be displayed within the order form.", "coleteonline"),
          "id"      => "coleteonline_address_street_number_field_type",
          "type"    => "select",
          "class"   => "wc-enhanced-select",
          "default" => "displayMandatory",
          "options" => array(
            "displayMandatory"    => __("Yes and mandatory", "coleteonline"),
            "displayOptional"     => __("Yes but not mandatory", "coleteonline"),
            "no"                  => __("Do not display", "coleteonline")
          )
        ),
        array(
          'title'    => __('Enable advanced product fields', 'coleteonline'),
          'desc'     => __('Some products need special configuration for displaying the right shipping price. This option enables extra configuration fields on the product admin page.',
                          'coleteonline'),
          'id'       => 'coleteonline_advanced_product_fields',
          'type'     => 'select',
          'class'    => 'wc-enhanced-select',
          'default'  => 'no',
          'options'   => array(
            "no" =>  __("No", "coleteonline"),
            "yes" =>  __("Yes", "coleteonline")
          )
        ),
        array(
          'title'    => __('Custom display name for aggregated services', 'coleteonline'),
          'desc'     => __('When multiple courier orders need to be created choose the displayed courier name to the user',
                          'coleteonline'),
          'id'       => 'coleteonline_advanced_aggregated_services_custom_name',
          'type'     => 'text',
          'default'  => 'ColeteOnline',
          'class'    => 'wc-enhanced-input',
          'css'      => 'min-width:300px;'
        ),
        array(
          'title'    => __('Show email as first field', 'coleteonline'),
          'parent'   => "coleteonline_checkout_form_type",
          'desc'     => __('If the email field on the checkout page should be displayed as first field',
                          'coleteonline'),
          'id'       => 'coleteonline_address_email_show_first',
          'type'     => 'select',
          'class'    => 'wc-enhanced-select',
          'default'  => 'no',
          'options'  => array(
            "no"       => __("No", "coleteonline"),
            "yes"      =>  __("Yes", "coleteonline")
          )
        ),
        array(
          'title'    => __('Validate email', 'coleteonline'),
          'parent'   => "coleteonline_checkout_form_type",
          'desc'     => __('Validate the email during the checkout process.',
                          'coleteonline'),
          'id'       => 'coleteonline_address_validate_email',
          'type'     => 'select',
          'class'    => 'wc-enhanced-select',
          'default'  => 'no',
          'options'  => array(
            "yes" => __("Yes", "coleteonline"),
            "no"  => __("No", "coleteonline")
          )
        ),
        array(
          'title'    => __('Show phone for shipping address', 'coleteonline'),
          'parent'   => "coleteonline_checkout_form_type",
          'desc'     => __('By default the phone is shown only for the billing address. Enable this to show for both',
                          'coleteonline'),
          'id'       => 'coleteonline_address_shipping_show_phone',
          'type'     => 'select',
          'class'    => 'wc-enhanced-select',
          'default'  => 'no',
          'options'  => array(
            "no"       => __("No", "coleteonline"),
            "yes"      =>  __("Yes", "coleteonline")
          )
        ),
        array(
          'title'    => __('Validate phone', 'coleteonline'),
          'parent'   => "coleteonline_checkout_form_type",
          'desc'     => __('Validate the phone during the checkout process.',
                          'coleteonline'),
          'id'       => 'coleteonline_address_validate_phone',
          'type'     => 'select',
          'class'    => 'wc-enhanced-select',
          'options'  => array(
            "yes" => __("Yes", "coleteonline"),
            "no"  => __("No", "coleteonline")
          )
        ),
        array(
          'title'    => __('Use optimized search', 'coleteonline'),
          'parent'   => "coleteonline_checkout_form_type",
          'desc'     => __('This option optimizes the speed for checkout auto complete fields.',
                          'coleteonline'),
          'id'       => 'coleteonline_address_optimized_search',
          'type'     => 'select',
          'class'    => 'wc-enhanced-select',
          'default'  => 'yes',
          'options'  => array(
            "yes" => __("Yes", "coleteonline"),
            "no"  => __("No", "coleteonline")
          )
        ),
        array(
          'type' => 'sectionend',
          'id'   => 'coleteonline_advanced_options',
        ),
      );
    } else if ($section_name === "delivery_to_fixed_points") {
      $gateways = WC()->payment_gateways->get_available_payment_gateways();
      $enabled_gateways = [];

      if($gateways) {
        foreach($gateways as $gateway) {
          if($gateway->enabled == 'yes') {
            $enabled_gateways[$gateway->id] = $gateway->title;
          }
        }
      }
      $settings =
        array(
          array(
            'title' => __('Delivery to fixed points options', 'coleteonline' ),
            'desc'  => __('Configure delivery to fixed points', 'coleteonline'),
            'type'  => 'title',
            'id'    => 'coleteonline_delivery_to_fixed_points_options_title',
          ),
          array(
            'title'    => __('Hide delivery to fixed points for payment gateways', 'coleteonline'),
            'desc'     => __("Choose the payment gateways for which you don't want to allow your clients to place orders using a shipping point.", 'coleteonline'),
            'id'       => 'coleteonline_delivery_to_fixed_points_restricted_payment_gateways',
            'type'     => 'multiselect',
            'class'    => 'wc-enhanced-select',
            'css'      => 'min-width:300px;',
            'options'  => $enabled_gateways,
            'default'  => array(),
          ),
          array(
            'title'    => __('Add custom text for restricted payment gateways', 'coleteonline'),
            'desc'     => __('Enable the display of a custom text to the payment methods for which delivery to fixed points is disabled',
                            'coleteonline'),
            'id'       => 'coleteonline_delivery_to_fixed_points_restricted_message_toggle',
            'type'     => 'checkbox',
            'default'  => '',
            'class'    => 'wc-enhanced-input',
            'css'      => 'min-width:300px;'
          ),
          array(
            'title'    => __('Text for restricted payment gateways', 'coleteonline'),
            'desc'     => __('The message which will be displayed for the restricted payment gateways', 'coleteonline'),
            'id'       => 'coleteonline_delivery_to_fixed_points_restricted_message_text',
            'type'     => 'text',
            'default'  => '',
            'class'    => 'wc-enhanced-input',
            'css'      => 'min-width:300px;'
          ),
          array(
            'title'    => __('Add custom text when order contains products that cannot be delivered to fixed points', 'coleteonline'),
            'desc'     => __('Display a custom message when the order contains products that are not eligible for delivery to fixed points',
                            'coleteonline'),
            'id'       => 'coleteonline_delivery_to_fixed_points_restricted_products_message_toggle',
            'type'     => 'checkbox',
            'default'  => '',
            'class'    => 'wc-enhanced-input',
            'css'      => 'min-width:300px;'
          ),
          array(
            'title'    => __('Text for not eligible for locker products', 'coleteonline'),
            'desc'     => __('The message which will be displayed when the order contains products that cannot be delivered to fixed points', 'coleteonline'),
            'id'       => 'coleteonline_delivery_to_fixed_points_restricted_products_message_text',
            'type'     => 'text',
            'default'  => '',
            'class'    => 'wc-enhanced-input',
            'css'      => 'min-width:300px;'
          ),
          array(
            'title'    => __('Prioritize delivery to fixed points services', 'coleteonline'),
            'desc'     => __('Specify if delivery to fixed points services should be prioritized in the checkout page.',
                            'coleteonline'),
            'id'       => 'coleteonline_display_delivery_to_fixed_points_first',
            'type'     => 'select',
            'default'  => 'no',
            'class'    => 'wc-enhanced-select',
            'css'      => 'min-width:300px;',
            'options' => array(
              'no' => __('No', 'coleteonline'),
              'yes' => __('Yes', 'coleteonline')
            )
          ),
          array(
            'type' => 'sectionend',
            'id'   => 'coleteonline_delivery_to_fixed_points_options',
          )
        );
    }

    return $settings;
  }

  private static function get_shipping_classes_options() {
    $shipping_classes = get_terms(
      array(
        'taxonomy' => 'product_shipping_class',
        'hide_empty' => false
      )
    );

    $data = array();
    foreach ($shipping_classes as $class) {
      $data[$class->term_id] = $class->name . " - " . $class->slug;
    }

    return $data;
  }

}