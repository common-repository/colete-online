<?php

defined( 'ABSPATH' ) || exit;


class Colete_Online_Public {

  private $plugin_name;
  private $version;

  private $address_actions;
  private $address_filters;
  private $checkout_actions;

  public function __construct( $plugin_name, $version ) {
    $this->plugin_name = $plugin_name;
    $this->version = $version;

    require_once COLETE_ONLINE_ROOT . "/lib/class-colete-online-client.php";

    require_once COLETE_ONLINE_ROOT . '/public/class-colete-online-address-actions.php';

    $this->address_actions = new ColeteOnlineAddressActions();

    require_once COLETE_ONLINE_ROOT . '/public/class-colete-online-address-filters.php';

    $this->address_filters = new ColeteOnlineAddressFilters();

    require_once COLETE_ONLINE_ROOT . '/public/class-colete-online-checkout-actions.php';

    $this->checkout_actions = new ColeteOnlineCheckoutActions();
  }

  public function get_address_actions() {
    return $this->address_actions;
  }

  public function get_address_filters() {
    return $this->address_filters;
  }

  public function get_checkout_actions() {
    return $this->checkout_actions;
  }

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_styles() {
    $suffix = '';
    if (COLETE_ONLINE_STYLE_DEBUG === false) {
      $suffix = '.min';
    }
    wp_enqueue_style( $this->plugin_name . '-public-style',
                      plugin_dir_url( __FILE__ ) . "css/colete-online-public$suffix.css",
                      array(),
                      $this->version,
                      'all'
                    );

  }

