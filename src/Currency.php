<?php

namespace Drupal\currencylayer_proxy;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableJsonResponse;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Currency service class.
 */
class Currency {

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The request endpoint.
   *
   * @var string
   */
  public $endpoint;

  /**
   * The api key.
   *
   * @var string
   */
  public $apikey;

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs a new CreateCaseService object.
   */
  public function __construct(ClientInterface $http_client, LoggerChannelFactoryInterface $logger_factory, RequestStack $request) {
    $this->httpClient = $http_client;
    $this->loggerFactory = $logger_factory;
    $this->request = $request->getCurrentRequest();
    // API credentials would be part of the drupal configuration.
    // Have initialised these here for this example only.
    $this->endpoint = 'https://api.apilayer.com/currency_data/change';
    $this->apikey = 'r91NcaAoGspcs7OjDVVtQYBHEP7VICIb';
  }

  /**
   * Function to call currency data API.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   */
  public function getCurrencyData() {
    // Get all query params.
    $start_date = $this->request->query->get('start_date');
    $end_date = $this->request->query->get('end_date');
    $currencies = $this->request->query->get('currencies');
    $currencies = $this->sortCurrencies($currencies);
    $source = $this->request->query->get('source');

    try {
      // Sending GET Request.
      $response = $this->httpClient->request('GET', $this->endpoint,
        [
          'headers' => [
            'Content-Type' => 'application/json',
            'application' => 'application/json',
            'apikey' => $this->apikey,
          ],
          'query' => [
            'start_date' => $start_date,
            'end_date' => $end_date,
            'currencies' => $currencies,
            'source' => $source,
          ],
        ]
      );

    }
    catch (GuzzleException $e) {
      watchdog_exception('currencylayer_proxy_exception', $e);
      $exception['exception'] = [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
      ];
      return new CacheableJsonResponse(json_encode($exception));
    }

    // Check if response object has data.
    if (!empty($response)) {
      // Get the status code.
      $status_code = $response->getStatusCode();
      // Get the body from response object.
      $body = json_decode($response->getBody(), TRUE);

      if ($status_code == 200) {
        // Log the success message.
        $this->loggerFactory->get('currencylayer_proxy_success')->notice($response->getBody());
        // Cache the response based on the query params and return.
        $response = new CacheableJsonResponse($body);
        // Set cache context and cache results for 1 day.
        $response->addCacheableDependency(CacheableMetadata::createFromRenderArray($body)->addCacheContexts(['url.query_args'])->setCacheMaxAge(86400));
        return $response;
      }

      // Check for the status code and return error message respectively.
      // @todo Make error messages translatable.
      switch ($status_code) {
        case "400":
          $error_message = "The request was unacceptable, often due to missing a required parameter.";
          break;

        case "401":
          $error_message = "No valid API key provided.";

          break;

        case "404":
          $error_message = "The requested resource doesn't exist.";

          break;

        case "429":
          $error_message = "API request limit exceeded. See section Rate Limiting for more info.";

          break;

        default:
          $error_message = "We have failed to process your request. (You can contact us anytime)";
      }

      $error['error'] = [
        'message' => $error_message,
        'code' => $status_code,
      ];
      return new CacheableJsonResponse(json_encode($error));
    }

  }

  /**
   * Helper function to sort currencies.
   */
  public function sortCurrencies($currencies) {
    $currencies_array = array_filter(array_map('trim', explode(',', $currencies)));
    if (count($currencies_array) == 1) {
      return $currencies;
    }
    asort($currencies_array);
    $currencies = implode(',', $currencies_array);

    return $currencies;
  }

}
