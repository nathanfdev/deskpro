<?php

/**
 * Orb.
 */

namespace Orb\Scraper;

/**
 * This scraper fetches data from any simple Orb web resource.
 *
 * To create a simple orb web service, your script must implement the following protocol:
 * - Accept POST'ed requests
 * - There is a `consumer_key` item which should be an agreed-upon secret code to identify and authorize
 * service users.
 * - There is a `data` item which may be an array of any data that your resource might use (for example ID's).
 *
 * Your resource must return a JSON-encoded data structure that will be decoded into an array. Array items must be:
 * - identity
 * - identity_friendly
 * - data: A single value or array of values
 */
class OrbResource extends AbstractScraper
{
    /**
     * HTTP client.
     *
     * @var \Zend\Http\Client
     */
    protected $http;

    /**
     * The following options are required:
     * - consumer_key: A key/password that identifies this consumer. The remote producer should
     * verify the key to ensure authorized access.
     * - service_url: The URL to the remote service.
     */
    public function __construct(array $options = [])
    {
        if (!$this->hasOption('consumer_key')) {
            throw new \InvalidArgumentException('Missing required option `consumer_key`');
        }
        if (!$this->hasOption('service_url')) {
            throw new \InvalidArgumentException('Missing required option `consumer_key`');
        }

        if ($this->hasOption('http')) {
            $this->setHttpClient($this->getOption('http'));
        }
    }

    /**
     * Get data from the resource.
     *
     * @param mixed $identity A string or array of k=>v pairs to be sent as posted 'data'
     *
     * @return ItemInterface
     */
    public function getData($identity = null)
    {
        $http = $this->getHttpClient();
        $http->setUri($this->getOption('service_url'));

        $http->setParameterPost('consumer_key', $this->getOption('consumer_key'));

        if ($identity) {
            if (is_array($identity)) {
                foreach ($identity as $k => $v) {
                    $http->setParameterPost('data['.$k.']', $v);
                }
            } else {
                $http->setParameterPost('data', $identity);
            }
        }

        $http_result = $http->request(\Zend\Http\Client::POST);

        $data = @json_decode($http_result->getBody(), true);
        if (!$data) {
            throw new \UnexpectedValueException('Invalid JSON returned from service');
        }

        $item = new \Orb\Scraper\Item($data['identity'], $data['identity_friendly'], $data['data']);

        return $item;
    }

    /**
     * Set a custom HTTP client. If one is not set, a default client will be used automatically.
     *
     * @param \Zend\Http\Client $http
     */
    public function setHttpClient(\Zend\Http\Client $http)
    {
        $this->http = $http;
    }

    /**
     * Get the HTTP client.
     *
     * @return \Zend\Http\Client
     */
    public function getHttpClient()
    {
        if ($this->http !== null) {
            return $this->http;
        }

        $this->http = new \Zend\Http\Client();
        $this->http->resetParameters();

        return $this->http;
    }
}
