<?php

namespace Application\DeskPRO\Proxy;

use Application\DeskPRO\Proxy\Exception\FailedToExchangeRequestTokenException;
use DeskPRO\Bundle\AppBundle\AppSecret\AppSecret;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use Firebase\JWT\JWT;

/**
 * Class OutboundHttpProxy
 *
 * @package Application\DeskPRO\Proxy
 */
class OutboundHttpProxy
{
    /**
     * @var AppSecret
     */
    private $appSecret;

    /**
     * Constructor.
     *
     * @param AppSecret $appSecret
     */
    public function __construct(AppSecret $appSecret)
    {
        $this->appSecret = $appSecret;
    }

    /**
     * Generate a service token for Pusher
     *
     * @param string $siteId
     * @param string $appId
     * @param string $authKey
     * @return string
     * @throws FailedToExchangeRequestTokenException
     */
    public function generatePusherServiceToken($siteId, $appId, $authKey)
    {
        return $this->fetchServiceToken($siteId, 'pusher', [
            'app_id' => (string) $appId,
            'auth_key' => (string) $authKey,
        ]);
    }

    /**
     * Exchanges a "request" token (generated here) for a "service" token used by
     * our outbound proxy services
     *
     * @param string $siteId
     * @param string $service
     * @param array $payload
     * @throws FailedToExchangeRequestTokenException
     */
    protected function fetchServiceToken($siteId, $service, $payload = [])
    {
        global $DP_ENV;

        $tokenExchangeUrl = $DP_ENV->getConfig('env.proxy_token_exchange_url');

        if (empty($tokenExchangeUrl)) {
            throw new FailedToExchangeRequestTokenException(
                'Token exchange URL is not set, in production this should be set via the env var DP_PROXY_TOKEN_EXCHANGE_URL'
            );
        }

        if (empty($siteId)) {
            throw new FailedToExchangeRequestTokenException('Site ID not provided when exchanging request token');
        }

        $payload = array_merge($payload, [
            'sub' => (string) $siteId,
            'srv' => $service,
        ]);

        $requestToken = JWT::encode($payload, $this->appSecret->getAppSecret());

        $client = new HttpClient([
            'base_uri' => $tokenExchangeUrl,
        ]);

        try {
            $response = $client->post('/exchange', [
                'headers' => [
                    'Accept' => 'application/jwt',
                    'Cache-Control' => 'no-cache',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$requestToken,
                ],
            ]);
        } catch (\Exception $e) {
            throw new FailedToExchangeRequestTokenException('Failed to get service token from token exchange', 0, $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw new FailedToExchangeRequestTokenException(sprintf(
                'Request to get service token returned unsuccessful response, [%d] status code returned',
                $response->getStatusCode()
            ));
        }

        return (string) $response->getBody();
    }

    /**
     * @return bool
     */
    public static function isUsingProxy()
    {
        return (defined('DPC_IS_CLOUD') && defined('DPC_IS_USING_OUTBOUND_PROXY'));
    }
}
