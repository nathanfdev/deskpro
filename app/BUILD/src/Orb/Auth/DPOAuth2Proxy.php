<?php

namespace Orb\Auth;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\Request;
use Firebase\JWT\JWT;

class DPOAuth2Proxy
{
    private $oauth2ProxyHost;

    private $jwtSecret;

    private $useHttps;

    /**
     * @param DeskproContainer $container
     * @return DPOAuth2Proxy
     */
    public static function fromContainer(DeskproContainer $container)
    {
        $host = $container->getSettingsResolver()->getGlobalSettings()->get('dpoauth2proxy.host', null);
        $useHttps = $container->getSettingsResolver()->getGlobalSettings()->get('dpoauth2proxy.use_https', true);
        $secret = $container->getSettingsResolver()->getGlobalSettings()->get('core.app_secret', null);

        return new DPOAuth2Proxy($host, $secret, $useHttps);
    }

    /**
     * DPOAuth2ProxyClient constructor.
     * @param string $oauth2ProxyHost
     * @param string $jwtSecret
     * @param bool $useHttps
     */
    public function __construct( $oauth2ProxyHost, $jwtSecret, $useHttps )
    {
        $this->oauth2ProxyHost = $oauth2ProxyHost;
        $this->jwtSecret = $jwtSecret;
        $this->useHttps = $useHttps;
    }

    /**
     * @param string $account
     * @param string $provider
     * @return string
     */
    public function buildEntrypointURL($account, $provider)
    {
        return sprintf(
            "%s://%s/%s/%s/oauth2/start",
            $this->useHttps ? 'https' : 'http',
            $this->oauth2ProxyHost,
            $account,
            $provider
        );
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function authenticateRequest(Request $request)
    {
        $email = $request->query->get('email');
        $user = $request->query->get('user');
        $signature = $request->query->get('signature');

        $httpClient = new HttpClient([
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::VERIFY  => false,
            RequestOptions::HEADERS => [
                'X-Forwarded-Email' => $email,
                'X-Forwarded-User' => $user,
                'X-Signature' => $signature
            ],
        ]);

        $url = sprintf("%s://%s/verify", $this->useHttps ? 'https' : 'http', $this->oauth2ProxyHost);
        $response = $httpClient->get($url);
        if ($response->getStatusCode() === 200 ) {
            return true;
        }
        return false;
    }

    /**
     * Extracts the information about the authenticated subject and builds a JWT token that can be used for sign-in to Deskpro
     *
     * @param Request $downstream
     * @return array
     */
    public function encodeToken(Request $downstream)
    {
        $timestamp = time();
        $data = [
            "email" =>  $downstream->query->get('email'),
            "user" =>   $downstream->query->get('user'),
            "iat" => $timestamp,
            "exp" => $timestamp + 60 * 5 // expire in 5 minutes
        ];

        return [
            'token' => JWT::encode($data, 'oauth:'.$this->jwtSecret, 'HS512')
        ];
    }

    /**
     * Decodes the authentication token used to sign-in to Deskpro
     *
     * @param array $params
     * @return array|null
     */
    public function decodeToken(array $params)
    {
        JWT::$leeway = 60 * 0; // no leeway around token expiration
        if (! array_key_exists('token', $params)) {
            return null;
        }
        $encodedToken = $params['token'];
        try {
            $token = (array) JWT::decode($encodedToken, 'oauth:'.$this->jwtSecret, ['HS512']);
            if (empty($token)) {
                return null;
            }

            return $token;
        } catch (\UnexpectedValueException $e) {
            return null;
        }
    }
}
