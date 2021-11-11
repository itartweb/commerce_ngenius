<?php

namespace Drupal\commerce_ngenius\Plugin\Commerce\PaymentGateway;

use Drupal\commerce_payment\Entity\PaymentInterface;
use Drupal\commerce_payment\Entity\PaymentMethodInterface;
use Drupal\commerce_payment\PaymentMethodTypeManager;
use Drupal\commerce_payment\PaymentTypeManager;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OnsitePaymentGatewayBase;
use Drupal\commerce_price\MinorUnitsConverterInterface;
use Drupal\commerce_price\Price;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the N-Genius payment gateway.
 *
 * @CommercePaymentGateway(
 *   id = "ngenius_payment_iframe",
 *   label = "N-Genius payment gateway (iframe)",
 *   display_label = "N-Genius payment gateway (iframe)",
 *   modes = {
 *     "test" = @Translation("Test"),
 *     "live" = @Translation("Live"),
 *   },
 *   forms = {
 *     "add-payment" = "Drupal\commerce_ngenius\PluginForm\NgeniusPaymentIframeAddForm",
 *     "add-payment-method" = "Drupal\commerce_ngenius\PluginForm\NgeniusPaymentMethodIframeAddForm",
 *     "edit-payment-method" = "Drupal\commerce_payment\PluginForm\PaymentMethodEditForm",
 *   },
 *   payment_type = "payment_ngenius_iframe",
 *   requires_billing_information = FALSE,
 * )
 */
class NgeniusPaymentIframe extends OnsitePaymentGatewayBase implements NgeniusPaymentIframeInterface {

  /**
   * @var \Drupal\commerce_price\Entity\CurrencyInterface
   */
  protected $currencyStorage;

  /**
   * Constructs a new Manual object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_payment\PaymentTypeManager $payment_type_manager
   *   The payment type manager.
   * @param \Drupal\commerce_payment\PaymentMethodTypeManager $payment_method_type_manager
   *   The payment method type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   * @param \Drupal\commerce_price\MinorUnitsConverterInterface $minor_units_converter
   *   The minor units converter.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    PaymentTypeManager $payment_type_manager,
    PaymentMethodTypeManager $payment_method_type_manager,
    TimeInterface $time,
    MinorUnitsConverterInterface $minor_units_converter
  ) {
    parent::__construct(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $entity_type_manager,
      $payment_type_manager,
      $payment_method_type_manager,
      $time,
      $minor_units_converter
    );

    $this->currencyStorage = $entity_type_manager->getStorage('commerce_currency');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.commerce_payment_type'),
      $container->get('plugin.manager.commerce_payment_method_type'),
      $container->get('datetime.time'),
      $container->get('commerce_price.minor_units_converter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
        'outlet_ref' => '',
        'apikey_session' => '',
        'apikey_payment' => '',
      ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['outlet_ref'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Outlet ref'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
      '#default_value' => $this->configuration['outlet_ref'],
    ];

    $form['apikey_session'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key (Session)'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
      '#default_value' => $this->configuration['apikey_session'],
    ];

    $form['apikey_payment'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API key (Payment)'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
      '#default_value' => $this->configuration['apikey_payment'],
    ];

    $options = [];
    /** @var \Drupal\commerce_price\Entity\CurrencyInterface[] $currencies */
    $currencies = $this->currencyStorage->loadMultiple();
    foreach ($currencies as $currency) {
      $options[$currency->id()] = $currency->label();
    }
    $form['currency'] = [
      '#type' => 'select',
      '#title' => $this->t('Currency (default)'),
      '#options' => $options,
      '#default_value' => $this->configuration['currency'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $values = $form_state->getValue($form['#parents']);
    $this->configuration['outlet_ref'] = $values['outlet_ref'];
    $this->configuration['apikey_session'] = $values['apikey_session'];
    $this->configuration['apikey_payment'] = $values['apikey_payment'];
    $this->configuration['currency'] = $values['currency'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaymentInstructions(PaymentInterface $payment) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function createPayment(PaymentInterface $payment, $received = FALSE) {
    $this->assertPaymentState($payment, ['new']);

    $payment->state = $received ? 'completed' : 'pending';
    $payment->save();
  }

  /**
   * {@inheritdoc}
   */
  public function voidPayment(PaymentInterface $payment) {
    // @todo do code here.
  }

  /**
   * {@inheritdoc}
   */
  public function refundPayment(PaymentInterface $payment, Price $amount = NULL) {
    // @todo do code here.
  }

  /**
   * {@inheritdoc}
   */
  public function createPaymentMethod(PaymentMethodInterface $payment_method, array $payment_details) {
    // @todo do code here.
  }

  /**
   * {@inheritdoc}
   */
  public function deletePaymentMethod(PaymentMethodInterface $payment_method) {
    $payment_method->delete();
  }

  /**
   * {@inheritdoc}
   */
  public function updatePaymentMethod(PaymentMethodInterface $payment_method) {
    // @todo do code here.
  }

}
