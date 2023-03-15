<?php

namespace Drupal\currencylayer_proxy\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\currencylayer_proxy\Currency;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Currency controller class.
 */
class CurrencyController extends ControllerBase {

  /**
   * The currency service.
   *
   * @var \Drupal\currencylayer_proxy\Currency
   */
  protected $currency;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\currencylayer_proxy\Currency $currency
   *   The currency service.
   */
  public function __construct(Currency $currency) {
    $this->currency = $currency;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('currencylayer_proxy.currency')
      );
  }

  /**
   * Returns a cacheble JSON response.
   */
  public function content() {
    return $this->currency->getCurrencyData();
  }

}
