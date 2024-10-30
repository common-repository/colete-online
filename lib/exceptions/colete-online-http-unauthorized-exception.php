<?php

namespace ColeteOnline;

defined( 'ABSPATH' ) || exit;

require_once "colete-online-base-exception.php";

class ColeteOnlineHttpUnauthorizedException extends ColeteOnlineBaseException {

  public function __construct($response, $message = '', $code = 0,
                              $previous = null) {
    parent::__construct($response, $message, $code, $previous);
  }

}
