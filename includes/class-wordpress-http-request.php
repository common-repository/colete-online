<?php

namespace ColeteOnline;

defined( 'ABSPATH' ) || exit;

require_once COLETE_ONLINE_ROOT . "/lib/http/interface-colete-online-http-request.php";
require_once COLETE_ONLINE_ROOT . "/lib/exceptions/colete-online-server-error-exception.php";
require_once COLETE_ONLINE_ROOT . "/lib/exceptions/colete-online-http-fail-exception.php";
require_once COLETE_ONLINE_ROOT . "/lib/exceptions/colete-online-http-unauthorized-exception.php";


class WordpressHttpRequest implements InterfaceHttpRequest {
  public function get(string $url,
                      array $data = array(),
                      array $headers = array(),
                      int $timeout = 10) {
    $params = array(
      'body' => $data,
      'headers' => self::add_version_header($headers),
      'timeout' => $timeout
    );

    $response = wp_remote_get($url, $params);
    return $this->handle_response($response);
  }

  public function post(string $url,
                       array $data = array(),
                       array $headers = array(),
                       int $timeout = 10) {
    $params = array(
      'body' => $data,
      'headers' => self::add_version_header($headers),
      'timeout' => $timeout
    );
    $response = wp_remote_post($url, $params);

    return $this->handle_response($response);
  }

  private function handle_response($response) {
    if ( is_array( $response ) && ! is_wp_error( $response ) ) {
      $code = wp_remote_retrieve_response_code($response);
      if ($code >= 500) {
        throw new ColeteOnlineServerErrorException($response, '', $code);
      }
      if ($code === 400) {
        throw new ColeteOnlineBadRequestException($response, '', $code);
      }
      if ($code === 401) {
        throw new ColeteOnlineHttpUnauthorizedException($response, '', $code);
      }
      if ($code > 400) {
        throw new \Exception();
      }
    } else {
      if (is_wp_error($response)) {
        if ($response->get_error_code() === 'http_request_failed') {
          throw new ColeteOnlineHttpFailException($response, '', 0);
        }
      }
      throw new \Exception();
    }

    return json_decode(wp_remote_retrieve_body($response), true);
  }

  private static function add_version_header($headers) {
    return array_merge(
      $headers,
      array('x-plugin-version' => 'woocommerce_' . COLETE_ONLINE_VERSION));
  }
}