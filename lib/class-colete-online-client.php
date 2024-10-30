<?php

namespace ColeteOnline;

defined( 'ABSPATH' ) || exit;

require_once COLETE_ONLINE_ROOT . "/lib/api/class-colete-online-authenticate.php";
require_once COLETE_ONLINE_ROOT . "/lib/exceptions/colete-online-http-unauthorized-exception.php";
require_once COLETE_ONLINE_ROOT . "/lib/api/class-colete-online-url.php";
require_once COLETE_ONLINE_ROOT . "/lib/http/class-colete-online-http-request.php";

class ColeteOnlineClient {

  private $base_url;

  private ColeteOnlineAuthenticate $auth;
  public function __construct(string $client_id, string $client_secret) {
    $this->base_url = Url::getApi();

    $this->auth = new ColeteOnlineAuthenticate($client_id, $client_secret);
  }

  public function get_available_services($is_retry = false) {
    try {
      $url = $this->base_url . "service/list";

      $headers = array("Authorization" => "Bearer " . $this->auth->get_token());
      $response = (HttpRequest::get())->get($url, array(
        "type" => array(
          "domestic", "import", "export"
        )), $headers);
      return $response;
    } catch (ColeteOnlineHttpUnauthorizedException $e) {
      if ($is_retry === false) {
        $this->auth->logout();
        return $this->get_available_services(true);
      }
      throw $e;
    }
  }

  public function get_all_addresses($is_retry = false) {
    try {
      $url = $this->base_url . "address";

      $headers = array("Authorization" => "Bearer " . $this->auth->get_token());
      $page = 1;
      $totalPages = 1;
      $hardTotal = 50;

      $format = function($address) {
        $formatted = "";
        if (isset($address["contact"]["name"])) {
          $formatted .= $address["contact"]["name"] . " ";
        }
        if (isset($address["contact"]["phone"])) {
          $formatted .= $address["contact"]["phone"];
          if (isset($address["contact"]["phone2"])) {
            $formatted .= "/" . $address["contact"]["phone2"];
          }
          $formatted .= " ";
        }
        $formatted .= $address["address"]["city"] . ", ";
        $formatted .= $address["address"]["county"] . ", ";
        $formatted .= $address["address"]["street"] . ", ";
        $formatted .= $address["address"]["countryCode"] . ", ";
        $formatted .= $address["address"]["postalCode"];
        return $formatted;
      };

      $addresses = [];
      while ($page <= $totalPages && $hardTotal-- > 0) {
        $response = ((HttpRequest::get()))
          ->get($url, array("page" => $page), $headers);
        $totalPages = $response["pagination"]["totalPages"];
        ++$page;

        foreach ($response["data"] as $address) {
          $addresses[] = array(
            "id" => $address["locationId"],
            "shortName" => $address["shortName"],
            "address" => $format($address),
            "addressObject" => $address
          );
        }
      }

      return $addresses;
    } catch (ColeteOnlineHttpUnauthorizedException $e) {
      if ($is_retry === false) {
        $this->auth->logout();
        return $this->get_all_addresses(true);
      }
      throw $e;
    }
  }

  public function validate_phone(string $country_code, string $phone_number,
                                 bool $is_retry = false) {
    try {
      $url = $this->base_url . "validate/phone/$country_code/$phone_number";
      $headers = array("Authorization" => "Bearer " . $this->auth->get_token());
      $response = ((HttpRequest::get()))->get($url, array(), $headers);

      return $response;
    } catch (ColeteOnlineHttpUnauthorizedException $e) {
      if ($is_retry === false) {
        $this->auth->logout();
        return $this->validate_phone($country_code, $phone_number, true);
      }
      throw $e;
    }
  }

  public function validate_email(string $email_address,
                                 bool $is_retry = false) {
    try {
      $url = $this->base_url . "validate/email/$email_address";
      $headers = array("Authorization" => "Bearer " . $this->auth->get_token());
      $response = ((HttpRequest::get()))->get($url, array(), $headers);

      return $response;
    } catch (ColeteOnlineHttpUnauthorizedException $e) {
      if ($is_retry === false) {
        $this->auth->logout();
        return $this->validate_email($email_address, true);
      }
      throw $e;
    }
  }

  public function search_reverse_postal_code(string $country_code,
                                             string $postal_code,
                                             bool $is_retry = false) {
    try {
      $url = $this->base_url .
             "search/postal-code-reverse/$country_code/$postal_code";
      $headers = array("Authorization" => "Bearer " . $this->auth->get_token());
      $response = ((HttpRequest::get()))
                  ->get($url, array("format" => "object"), $headers);

      return $response;
    } catch (ColeteOnlineHttpUnauthorizedException $e) {
      if ($is_retry === false) {
        $this->auth->logout();
        return $this->search_reverse_postal_code($country_code, $postal_code,
                                                 true);
      }
      throw $e;
    }
  }

  public function search_postal_code(string $country_code,
                                     string $county,
                                     string $city,
                                     string $street,
                                     int $validate_street = 2,
                                     bool $is_retry = false) {
    try {
      $url = $this->base_url .
             "search/postal-code/$country_code/$city/$county/$street";
      $headers = array("Authorization" => "Bearer " . $this->auth->get_token());
      $params = array("validateStreet" => $validate_street,
                      "limit" => 1);
      if ($country_code === "RO") {
        $params["isCountyCode"] = true;
      }
      $response = ((HttpRequest::get()))->get($url, $params, $headers);
      return $response;
    } catch (ColeteOnlineHttpUnauthorizedException $e) {
      if ($is_retry === false) {
        $this->auth->logout();
        return $this->search_postal_code($country_code, $county, $city,
                                         $street, $validate_street, true);
      }
      throw $e;
    }
  }

