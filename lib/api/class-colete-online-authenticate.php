<?php

namespace ColeteOnline;

defined( 'ABSPATH' ) || exit;

require_once COLETE_ONLINE_ROOT . '/lib/store/class-colete-online-data-store.php';
require_once COLETE_ONLINE_ROOT . '/lib/api/class-colete-online-url.php';

class ColeteOnlineAuthenticate {

  private const TOKEN_KEY = "colete_online_api_token";
  private const EXPIRE_KEY = "colete_online_token_expiration";
  // private const AUTHENTICATE_URL = "https://auth.colete-online.ro/token";
  private string $authenticate_url;
  private InterfaceDataStore $store;

  private string $authorization;

  public function __construct(string $client_id, string $client_secret) {
    $this->store = DataStore::get();

    $this->authenticate_url = Url::getAuth();

    $this->authorization = \base64_encode($client_id . ":" . $client_secret);
  }


  public function get_token(bool $force = false): string {

    $timestamp = \time();

    $token = $this->store->get($this::TOKEN_KEY);
    $expire_at = $this->store->get($this::EXPIRE_KEY);

    if (\is_string($token) && $timestamp < $expire_at && !$force) {
      return $token;
    }

    $headers = array("Authorization" => "Basic " . $this->authorization);
    $body = array("grant_type" => "client_credentials");
    $url = $this->authenticate_url;

    $response = (new WordpressHttpRequest())->post($url, $body, $headers);

    $this->store->set($this::TOKEN_KEY, $response["access_token"]);
    $this->store->set($this::EXPIRE_KEY, $timestamp + $response["expires_in"]);

    return $response["access_token"];
  }

  public function logout() {
    $this->store->set($this::TOKEN_KEY, null);
    $this->store->set($this::EXPIRE_KEY, null);
  }

}