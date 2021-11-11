<?php

namespace Drupal\commerce_ngenius\Plugin\Commerce\PaymentType;

use Drupal\commerce_payment\Plugin\Commerce\PaymentType\PaymentTypeBase;

/**
 * Provides the N-Genius payment type.
 *
 * @CommercePaymentType(
 *   id = "payment_ngenius_iframe",
 *   label = @Translation("N-Genius (iframe)"),
 *   workflow = "payment_manual",
 * )
 */
class PaymentNgeniusIframe extends PaymentTypeBase {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    return [];
  }

}