  public function validate_postal_code(string $country_code,
                                       string $county,
                                       string $city,
                                       string $street,
                                       string $postal_code,
                                       int $validate_street = 0,
                                       bool $is_retry = false) {
    try {
      $url = $this->base_url . "search/validate-postal-code/$country_code/" .
                                "$city/$county/$street/$postal_code";
      $headers = array("Authorization" => "Bearer " . $this->auth->get_token());
      $params = array("validateStreet" => $validate_street);
      if ($country_code === "RO") {
        $params["isCountyCode"] = true;
      }
      $response = ((HttpRequest::get()))
                  ->get($url, $params, $headers);
      return $response;
    } catch (ColeteOnlineHttpUnauthorizedException $e) {
      if ($is_retry === false) {
        $this->auth->logout();
        return $this->validate_postal_code($country_code, $county, $city,
                                          $street, $postal_code,
                                          $validate_street, true);
      }
      throw $e;
    }
  }

  public function search_location_merged(string $country_code,
                                         string $needle,
                                         int $page,
                                         int $count,
                                         bool $is_retry = false) {
    try {
      $url = $this->base_url . "search/location/$country_code/$needle";
      $headers = array("Authorization" => "Bearer " . $this->auth->get_token());
      $response = ((HttpRequest::get()))->get(
          $url,
          array("format" => "objectFull",
                "group" => true,
                "page" => $page,
                "limit" => $count
              ),
          $headers);
      return $response;
    } catch (ColeteOnlineHttpUnauthorizedException $e) {
      if ($is_retry === false) {
        $this->auth->logout();
        return $this->search_location_merged($country_code, $needle, $page,
                                             $count, true);
      }
      throw $e;
    }
  }

  public function search_street(string $country_code,
                                string $county_code,
                                string $city,
                                string $needle,
                                int $page,
                                int $count,
                                bool $is_retry = false) {
    try {
      $url = $this->base_url .
      "search/street/$country_code/$city/$county_code/$needle";
      $headers = array("Authorization" => "Bearer " . $this->auth->get_token());
      $response = ((HttpRequest::get()))->get(
          $url,
          array("isCountyCode" => "true",
                "page" => $page,
                "limit" => $count
              ),
          $headers);
      return $response;
    } catch (ColeteOnlineHttpUnauthorizedException $e) {
      if ($is_retry === false) {
        $this->auth->logout();
        return $this->search_street($country_code, $county_code, $city, $needle,
                                    $page, $count, true);
      }
      throw $e;
    }

  }

    public function search_city(string $country_code,
                                string $county_code,
                                string $needle,
                                int $page,
                                int $count,
                                $is_retry = false) {
    try {
      $url = $this->base_url .
             "search/city/$country_code/$county_code/$needle";
      $headers = array("Authorization" => "Bearer " . $this->auth->get_token());
      $response = ((HttpRequest::get()))->get(
            $url,
            array("isCountyCode" => true,
                  "group" => true,
                  "page" => $page,
                  "limit" => $count
                ),
            $headers);

      return $response;
    } catch (ColeteOnlineHttpUnauthorizedException $e) {
      if ($is_retry === false) {
        $this->auth->logout();
        return $this->search_city($country_code, $county_code, $needle, $page,
                                  $count, true);
      }
      throw $e;
    }
  }

  public function get_prices(array $data = array(), $is_retry = false) {
    try {
      $url = $this->base_url . "order/price";
      $headers = array("Authorization" => "Bearer " . $this->auth->get_token());
      $response = ((HttpRequest::get()))->post($url, $data, $headers);
      return $response;
    } catch (ColeteOnlineHttpUnauthorizedException $e) {
      if ($is_retry === false) {
        $this->auth->logout();
        return $this->get_prices($data, true);
      }
      throw $e;
    }
  }

  public function create_order(array $data = array(), $is_retry = false) {
    try {
      $url = $this->base_url . "order";
      $headers = array("Authorization" => "Bearer " . $this->auth->get_token());
      $response = ((HttpRequest::get()))->post($url, $data, $headers, 65);
      return $response;
    } catch (ColeteOnlineHttpUnauthorizedException $e) {
      if ($is_retry === false) {
        $this->auth->logout();
        return $this->create_order($data, true);
      }
      throw $e;
    }
  }

  public function get_order_file($unique_id, array $data = array(),
                                 $is_retry = false) {
    try {
      $url = $this->base_url . "order/awb/" . $unique_id;
      $headers = array("Authorization" => "Bearer " . $this->auth->get_token());
      $response = ((HttpRequest::get()))->get(
        $url,
        array_merge(array('responseType' => 'buffer'), $data),
        $headers
      );
      return $response;
    } catch (ColeteOnlineHttpUnauthorizedException $e) {
      if ($is_retry === false) {
        $this->auth->logout();
        return $this->get_order_file($unique_id, $data, true);
      }
      throw $e;
    }
  }

}