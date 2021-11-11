<?php

namespace Drupal\commerce_ngenius\Controller;

use Drupal\commerce_ngenius\NgeniusPaymentManager;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\commerce_checkout\CheckoutOrderManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Psr\Log\LoggerInterface;

/**
 * Class NgeniusController.
 */
class NgeniusController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The checkout order manager.
   *
   * @var \Drupal\commerce_checkout\CheckoutOrderManagerInterface
   */
  protected $checkoutOrderManager;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The ngenius payment manager service.
   *
   * @var \Drupal\commerce_ngenius\NgeniusPaymentManager
   */
  protected $ngeniusPaymentManager;

  /**
   * Constructs a new PaymentCheckoutController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_checkout\CheckoutOrderManagerInterface $checkout_order_manager
   *   The checkout order manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   * @param \Drupal\commerce_ngenius\NgeniusPaymentManager $ngenius_payment_manager
   *   The ngenius payment manager service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    CheckoutOrderManagerInterface $checkout_order_manager,
    LoggerInterface $logger,
    NgeniusPaymentManager $ngenius_payment_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->checkoutOrderManager = $checkout_order_manager;
    $this->logger = $logger;
    $this->ngeniusPaymentManager = $ngenius_payment_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('commerce_checkout.checkout_order_manager'),
      $container->get('logger.channel.commerce_payment'),
      $container->get('commerce_ngenius.payment_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function saveSessionId(Request $request, RouteMatchInterface $route_match) {
    $params = [];
    $session_id = $request->request->get('session_id');
    $payment_gateway_id = $request->request->get('payment_gateway_id');

    if (!empty($session_id)) {
      /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
      $order = $route_match->getParameter('commerce_order');
      $order->set('remote_session_id', $session_id);
      $order->set('payment_gateway', $payment_gateway_id);
      $order->save();

      $payment_gateway_storage = $this->entityTypeManager->getStorage('commerce_payment_gateway');
      $payment_gateway = $payment_gateway_storage->load($payment_gateway_id);
      /** @var \Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\OnsitePaymentGatewayInterface $payment_gateway_plugin */
      $payment_gateway_plugin = $payment_gateway->getPlugin();
      $config = $payment_gateway_plugin->getConfiguration();

      $urls = $this->ngeniusPaymentManager->getApiUrls($config['mode']);

      $head = ['Authorization: Basic ' . $config['apikey_payment'], 'Content-Type: application/vnd.ni-identity.v1+json'];
      $url = $urls['token'];
      $response = $this->ngeniusPaymentManager->invokeCurlRequest('POST', $url, $head);
      $tokenResponse = json_decode($response);
      $accessToken = $tokenResponse->access_token;

      if ($accessToken) {
        $url = $urls['payment'] . '/' . $config['outlet_ref'] . '/payment/hosted-session/' . $session_id;
        $head = ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/vnd.ni-payment.v2+json', 'Accept: application/vnd.ni-payment.v2+json'];

        $amount = $order->getTotalPrice();
        // @todo it needs to be refactored.
        $moduleHandler = \Drupal::service('module_handler');
        if ($moduleHandler->moduleExists('commerce_currency_resolver')) {
          $amount = \Drupal::service('commerce_currency_resolver.calculator')->priceConversion($amount, $config['currency']);
        }

        $post = [
          'action' => 'SALE',
          'amount' => [
            'currencyCode' => $config['currency'],
            'value' => $amount->getNumber() * 100,
          ],
        ];
        $post = json_encode($post);
        $response = $this->ngeniusPaymentManager->invokeCurlRequest('POST', $url, $head, $post);
        $paymentResponse = json_decode($response);

        $order->set('remote_payment_response', serialize($paymentResponse));
        $order->save();

        $params['paymentResponse'] = $paymentResponse;
      }
    }

    return new JsonResponse($params, 200);
  }

  /**
   * {@inheritdoc}
   */
  public function checkPayment(Request $request, RouteMatchInterface $route_match) {
    $params = [];
    $status = $request->request->get('status');

    $params['status'] = 'fail';
    if (!empty($status)) {
      /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
      $order = $route_match->getParameter('commerce_order');

      if ($status == 'AUTHORISED' || $status == 'CAPTURED') {
        $params['status'] = 'complete';

        // @todo Complete payment.

        // @todo Complete order.
      }
    }

    return new JsonResponse($params, 200);
  }

}
