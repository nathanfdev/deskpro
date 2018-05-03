<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\OauthProviderConnectionLoader;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\SerializedOauth2Connection;
use Firebase\JWT\JWT;
use FOS\RestBundle\Controller\Annotations as Rest;
use League\OAuth2\Client\Token\AccessToken;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HttpProxyController.
 *
 * @ApiModes("session")
 * @ApiUserContext("agent")
 * @Rest\Route("/apps/proxy-oauth")
 */
class Oauth2ProxyController extends BaseController
{
    /**
     * @param array $state
     * @param $secret
     *
     * @return null|string
     */
    public static function encode($state, $secret)
    {
        if (empty($secret)) {
            return null;
        }

        $token = JWT::encode($state, 'oauth:'.$secret, 'HS512');

        return $token;
    }

    /**
     * @param string $token
     * @param string $secret
     *
     * @return array|null
     */
    public static function decode($token, $secret)
    {
        if (empty($secret)) {
            return null;
        }

        try {
            $state = (array) JWT::decode($token, 'oauth:'.$secret, ['HS512']);
            if (empty($state)) {
                return null;
            }

            return $state;
        } catch (\UnexpectedValueException $e) {
            return null;
        }
    }

    /**
     * @param DeskproContainer $container
     *
     * @return null|string
     */
    private function readJWTSecret(DeskproContainer $container)
    {
        $secret = $container->getSettingsResolver()->getGlobalSettings()->get('core.app_secret', null);
        if (empty($secret)) {
            return null;
        }

        return $secret;
    }

    /**
     * @ParamConverter("provider", class="AppStoreBundle:Infrastructure\Security\OauthProviderConnectionLoader", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\OauthProviderConnectionLoaderConverter")
     *
     * @Rest\Get("/{provider}/authorize")
     *
     * @param OauthProviderConnectionLoader|null $provider
     * @param Request                            $request
     *
     * @return RedirectResponse|Response
     */
    public function authorizeAction(OauthProviderConnectionLoader $provider = null, Request $request)
    {
        // check that we can post back messages. if we can not then, we show html errors
        $callbackMethod = ProxyParams::getDPQueryParam('callbackMethod', $request);
        $callbackUrl    = ProxyParams::getDPQueryParam('callbackUrl', $request);

        if ($callbackMethod !== 'postMessage' || empty($callbackUrl)) {
            return new Response('Invalid callback method', 400);
        }

        // check that we have an application
        $applicationId = ProxyParams::getDPQueryParam('applicationId', $request);
        if (is_null($applicationId)) {
            return new Response('Connection not found', 400);
        }

        $state = ProxyParams::getDPQueryParam('state', $request);
        $errorResponseBuilder = OauthResponseBuilder::forResponseType('error')
            ->withApplicationState($state)
        ;

        $clientProfile = ProxyParams::getDPQueryParam('client_profile', $request, 'web-server'); //web-server, user-agent, [ native applications, autonomous clients ]
        if (!in_array($clientProfile, ['web-server', 'user-agent'])) {
            return $errorResponseBuilder->withErrorType('invalid client profile')->buildPostMessage($callbackUrl);
        }

        /** @var SerializedOauth2Connection $connection */
        $connection = null;
        if ($clientProfile === 'web-server') {
            if (is_null($provider)) {
                return $errorResponseBuilder->withErrorType('provider not found')->buildPostMessage($callbackUrl);
            }

            $connection = $provider->loadOauth2Connection($applicationId, $this->getUser());
            if (empty($connection)) {
                return $errorResponseBuilder->withErrorType('connection not found')->buildPostMessage($callbackUrl);
            }
        }

        if ($clientProfile === 'web-server') {
            $proxyState = ProxyParams::qualifyAllDpParams([
                'appState'       => ProxyParams::getDPQueryParam('state', $request),
                'callbackMethod' => $callbackMethod,
                'callbackUrl'    => $callbackUrl,
            ]);

            $secret = $this->readJWTSecret($this->getContainer());
            $state  = self::encode($proxyState, $secret);
            if (empty($state)) {
                return $errorResponseBuilder->withErrorType('failed to secure the request')->buildPostMessage($callbackUrl);
            }

            $extraQueryParams = ProxyParams::getExtraQueryParams($request);
            $authorizationUrl = $connection->getAuthorizationUrl(array_merge($extraQueryParams, ['state' => $state]));

            return new RedirectResponse($authorizationUrl);
        }

        return $errorResponseBuilder->withErrorType('only web-server profile allowed')->buildPostMessage($callbackUrl);
    }

