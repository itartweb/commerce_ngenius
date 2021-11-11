<?php

namespace Drupal\commerce_ngenius\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\PaymentGatewayInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\HasPaymentInstructionsInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsVoidsInterface;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsRefundsInterface;

/**
 * Provides the base interface for manual payment gateways.
 *
 * Manual payment gateways instruct the customer to pay the store
 * in the real world. The gateway creates a payment entity to allow
 * the merchant to track and record the money flow.
 *
 * Examples: cash on delivery, pay in person, cheque, bank transfer, etc.
 */
interface NgeniusPaymentIframeInterface extends PaymentGatewayInterface, HasPaymentInstructionsInterface, SupportsVoidsInterface, SupportsRefundsInterface {

  /**
   * Creates a payment.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentInterface $payment
   *   The payment.
   * @param bool $received
   *   Whether the payment was already received.
   */
  public function createPayment(PaymentInterface $payment, $received = FALSE);

}
