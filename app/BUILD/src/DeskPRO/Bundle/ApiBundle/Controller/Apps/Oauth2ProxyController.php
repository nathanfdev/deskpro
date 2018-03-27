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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
        // check that we have an application
        $applicationId = $request->query->get('applicationId', null);
        if (is_null($applicationId)) {
            return new Response('Connection not found', 400);
        }

        // check that we can post back messages. if we can not then, we show html errors
        $callbackMethod = $request->query->get('callbackMethod', 'postMessage');
        $callbackUrl    = $request->query->get('callbackUrl');
        if ($callbackMethod !== 'postMessage' || empty($callbackUrl)) {
            return new Response('Invalid callback method', 400);
        }

        $errorResponseBuilder = OauthResponseBuilder::forResponseType('error')
            ->withApplicationState($request->query->get('state', null))
            ->withRedirectUrl($callbackUrl)
        ;

        $clientProfile = $request->query->get('client_profile', 'web-server'); //web-server, user-agent, [ native applications, autonomous clients ]
        if (!in_array($clientProfile, ['web-server', 'user-agent'])) {
            return $errorResponseBuilder->withErrorType('invalid client profile')->buildPostMessage();
        }

        /** @var SerializedOauth2Connection $connection */
        $connection = null;
        if ($clientProfile === 'web-server') {
            if (is_null($provider)) {
                return $errorResponseBuilder->withErrorType('provider not found')->buildPostMessage();
            }

            $connection = $provider->loadOauth2Connection($applicationId, $this->getUser());
            if (empty($connection)) {
                return $errorResponseBuilder->withErrorType('connection not found')->buildPostMessage();
            }
        }

        if ($clientProfile === 'web-server') {
            $proxyState = [
                'appState'       => $request->query->get('state', null),
                'callbackMethod' => $callbackMethod,
                'callbackUrl'    => $request->query->get('callbackUrl'),
            ];

            $secret = $this->readJWTSecret($this->getContainer());
            $state  = self::encode($proxyState, $secret);
            if (empty($state)) {
                return $errorResponseBuilder->withErrorType('failed to secure the request')->buildPostMessage();
            }

            $authorizationUrl = $connection->getAuthorizationUrl(['state' => $state]);

            return new RedirectResponse($authorizationUrl);
        }

        return $errorResponseBuilder->withErrorType('only web-server profile allowed')->buildPostMessage();
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

        // prepare the error response builder
        $errorResponseBuilder = OauthResponseBuilder::forResponseType('error')
            ->withApplicationState($proxyState['appState'])
            ->withRedirectUrl($proxyState['callbackUrl'])
        ;

        $responseType = $request->query->get('response_type', 'code'); // === 'code'
        if (!in_array($responseType, ['code', 'error'])) {
            return $errorResponseBuilder->withErrorType('unexpected response type')->buildPostMessage();
        }

        if ($responseType === 'code') {
            $connection = $provider->loadOauth2Connection($application, $this->getUser());
            if (empty($connection)) {
                return $errorResponseBuilder->withErrorType('connection not found')->buildPostMessage();
            }

            try {
                $code  = $request->query->get('code');
                $token = $connection->getAccessToken('authorization_code', ['code' => $code]);

                return OauthResponseBuilder::forResponseType('token')
                    ->withApplicationState($proxyState['appState'])
                    ->withOauth2Token($token)
                    ->withRedirectUrl($proxyState['callbackUrl'])
                    ->buildPostMessage();
            } catch (\Exception $e) {
                return $errorResponseBuilder->withErrorType('failed to retrieve token')->buildPostMessage();
            }
        }

        // when $responseType === 'error'
        return $errorResponseBuilder->withErrorType('oauth error')->buildPostMessage();
    }
}
