<?php

namespace ColeteOnline;

defined( 'ABSPATH' ) || exit;

class ColeteOnlineBaseException extends \Exception {

  protected $response;
  protected $code;
  protected $message;

  public function __construct($response, $message = '', $code = 0,
                              $previous = null) {
    $this->response = $response;
    $this->message = $message;
    $this->code = $code;
    parent::__construct($message, $code, $previous);
  }

  public function getRawResponse() {
    return $this->response;
  }

}
