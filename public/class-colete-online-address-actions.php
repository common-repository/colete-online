<?php

defined( 'ABSPATH' ) || exit;

require_once COLETE_ONLINE_ROOT . "/lib/class-colete-online-client.php";

class ColeteOnlineAddressActions {

  public function __construct() {}

  public function reverse_postal_code() {
    try {
      if (isset($_GET['country']) && isset($_GET['postal_code'])) {
        $settings = get_option("woocommerce_coleteonline_settings");
        $response = (new \ColeteOnline\ColeteOnlineClient(
          $settings['client_id'],
          $settings['client_secret']
        ))->search_reverse_postal_code(
          wc_clean(wp_unslash($_GET['country'])),
          wc_clean(wp_unslash($_GET['postal_code']))
        );
        echo json_encode($response);
      } else {
        echo json_encode(array("error" => true));
      }
    } catch (Exception $e) {
      echo json_encode(array("error" => true));
    }
    wp_die();
  }

  public function search_postal_code() {
    try {
      if (
        isset($_GET['country']) && isset($_GET['county']) &&
        isset($_GET['city']) && isset($_GET['street'])
      ) {
        $settings = get_option("woocommerce_coleteonline_settings");
        $response = (new \ColeteOnline\ColeteOnlineClient(
          $settings['client_id'],
          $settings['client_secret'],
        ))->search_postal_code(
          wc_clean(wp_unslash($_GET['country'])),
          wc_clean(wp_unslash($_GET['county'])),
          wc_clean(wp_unslash($_GET['city'])),
          wc_clean(wp_unslash($_GET['street'])),
          wc_clean(wp_unslash($_GET['validate_street']))
        );
        echo json_encode($response);
      } else {
        echo json_encode(array("error" => true));
      }
    } catch (Exception $e) {
      echo json_encode(array("error" => true));
    }
    wp_die();
  }

  public function autocomplete_city_state_merged() {
    try {
      if (isset($_GET['country']) && isset($_GET['q'])) {
        $settings = get_option("woocommerce_coleteonline_settings");
        $response = (new \ColeteOnline\ColeteOnlineClient(
          $settings['client_id'],
          $settings['client_secret']
        ))->search_location_merged(
          wc_clean(wp_unslash($_GET['country'])),
          wc_clean(wp_unslash($_GET['q'])),
          wc_clean(wp_unslash($_GET['page'])),
          20
        );
        echo json_encode($response);
      }
    } catch (\Exception $e) {
      echo json_encode(array("error" => true));
    }
    wp_die();
  }

  public function autocomplete_city() {
    try {
      if (
        isset($_GET['country']) && isset($_GET['county_code']) &&
        isset($_GET['q'])
      ) {
        $settings = get_option("woocommerce_coleteonline_settings");
        $response = (new \ColeteOnline\ColeteOnlineClient(
          $settings['client_id'],
          $settings['client_secret']
        ))->search_city(
          wc_clean(wp_unslash($_GET['country'])),
          wc_clean(wp_unslash($_GET['county_code'])),
          wc_clean(wp_unslash($_GET['q'])),
          wc_clean(wp_unslash($_GET['page'])),
          20
        );
        echo json_encode($response);
      }
    } catch (Exception $e) {
      echo json_encode(array("error" => true));
    }
    wp_die();
  }

  public function autocomplete_street() {
    try {
      if (
        isset($_GET['country']) && isset($_GET['county']) &&
        isset($_GET['q'])
      ) {
        $settings = get_option("woocommerce_coleteonline_settings");
        $response = (new \ColeteOnline\ColeteOnlineClient(
          $settings['client_id'],
          $settings['client_secret']
        ))->search_street(
          wc_clean(wp_unslash($_GET['country'])),
          wc_clean(wp_unslash($_GET['county'])),
          wc_clean(wp_unslash($_GET['city'])),
          wc_clean(wp_unslash($_GET['q'])),
          wc_clean(wp_unslash($_GET['page'])),
          20
        );
        echo json_encode($response);
      }
    } catch (Exception $e) {
      echo wp_json_encode(array("error" => true));
    }
    wp_die();
  }

  private function validate_phone_helper($phone, $country) {
    try {
      $settings = get_option("woocommerce_coleteonline_settings");
      $response = (new \ColeteOnline\ColeteOnlineClient(
        $settings['client_id'],
        $settings['client_secret']
      ))->validate_phone($country, $phone);
      return $response;
    } catch (Exception $e) {
      return array("error" => true);
    }
  }