    /**
     * @ParamConverter("application", class="AppBundle:Entity\AppStore\AppInstance", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\AppInstanceParamConverter")
     * @ParamConverter("provider", class="AppStoreBundle:Infrastructure\Security\OauthProviderConnectionLoader", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\OauthProviderConnectionLoaderConverter")
     *
     * @Rest\Get("/{provider}/grant-access/{application}")
     *
     * @param AppInstance                        $application
     * @param OauthProviderConnectionLoader|null $provider
     * @param Request                            $request
     *
     * @return Response
     */
    public function grantAccessAction(AppInstance $application = null, OauthProviderConnectionLoader $provider = null, Request $request)
    {
        if (is_null($application) || empty($provider)) {
            return new Response('Connection not found', 404);
        }

        // let's try and decode the state first so we can postMessage back an error
        $state      = $request->query->get('state', null);
        $secret     = self::readJWTSecret($this->getContainer());
        $proxyState = self::decode($state, $secret);
        if (empty($proxyState)) {
            return new Response('Invalid oauth request', 400);
        }

        $callbackUrl = ProxyParams::getDPQueryParamFromArray('callbackUrl', $proxyState);
        $appState = ProxyParams::getDPQueryParamFromArray('appState', $proxyState);
        // prepare the error response builder
        $errorResponseBuilder = OauthResponseBuilder::forResponseType('error')->withApplicationState($appState);

        $responseType = $request->query->get('response_type', 'code'); // === 'code'
        if (!in_array($responseType, ['code', 'error'])) {
            return $errorResponseBuilder->withErrorType('unexpected response type')->buildPostMessage($callbackUrl);
        }

        if ($responseType === 'code') {
            $connection = $provider->loadOauth2Connection($application, $this->getUser());
            if (empty($connection)) {
                return $errorResponseBuilder->withErrorType('connection not found')->buildPostMessage($callbackUrl);
            }

            try {
                $code  = $request->query->get('code');
                $token = $connection->getAccessTokenWithAuthorizationCode($code);

                return OauthResponseBuilder::forResponseType('token')
                    ->withApplicationState($appState)
                    ->withOauth2Token($token)
                    ->buildPostMessage($callbackUrl);
            } catch (\Exception $e) {
                return $errorResponseBuilder->withErrorType('failed to retrieve token')->buildPostMessage($callbackUrl);
            }
        }

        // when $responseType === 'error'
        return $errorResponseBuilder->withErrorType('oauth error')->buildPostMessage($callbackUrl);
    }

    /**
     * @ParamConverter("provider", class="AppStoreBundle:Infrastructure\Security\OauthProviderConnectionLoader", converter="DeskPRO\Bundle\AppStoreBundle\ParamConverter\OauthProviderConnectionLoaderConverter")
     *
     * @Rest\Get("/{provider}/refresh-access")
     *
     * @param OauthProviderConnectionLoader|null $provider
     * @param Request                            $request
     *
     * @return JsonResponse
     */
    public function refreshAccessAction(OauthProviderConnectionLoader $provider = null, Request $request)
    {
        // prepare the error response builder
        $errorResponseBuilder = OauthResponseBuilder::forResponseType('error');

        // check that we have an application
        $applicationId = ProxyParams::getDPQueryParam('applicationId', $request);
        if (is_null($applicationId)) {
            return $errorResponseBuilder->withErrorType('Connection not found')->buildJSON();
        }

        // check that we can post back messages. if we can not then, we show html errors
        $refreshToken = $request->query->get('refresh_token', null);
        if (empty($refreshToken)) {
            return $errorResponseBuilder->withErrorType('Missing refresh token')->buildJSON();
        }

        $person = $this->getUser();
        $connection = $provider->loadOauth2Connection($applicationId, $person);
        if (empty($connection)) {
            return $errorResponseBuilder->withErrorType('connection not found')->buildJSON();
        }

        $extraQueryParams = ProxyParams::getExtraQueryParams($request);
        $tokens = $provider->loadOauth2Tokens($applicationId, $person, $refreshToken);
        $newAccessToken = $connection->getAccessTokenWithRefreshToken($tokens->getRefreshToken(), $extraQueryParams);

        // we're merging the tokens to preserve the information requested with the original token, such as
        // the refresh token which is not always returned with the refresh token response and since the api clients
        // are just replacing the old token with the new one that information will be lost
        $tokenValues = array_merge($tokens->jsonSerialize(), $newAccessToken->jsonSerialize());
        $finalToken = new AccessToken($tokenValues);

        return OauthResponseBuilder::forResponseType('token')
            ->withOauth2Token($finalToken)
            ->buildJSON()
        ;
    }

}
