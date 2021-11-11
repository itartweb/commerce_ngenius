<?php

namespace Drupal\commerce_ngenius;

/**
 * {@inheritdoc}
 */
class NgeniusPaymentManager implements NgeniusPaymentManagerInterface {

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
  public function invokeCurlRequest($type, $url, $headers, $post = NULL) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    if ($type == 'POST') {
      curl_setopt($ch, CURLOPT_POST, 1);
      if (!empty($post)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      }
    }

    $server_output = curl_exec($ch);
    curl_close($ch);

    return $server_output;
  }

  /**
   * Get API Urls.
   *
   * @param string $mode
   *
   * @return array
   */
  public function getApiUrls($mode) {
    if ($mode == 'live') {
      return [
        'token' => NgeniusPaymentManagerInterface::LIVE_TOKEN_URL,
        'payment' => NgeniusPaymentManagerInterface::LIVE_PAYMENT_URL,
      ];
    }

    return [
      'token' => NgeniusPaymentManagerInterface::TEST_TOKEN_URL,
      'payment' => NgeniusPaymentManagerInterface::TEST_PAYMENT_URL,
    ];
  }

}
