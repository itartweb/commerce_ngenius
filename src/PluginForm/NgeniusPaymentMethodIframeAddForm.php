<?php

namespace Drupal\commerce_ngenius\PluginForm;

use Drupal\commerce_payment\PluginForm\PaymentMethodAddForm as BasePaymentMethodAddForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * {@inheritdoc}
 */
class NgeniusPaymentMethodIframeAddForm extends BasePaymentMethodAddForm {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    /** @var \Drupal\commerce_payment\Entity\PaymentMethodInterface $payment_method */
    $payment_method = $this->entity;

    $form['#attached']['library'][] = 'commerce_ngenius/iframe';

    $form['payment_details'] = [
      '#parents' => array_merge($form['#parents'], ['payment_details']),
      '#type' => 'container',
      '#payment_method_type' => $payment_method->bundle(),
    ];

    $form['payment_details'] = $this->buildIframeForm($form['payment_details'], $form_state);

    // Move the billing information below the payment details.
    if (isset($form['billing_information'])) {
      $form['billing_information']['#weight'] = 10;
    }

    $payment_gateway = $payment_method->getPaymentGateway();
    /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OnsitePaymentGatewayInterface $payment_gateway_plugin */
    $payment_gateway_plugin = $payment_gateway->getPlugin();
    $config = $payment_gateway_plugin->getConfiguration();

    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = \Drupal::routeMatch()->getParameter('commerce_order');

    $js_settings = [
      'id' => 'ngenius-mount-id',
      'outletRef' => $config['outlet_ref'],
      'apiKey' => $config['apikey_session'],
      'payment_gateway_id' => $payment_gateway->id(),
      'session_id_save_url' => Url::fromRoute('commerce_ngenius.session_id', ['commerce_order' => $order->id()])->toString(),
      'check_payment_url' => Url::fromRoute('commerce_ngenius.check_payment', ['commerce_order' => $order->id()])->toString(),
      'cancel_url' => Url::fromRoute('commerce_checkout.form', ['commerce_order' => $order->id(), 'step' => 'payment_information'])->toString(),
      'complete_url' => Url::fromRoute('commerce_checkout.form', ['commerce_order' => $order->id(), 'step' => 'complete'])->toString(),
    ];

    $form['#attached']['drupalSettings']['commerce_ngenius'] = $js_settings;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildIframeForm(array $element, FormStateInterface $form_state) {
    $element['ngenius'] = [
      '#type' => 'markup',
      '#markup' => '<div id="ngenius-mount-id"></div>',
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function validateCreditCardForm(array &$element, FormStateInterface $form_state) {}

}