  /**
   * Register the JavaScript for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts() {
    $suffix = '';
    if (COLETE_ONLINE_SCRIPT_DEBUG === false) {
      $suffix = '.min';
    }

    if (is_cart()) {
      wp_enqueue_script( $this->plugin_name . '-public-cart',
                       plugin_dir_url( __FILE__ ) . "js/colete-online-cart$suffix.js",
                       array( 'jquery', 'jquery-ui-core', 'select2' ),
                       $this->version,
                       true
                      );
      if (get_option("coleteonline_address_optimized_search") === "yes") {
        $path = explode("/", dirname(__FILE__));
        end($path);
        $directory = prev($path);
        wp_localize_script(
          $this->plugin_name . '-public-cart',
          'customAjaxPath',
          array(
            'pluginsUrl' => plugins_url( "$directory/includes/class-colete-online-custom-ajax.php")
          )
        );
      }
      wp_localize_script(
        $this->plugin_name . '-public-cart',
        'shippingCityTranslations',
        array(
          'emptyState' =>  __("Select the county before", "coleteonline"),
          'nonEmptyState'=> __("Select the city / locality", "coleteonline")
        )
      );
      wp_localize_script(
        $this->plugin_name . '-public-cart',
        'coleteOnlineOptions',
        array(
          'postalCodePosition' => get_option('coleteonline_address_postal_code_show', 'after'),
          'autoSelectCity' => get_option('coleteonline_address_auto_select_city', 'yes')
        )
      );
      wp_localize_script(
        $this->plugin_name . '-public-cart',
        'coleteOnlineLanguage',
        array(
          'localitySearch' => array(
            'errorLoading' => _x('No localities could be loaded', 'cart', 'coleteonline'),
            'inputTooLong' => _x('Please insert %d or less characters to start searching for localities', 'cart', 'coleteonline'),
            'inputTooShort' => _x('Please insert %d or more characters to start searching for localities', 'cart', 'coleteonline'),
            'loadingMore' => _x('More localities are loading...', 'cart', 'coleteonline'),
            'noResults' => _x('No localities found for the given input', 'cart', 'coleteonline'),
            'searching' => _x('Searching localities...', 'cart', 'coleteonline'),
            'removeAllItems' => _x('Remove all localities', 'cart', 'coleteonline'),
            'removeItem' => _x('Remove locality', 'cart', 'coleteonline'),
            'search' => _x('Search for localities', 'cart', 'coleteonline')
          )
        )
      );
    }

    if (is_checkout() || is_wc_endpoint_url('edit-address')) {
      wp_enqueue_script( $this->plugin_name . '-public-addresses',
                       plugin_dir_url( __FILE__ ) . "js/colete-online-addresses$suffix.js",
                       array( 'jquery', 'jquery-ui-core', 'select2' ),
                       $this->version,
                       true
                      );
      if (get_option("coleteonline_address_optimized_search") === "yes") {
        $path = explode("/", dirname(__FILE__));
        end($path);
        $directory = prev($path);
        wp_localize_script(
          $this->plugin_name . '-public-addresses',
          'customAjaxPath',
          array(
            'pluginsUrl' => plugins_url( "$directory/includes/class-colete-online-custom-ajax.php")
          )
        );
      }
      wp_localize_script(
        $this->plugin_name . '-public-addresses',
        'shippingCityTranslations',
        array(
          'emptyState' =>  __("Select the county before", "coleteonline"),
          'nonEmptyState'=> __("Select the city / locality", "coleteonline")
        )
      );

      wp_localize_script(
        $this->plugin_name . '-public-addresses',
        'coleteOnlineLanguage',
        array(
          'streetSearch' => array(
            'errorLoading' => _x('No streets could be loaded', 'addresses', 'coleteonline'),
            'inputTooLong' => _x('Please insert %d or less characters to start searching for streets', 'addresses', 'coleteonline'),
            'inputTooShort' => _x('Please insert %d or more characters to start searching for streets', 'addresses', 'coleteonline'),
            'loadingMore' => _x('More streets are loading...', 'addresses', 'coleteonline'),
            'noResults' => _x('No streets found for the given input',  'addresses', 'coleteonline'),
            'searching' => _x('Searching streets...',  'addresses', 'coleteonline'),
            'removeAllItems' => _x('Remove all streets',  'addresses', 'coleteonline'),
            'removeItem' => _x('Remove street',  'addresses', 'coleteonline'),
            'search' => _x('Search streets',  'addresses', 'coleteonline')
          ),
          'localitySearch' => array(
            'errorLoading' => _x('No localities could be loaded', 'addresses', 'coleteonline'),
            'inputTooLong' => _x('Please insert %d or less characters to start searching for localities', 'addresses', 'coleteonline'),
            'inputTooShort' => _x('Please insert %d or more characters to start searching for localities', 'addresses', 'coleteonline'),
            'loadingMore' => _x('More localities are loading...', 'addresses', 'coleteonline'),
            'noResults' => _x('No localities found for the given input',  'addresses', 'coleteonline'),
            'searching' => _x('Searching for localities...', 'addresses', 'coleteonline'),
            'removeAllItems' => _x('Remove all localities', 'addresses', 'coleteonline'),
            'removeItem' => _x('Remove locality', 'addresses', 'coleteonline'),
            'search' => _x('Search for localities', 'addresses', 'coleteonline')
          ),
          'localityAndStateSearch' => array(
            'errorLoading' => _x('No locations could be loaded', 'addresses', 'coleteonline'),
            'inputTooLong' => _x('Please insert %d or less characters to start searching for locations', 'addresses', 'coleteonline'),
            'inputTooShort' => _x('Please insert %d or more characters to start searching for locations', 'addresses', 'coleteonline'),
            'loadingMore' => _x('More locations are loading...', 'addresses', 'coleteonline'),
            'noResults' => _x('No locations found for the given input',  'addresses', 'coleteonline'),
            'searching' => _x('Searching locations...',  'addresses', 'coleteonline'),
            'removeAllItems' => _x('Remove all locations', 'addresses', 'coleteonline'),
            'removeItem' => _x('Remove location', 'addresses', 'coleteonline'),
            'search' => _x('Search for locations', 'addresses', 'coleteonline')
          )
        )
      );

      wp_localize_script(
        $this->plugin_name . '-public-addresses',
        'coleteOnlineOptions',
        array(
          'checkoutFormType' => get_option('coleteonline_checkout_form_type', 'coleteonline'),
          'postalCodePosition' => get_option('coleteonline_address_postal_code_show', 'after'),
          'separateFields' => get_option('coleteonline_address_separate_fields', 'no') === 'yes',
          'validatePhone' => get_option('coleteonline_address_validate_phone', 'no') === 'yes',
          'validateEmail' => get_option('coleteonline_address_validate_email', 'no') === 'yes',
          'autoSelectCity' => get_option('coleteonline_address_auto_select_city', 'yes'),
          'autoSelectStreet' => get_option('coleteonline_address_auto_select_street', 'yes'),
          'addressStreetNumberField' => get_option('coleteonline_address_street_number_field_type', 'displayMandatory'),
          'addressStreetField' => get_option('coleteonline_address_street_field_type', 'displayWithAutocomplete'),
          'addressCityField' => get_option('coleteonline_address_city_field_type', 'coleteonline'),
          'isCheckout' => is_checkout(),
          'isEditAddressPage' => is_wc_endpoint_url('edit-address')
        )
      );
    }

  }

}
