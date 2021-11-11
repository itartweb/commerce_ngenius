<?php

namespace Drupal\commerce_ngenius\Plugin\Commerce\CheckoutFlow;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowWithPanesBase;

/**
 * Provides the iframe multistep checkout flow.
 *
 * @CommerceCheckoutFlow(
 *   id = "multistep_ngenius_iframe",
 *   label = "Multistep (N-Genius) - Iframe",
 * )
 */
class MultistepNgeniusIframe extends CheckoutFlowWithPanesBase {

  /**
   * {@inheritdoc}
   */
  public function getSteps() {
    // Note that previous_label and next_label are not the labels
    // shown on the step itself. Instead, they are the labels shown
    // when going back to the step, or proceeding to the step.
    return [
      'login' => [
        'label' => $this->t('Login'),
        'previous_label' => $this->t('Go back'),
        'has_sidebar' => FALSE,
      ],
      'order_information' => [
        'label' => $this->t('Order information'),
        'next_label' => $this->t('Continue to shipping'),
        'previous_label' => $this->t('Go back'),
        'has_sidebar' => TRUE,
      ],
      'payment_information' => [
        'label' => $this->t('Payment information'),
        'next_label' => $this->t('Continue to payment'),
        'previous_label' => $this->t('Go back'),
        'has_sidebar' => TRUE,
      ],
      'complete' => [
        'label' => $this->t('Complete'),
        'next_label' => $this->t('Complete checkout'),
        'has_sidebar' => FALSE,
      ],
    ];
  }

}
