<?php

defined( 'ABSPATH' ) || exit;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       www.colete-online.ro
 * @since      1.0.0
 *
 * @package    Colete_Online
 * @subpackage Colete_Online/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Colete_Online
 * @subpackage Colete_Online/includes
 * @author     Colete Online <alex@colete-online.ro>
 */

require_once COLETE_ONLINE_ROOT . '/lib/class-colete-online-order-data.php';
require_once COLETE_ONLINE_ROOT . '/includes/class-colete-online-wc-order-data-helper.php';

require_once COLETE_ONLINE_ROOT . '/lib/exceptions/colete-online-bad-request-exception.php';
require_once COLETE_ONLINE_ROOT . '/lib/exceptions/colete-online-server-error-exception.php';

// use Automattic\WooCommerce\Utilities\OrderUtil;

class Colete_Online {

  protected $loader;
  protected $plugin_name;

  protected $version;

  public function __construct() {
    if ( defined( 'COLETE_ONLINE_VERSION' ) ) {
      $this->version = COLETE_ONLINE_VERSION;
    } else {
      $this->version = '1.0.0';
    }
    $this->plugin_name = 'colete-online';

    $this->load_dependencies();
    $this->init();
    $this->set_locale();
    $this->define_admin_hooks();
    $this->define_public_hooks();
    $this->define_wc_shipping_hooks();
  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   * - Colete_Online_Loader. Orchestrates the hooks of the plugin.
   * - Colete_Online_i18n. Defines internationalization functionality.
   * - Colete_Online_Admin. Defines all hooks for the admin area.
   * - Colete_Online_Public. Defines all hooks for the public side of the site.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function load_dependencies() {

    /**
     * The class responsible for orchestrating the actions and filters of the
     * core plugin.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) .
                 'includes/class-colete-online-loader.php';

    /**
     * The class responsible for defining internationalization functionality
     * of the plugin.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) .
                 'includes/class-colete-online-i18n.php';

    /**
     * The class responsible for defining all actions that occur in the admin area.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) .
                 'admin/class-colete-online-admin.php';

    /**
     * The class responsible for defining all actions that occur in the public-facing
     * side of the site.
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) .
                 'public/class-colete-online-public.php';


    /**
     * The data store;
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) .
                 'lib/store/class-colete-online-data-store.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) .
                 'includes/class-wordpress-data-store.php';

    /**
     * The http helper
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) .
                 'lib/http/class-colete-online-http-request.php';
    require_once plugin_dir_path( dirname( __FILE__ ) ) .
                 'includes/class-wordpress-http-request.php';


    /**
     * The app url
     */
    require_once plugin_dir_path( dirname( __FILE__ ) ) .
                 'lib/api/class-colete-online-url.php';

    $this->loader = new Colete_Online_Loader();

  }

  private function init() {
    \ColeteOnline\DataStore::set(new \ColeteOnline\WordpressDataStore());
    \ColeteOnline\HttpRequest::set(new \ColeteOnline\WordpressHttpRequest());

    \ColeteOnline\Url::setTesting(
      get_option('coleteonline_courier_testing', 'no') === 'yes'
    );
  }

  /**
   * Define the locale for this plugin for internationalization.
   *
   * Uses the Colete_Online_i18n class in order to set the domain and to register the hook
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function set_locale() {

    $plugin_i18n = new Colete_Online_i18n();

    $this->loader->add_action('plugins_loaded',
                              $plugin_i18n,
                              'load_plugin_textdomain');

  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_admin_hooks() {

    $plugin_admin = new Colete_Online_Admin($this->get_plugin_name(),
                                            $this->get_version());

    $this->loader->add_action('admin_enqueue_scripts',
                              $plugin_admin,
                              'enqueue_styles');
    $this->loader->add_action('admin_notices',
                            $plugin_admin->get_admin_notice(),
                            'admin_notice');
    if (!in_array(
      'woocommerce/woocommerce.php',
      apply_filters('active_plugins', get_option('active_plugins')),
      ''
    )) {
      return;
    }

    $this->loader->add_action('admin_enqueue_scripts',
                              $plugin_admin,
                              'enqueue_scripts');

    $admin_ajax = $plugin_admin->get_admin_ajax();
    $this->loader->add_action('wp_ajax_coleteonline_get_all_addresses',
                              $admin_ajax,
                              'get_all_addresses');

    $this->loader->add_action('add_meta_boxes',
                              $plugin_admin,
                              'add_meta_boxes');

    $admin_order = $plugin_admin->get_admin_order();

    $this->loader->add_filter('woocommerce_admin_shipping_fields',
                              $admin_order,
                              'change_admin_shipping_address_fields');

    $this->loader->add_action('wp_ajax_coleteonline_get_services_list',
                              $admin_order,
                              'get_services_list');

    $this->loader->add_action('wp_ajax_coleteonline_create_courier_order',
                              $admin_order,
                              'create_order');

    $this->loader->add_action('wp_ajax_coleteonline_add_extra_courier_order',
                              $admin_order,
                              'add_extra_courier_order');

    $this->loader->add_action('wp_ajax_coleteonline_get_order_label',
                              $admin_order,
                              'get_order_label');

    $this->loader->add_action('woocommerce_register_shop_order_post_statuses',
                              $admin_order,
                              'register_custom_statuses');

    $this->loader->add_filter('wc_order_statuses',
                              $admin_order,
                              'add_custom_order_statuses');

    $this->loader->add_filter('woocommerce_order_payment_status_changed',
                              $admin_order,
                              'auto_generate_order',
                              15,
                              2);

    $this->loader->add_filter('woocommerce_order_status_on-hold_to_processing',
                              $admin_order,
                              'auto_generate_order_status_on_hold_to_processing',
                              15,
                              2);

    $this->loader->add_filter('woocommerce_admin_order_actions',
                              $admin_order,
                              'add_order_list_actions',
                              15,
                              2);

    $this->loader->add_filter('woocommerce_hidden_order_itemmeta',
                              $admin_order,
                              'hide_shipping_meta',
                              15,
                              1);

    $bulk_action_handler = "-edit-shop_order";
    if (!has_filter("bulk_actions-{$bulk_action_handler}")) {
      $bulk_action_handler = "woocommerce_page_wc-orders";
    }

    $this->loader->add_filter("bulk_actions-{$bulk_action_handler}",
                              $admin_order,
                              'bulk_actions_list_edit');

    $this->loader->add_filter("handle_bulk_actions-{$bulk_action_handler}",
                              $admin_order,
                              'handle_bulk_actions_list_edit',
                              10,
                              3);

    $this->loader->add_action('admin_notices',
                              $admin_order,
                              'notice_after_bulk_action');

    $this->loader->add_action('woocommerce_email_before_order_table',
                              $admin_order,
                              'add_fixed_point_to_order_email',
                              10,
                              4);

    $admin_product = $plugin_admin->get_admin_product();
    $this->loader->add_action('woocommerce_product_options_dimensions',
                              $admin_product,
                              'custom_product_options_dimensions');

    $this->loader->add_action('woocommerce_product_bulk_and_quick_edit',
                              $admin_product,
                              'save_product',
                              10,
                              2);

  }

  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_public_hooks() {
    if (!in_array(
      'woocommerce/woocommerce.php',
      apply_filters('active_plugins', get_option('active_plugins')),
      ''
    )) {
      return;
    }

    if (get_option("coleteonline_logged_in_once", 'no') === 'no') {
      return;
    }

    $plugin_public = new Colete_Online_Public($this->get_plugin_name(),
                                              $this->get_version());

    $this->loader->add_action('wp_enqueue_scripts',
                              $plugin_public,
                              'enqueue_styles');
    $this->loader->add_action('wp_enqueue_scripts',
                              $plugin_public,
                              'enqueue_scripts');

    $ajaxHandle = "wp_ajax_";
    if(get_option("coleteonline_address_optimized_search") === "yes") {
      $ajaxHandle = "colete_online_custom_ajax_handler_";
    }

    $address_actions = $plugin_public->get_address_actions();

    $this->loader->add_action("{$ajaxHandle}coleteonline_phone_number_check",
                              $address_actions,
                              'validate_phone_ajax');

    $this->loader->add_action("{$ajaxHandle}nopriv_coleteonline_phone_number_check",
                              $address_actions,
                              'validate_phone_ajax');

    $this->loader->add_action('woocommerce_after_checkout_validation',
                              $address_actions,
                              'validate_checkout');

    $this->loader->add_action("{$ajaxHandle}coleteonline_reverse_postal_code_search",
                              $address_actions,
                              'reverse_postal_code');

    $this->loader->add_action("{$ajaxHandle}nopriv_coleteonline_reverse_postal_code_search",
                              $address_actions,
                              'reverse_postal_code');

    $this->loader->add_action("{$ajaxHandle}coleteonline_postal_code_search",
                              $address_actions,
                              'search_postal_code');

    $this->loader->add_action("{$ajaxHandle}nopriv_coleteonline_postal_code_search",
                              $address_actions,
                              'search_postal_code');

    $this->loader->add_action("{$ajaxHandle}coleteonline_validate_postal_code",
                              $address_actions,
                              'validate_postal_code');

    $this->loader->add_action("{$ajaxHandle}nopriv_coleteonline_validate_postal_code",
                              $address_actions,
                              'validate_postal_code');

    $this->loader->add_action("{$ajaxHandle}coleteonline_autocomplete_city_state_merged",
                              $address_actions,
                              'autocomplete_city_state_merged');

    $this->loader->add_action("{$ajaxHandle}nopriv_coleteonline_autocomplete_city_state_merged",
                              $address_actions,
                              'autocomplete_city_state_merged');

    $this->loader->add_action("{$ajaxHandle}coleteonline_autocomplete_city",
                              $address_actions,
                              'autocomplete_city');

    $this->loader->add_action("{$ajaxHandle}nopriv_coleteonline_autocomplete_city",
                              $address_actions,
                              'autocomplete_city');

    $this->loader->add_action("{$ajaxHandle}coleteonline_autocomplete_street",
                              $address_actions,
                              'autocomplete_street');

    $this->loader->add_action("{$ajaxHandle}nopriv_coleteonline_autocomplete_street",
                              $address_actions,
                              'autocomplete_street');

    $address_filters = $plugin_public->get_address_filters();
    $this->loader->add_filter('woocommerce_formatted_address_replacements',
                              $address_filters,
                              'formatted_address_replacements',
                              10,
                              2);

    $this->loader->add_filter('woocommerce_get_order_address',
                              $address_filters,
                              'complete_order_address_data',
                              15,
                              3);

    $this->loader->add_filter('woocommerce_order_get_billing_address_1',
                              $address_filters,
                              'complete_billing_address_field',
                              15,
                              2);

    if (get_option("coleteonline_checkout_form_type") !== "woocommerceDefault") {
      $this->loader->add_filter('woocommerce_billing_fields',
                                $address_filters,
                                'change_billing_fields');

      $this->loader->add_filter('woocommerce_shipping_fields',
                                $address_filters,
                                'change_shipping_fields');

      $this->loader->add_filter('woocommerce_get_country_locale',
                                $address_filters,
                                'change_country_locale');

      $this->loader->add_filter('woocommerce_localisation_address_formats',
                                $address_filters,
                                'localize_address_format');

      $this->loader->add_filter('woocommerce_my_account_my_address_formatted_address',
                                $address_filters,
                                'complete_my_account_address_data',
                                15,
                                3);
    }

    $checkout_actions = $plugin_public->get_checkout_actions();

    $this->loader->add_action('woocommerce_checkout_update_order_review',
                              $checkout_actions,
                              'update_session');

    $this->loader->add_action('woocommerce_after_shipping_rate',
                              $checkout_actions,
                              'modify_shipping_option',
                              20,
                              2);

    $this->loader->add_action('woocommerce_review_order_before_shipping',
                              $checkout_actions,
                              'display_before_shipping_options');

    $this->loader->add_action('woocommerce_review_order_after_shipping',
                              $checkout_actions,
                              'display_shipping_options');

    $this->loader->add_action('woocommerce_checkout_create_order',
                              $checkout_actions,
                              'create_order_update_customer_note',
                              20,
                              2);


    $this->loader->add_action('woocommerce_after_checkout_validation',
                              $checkout_actions,
                              'validate_checkout');


    $this->loader->add_action('woocommerce_checkout_create_order',
                              $checkout_actions,
                              'create_order_set_shipping_options',
                              20,
                              2);

    $this->loader->add_action('woocommerce_checkout_create_order_shipping_item',
                              $checkout_actions,
                              'checkout_create_order_shipping_item_handler',
                              20,
                              4);

    $this->loader->add_action('woocommerce_add_to_cart',
                              $checkout_actions,
                              'add_to_cart_action',
                              10,
                              6);

  }

  private function define_wc_shipping_hooks() {
    $this->loader->add_action('woocommerce_shipping_init',
                              $this,
                              'load_shipping');
    $this->loader->add_action('woocommerce_shipping_methods',
                              $this,
                              'shipping_methods');
  }

  public function load_shipping() {
    require_once plugin_dir_path( dirname( __FILE__ ) ) .
      'includes/class-colete-online-shipping-method.php';
  }

  public function shipping_methods($methods) {
    $methods['coleteonline'] = 'ColeteOnline_Shipping_Method';

    return $methods;
  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    1.0.0
   */
  public function run() {
    $this->loader->run();
  }

  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     1.0.0
   * @return    string    The name of the plugin.
   */
  public function get_plugin_name() {
    return $this->plugin_name;
  }

  /**
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @since     1.0.0
   * @return    Colete_Online_Loader    Orchestrates the hooks of the plugin.
   */
  public function get_loader() {
    return $this->loader;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     1.0.0
   * @return    string    The version number of the plugin.
   */
  public function get_version() {
    return $this->version;
  }

}
