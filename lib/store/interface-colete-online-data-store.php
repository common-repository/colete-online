<?php

namespace ColeteOnline;

defined( 'ABSPATH' ) || exit;

interface InterfaceDataStore {
  public function get(string $key);
  public function set(string $key, $data): void;
}