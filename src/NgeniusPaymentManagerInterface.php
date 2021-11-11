<?php

namespace Drupal\commerce_ngenius;

/**
 * {@inheritdoc}
 */
interface NgeniusPaymentManagerInterface {

  /**
   * Live mode.
   */
  const LIVE_TOKEN_URL = 'https://api-gateway.ngenius-payments.com/identity/auth/access-token';
  const LIVE_PAYMENT_URL = 'https://api-gateway.ngenius-payments.com/transactions/outlets';

  /**
   * Test mode.
   */
  const TEST_TOKEN_URL = 'https://api-gateway.sandbox.ngenius-payments.com/identity/auth/access-token';
  const TEST_PAYMENT_URL = 'https://api-gateway.sandbox.ngenius-payments.com/transactions/outlets';

  /**
   * Do Curl Request.
   *
   * @param string $type
   * @param string $url
   * @param array $headers
   * @param array $post
   *
   * @return mixed
   */
  public function invokeCurlRequest($type, $url, $headers, $post = NULL);

  /**
   * Get API Urls.
   *
   * @param string $mode
   *
   * @return array
   */
  public function getApiUrls($mode);

}