  private function validate_email_helper($email) {
    try {
      $settings = get_option("woocommerce_coleteonline_settings");
      $response = (new \ColeteOnline\ColeteOnlineClient(
        $settings['client_id'],
        $settings['client_secret']
      ))->validate_email($email);
      return $response;
    } catch (Exception $e) {
      return array("error" => true);
    }
  }

  public function validate_phone_ajax() {
    if (isset($_POST['phone_number']) && isset($_POST['country'])) {
      echo json_encode($this->validate_phone_helper(
        wc_clean(wp_unslash($_POST["phone_number"])),
        wc_clean(wp_unslash($_POST["country"])))
      );
    } else {
      echo json_encode(array("error" => true));
    }
    wp_die();
  }

  public function validate_postal_code() {
    if (isset($_GET['country']) && isset($_GET['county']) &&
        isset($_GET['city']) && isset($_GET['street']) &&
        isset($_GET['postal_code'])) {
      $settings = get_option("woocommerce_coleteonline_settings");
      $response = (new \ColeteOnline\ColeteOnlineClient(
        $settings['client_id'],
        $settings['client_secret']
      ))->validate_postal_code(
        wc_clean(wp_unslash($_GET['country'])),
        wc_clean(wp_unslash($_GET['county'])),
        wc_clean(wp_unslash($_GET['city'])),
        wc_clean(wp_unslash($_GET['street'])),
        wc_clean(wp_unslash($_GET['postal_code']))
      );
      echo json_encode($response);
    } else {
      echo json_encode(array("error" => true));
    }
    wp_die();
  }

  private function validate_postal_code_city_state_checkout($posted) {
    if (get_option("coleteonline_address_validate_address_checkout") === "yes") {
      $country = $posted['shipping_country'];
      $postal_code = $posted['shipping_postcode'];
      $state = $posted['shipping_state'];
      $city = $posted['shipping_city'];
      $street = $posted['shipping_street'] ? $posted['shipping_street'] :
                                             $posted['shipping_address_1'];
      if ($country === "RO") {
        $show_notice = 0;
        if (!strlen($country) || !strlen($postal_code) || !strlen($state) ||
            !strlen($city) || !strlen($street)) {
          $show_notice = 1;
        }

        if (!$show_notice) {
          try {
            if ($state === "B") {
              $state = $city;
              $city = "Bucuresti";
            }
            $settings = get_option("woocommerce_coleteonline_settings");
            $response = (new \ColeteOnline\ColeteOnlineClient(
              $settings['client_id'],
              $settings['client_secret']
            ))->validate_postal_code($country, $state, $city, $street,
                                     $postal_code);
            if (!$response['format']) {
              $show_notice = 2;
            } else if (isset($response['location']) && !$response['location']) {
              $show_notice = 3;
            }
          } catch (Exception $e) {}
        }

        if ($show_notice) {
          if ($show_notice === 1) {
            wc_add_notice(__("Please fill all the address fields.", 'coleteonline'), 'error');
          } else if ($show_notice === 2) {
            wc_add_notice(__("The postal code format is invalid for the selected country.", 'coleteonline'), 'error');
          } else if ($show_notice === 3) {
            wc_add_notice(__("The county, locality, street and postal code combination is invalid.", 'coleteonline'), 'error');
          }
        }
      }
    }
  }

  private function validate_phone_checkout($posted) {
    if (get_option("coleteonline_address_validate_phone") === "yes") {
      $phone = $posted["shipping_phone"] ? $posted["shipping_phone"] :
                                           $posted["billing_phone"];
      $country = $posted["billing_country"];
      $show_notice = false;
      if (!strlen($phone)) {
        $show_notice = true;
      } else {
        $response = $this->validate_phone_helper($phone, $country);
        if (!$response["valid"] && !isset($response["error"])) {
          $show_notice = true;
        }
      }
      if ($show_notice) {
        wc_add_notice(__("Invalid phone number.", 'coleteonline'), 'error');
      }
    }
  }

  private function validate_email_checkout($posted) {
    if (get_option("coleteonline_address_validate_email") === "yes") {
      $email = $posted["billing_email"];
      $show_notice = false;
      if (!isset($email) || !strlen($email)) {
        $show_notice = true;
      } else {
        $response = $this->validate_email_helper($email);
        if (!$response["valid"] && !isset($response["error"])) {
          $show_notice = true;
        }
      }
      if ($show_notice) {
        wc_add_notice(__("Invalid email address.", 'coleteonline'), 'error');
      }
    }
  }

  public function validate_checkout($posted) {
    if (get_option("coleteonline_checkout_form_type", "coleteonline") !== "woocommerceDefault") {
      $this->validate_postal_code_city_state_checkout($posted);
      $this->validate_phone_checkout($posted);
      $this->validate_email_checkout($posted);
    }
  }



}
