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
     * @var string
     */
    protected $proxyUsername;

    /**
     * @var string
     */
    protected $proxyPassword;

    /**
     * Constructor.
     *
     * @param string $authId
     * @param string $authToken
     * @param string $proxyHost
     * @param string $proxyUsername
     * @param string $proxyPassword
     */
    public function __construct($authId, $authToken, $proxyHost, $proxyUsername, $proxyPassword)
    {
        if ($proxyUsername) {
            $this->basicAuth = new BasicAuth($proxyUsername, $proxyPassword);
        } else {
            $this->basicAuth = new BasicAuth($authId, $authToken);
        }

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
