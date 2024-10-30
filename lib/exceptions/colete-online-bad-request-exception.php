<?php

namespace ColeteOnline;

defined( 'ABSPATH' ) || exit;

require_once "colete-online-base-exception.php";

class ColeteOnlineBadRequestException extends ColeteOnlineBaseException {

  public function __construct($response, $message = '', $code = 0,
                              $previous = null) {
    parent::__construct($response, $message, $code, $previous);
  }

  public function maybeGetValidationErrors() {
    try {
      $data = json_decode(wp_remote_retrieve_body($this->getRawResponse()), true);
      if ($data['message'] === 'Validation errors') {
        $errors = array();
        foreach ($data['errors'] as $error) {
          $errors[] = array(
            'field' => $error['parameter'],
            'message' => $error['message']
          );
        }
        return $errors;
      }
    } catch (\Exception $e) {
      return false;
    }
  }

}
