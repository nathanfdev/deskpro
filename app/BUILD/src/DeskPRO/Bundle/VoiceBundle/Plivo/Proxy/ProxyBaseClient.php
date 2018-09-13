<?php

namespace DeskPRO\Bundle\VoiceBundle\Plivo\Proxy;

use Plivo\Authentication\BasicAuth;
use Plivo\BaseClient;
use Plivo\Http\PlivoRequest;
use Plivo\HttpClients\PlivoGuzzleHttpClient;

/**
 * Class ProxyBaseClient.
 */
class ProxyBaseClient extends BaseClient
{
    /**
     * @var string
     */
    protected $proxyHost;

    /**
     * Constructor.
     *
     * @param string $authId
     * @param string $authToken
     * @param string $proxyHost
     */
    public function __construct($authId, $authToken, $proxyHost)
    {
        $this->basicAuth         = new BasicAuth($authId, $authToken);
        $this->proxyHost         = $proxyHost;
        $this->httpClientHandler = new PlivoGuzzleHttpClient(null, $this->basicAuth);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareRequestMessage(PlivoRequest $request)
    {
        $url = ($this->proxyHost ?: self::BASE_API_URL).$request->getUrl();

        $requestBody = json_encode($request->getParams(), JSON_FORCE_OBJECT);

        return [
            $url,
            $request->getMethod(),
            $request->getHeaders(),
            $requestBody,
        ];
    }
}
