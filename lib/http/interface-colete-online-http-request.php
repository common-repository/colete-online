<?php

namespace ColeteOnline;

defined( 'ABSPATH' ) || exit;

interface InterfaceHttpRequest {
  public function get(string $url,
                      array $data = array(),
                      array $headers = array(),
                      int $timeout = 10);
  public function post(string $url,
                      array $data = array(),
                      array $headers = array(),
                      int $timeout = 10);
}
