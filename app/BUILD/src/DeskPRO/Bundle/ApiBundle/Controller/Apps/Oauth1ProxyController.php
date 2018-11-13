<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Entity\AppStore\AppInstance;

use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\OauthErrorCodes;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\OauthException;
use DeskPRO\Bundle\AppStoreBundle\Oauth1\AuthorizationSession;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\OauthProviderConnectionLoader;
use DeskPRO\Bundle\AppStoreBundle\Infrastructure\Security\SerializedOauth1Connection;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HttpProxyController.
 *
 * @ApiModes("session")
 * @ApiUserContext("agent")
 * @Rest\Route("/apps/proxy-oauth/1.0")
 */
class Oauth1ProxyController extends BaseController
{
    private $oauthSessionCookieName = 'dp_oauth1_sess';

    private $tokenCallbackCookieName = 'dp_oauth1_token_callback';

    private $authSessionDuration = 300; //in seconds


    private function readTokenCallbackCookie(Request $request)
    {
        $tokenCallback = $request->cookies->get($this->tokenCallbackCookieName, null);
        return $tokenCallback;
    }

    /**
     * @param Request $request
     *
     * @return AuthorizationSession|null
     */
    private function readAuthSessionCookie(Request $request)
    {
        $serialized = $request->cookies->get($this->oauthSessionCookieName, null);
        if (is_string($serialized)) {
            return AuthorizationSession::unserialize(base64_decode($serialized));
        }

        return null;
    }

    /**
     * @param Response $response
     * @param string $callbackUrl
     * @return Cookie
     *
     */
    private function writeTokenCallbackCookie(Response $response, $callbackUrl = null)
    {
        if (is_null($callbackUrl)) {
            $sessionCookie = new Cookie($this->oauthSessionCookieName, '');
        } else {
            $expirationTime = time() + $this->authSessionDuration;
            $sessionCookie  = new Cookie($this->tokenCallbackCookieName, $callbackUrl, $expirationTime);
        }

        $response->headers->setCookie($sessionCookie);
    }

    /**
     * @param Response             $response
     * @param AuthorizationSession $session
     *
     * @return Cookie
     */
    private function writeAuthSessionCookie(Response $response, AuthorizationSession $session = null)
    {
        $sessionCookie = null;
        if (is_null($session)) {
            $sessionCookie = new Cookie($this->oauthSessionCookieName, '');
        } else {
            $expirationTime = time() + $this->authSessionDuration;
            $sessionCookie  = new Cookie(
                $this->oauthSessionCookieName,
                base64_encode(AuthorizationSession::serialize($session)),
                $expirationTime
            );
        }
        $response->headers->setCookie($sessionCookie);

        return $sessionCookie;
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
        $callbackMethod = ProxyParams::getDPQueryParam('callbackMethod', $request, 'postMessage');
        $callbackUrl    = ProxyParams::getDPQueryParam('callbackUrl', $request, 'postMessage');
        if ($callbackMethod !== 'postMessage' || empty($callbackUrl)) {
            return new Response('Invalid callback method', 400);
        }

        // check that we have an application
        $applicationId = ProxyParams::getDPQueryParam('applicationId', $request);
        if (is_null($applicationId)) {
            return new Response('Connection not found', 400);
        }

        $errorResponseBuilder = OauthResponseBuilder::forResponseType('error');
        $appState = ProxyParams::getDPQueryParam('state', $request);
        if (!is_null($appState)) {
            $errorResponseBuilder->withApplicationState($appState);
        }

        $clientProfile = ProxyParams::getDPQueryParam('client_profile', $request, 'web-server'); //web-server, user-agent, [ native applications, autonomous clients ]
        if (!in_array($clientProfile, ['web-server', 'user-agent'])) {
            return $errorResponseBuilder
                ->withError(OauthErrorCodes::CODE_BAD_REQUEST, 'Unknown client profile')
                ->buildPostMessage($callbackUrl)
            ;
        }

        /** @var SerializedOauth1Connection $connection */
        $connection = null;
        if ($clientProfile === 'web-server') {
            if (is_null($provider)) {
                return $errorResponseBuilder
                    ->withError(OauthErrorCodes::CODE_PROVIDER_NOT_FOUND)
                    ->buildPostMessage($callbackUrl);
            }

            $connection = $provider->loadOauth1Connection($applicationId, $this->getUser());
            if (empty($connection)) {
                return $errorResponseBuilder
                    ->withError(OauthErrorCodes::CODE_PROVIDER_NOT_FOUND)
                    ->buildPostMessage($callbackUrl)
                ;
            }
        }

        if ($clientProfile === 'web-server') {
            try {
                $authSession      = new AuthorizationSession();
                $authorizationUrl = $connection->getAuthorizationUrl($authSession);

                $response = new RedirectResponse($authorizationUrl);
                $this->writeAuthSessionCookie($response, $authSession);
                $this->writeTokenCallbackCookie($response, $callbackUrl);

                return $response;
            } catch (OAuthException $e) {
                return $errorResponseBuilder->withOauthException($e)->buildPostMessage($callbackUrl);
            }
        }

        return $errorResponseBuilder->withError(OauthErrorCodes::CODE_BAD_REQUEST,'only web-server profile allowed')->buildPostMessage($callbackUrl);
    }

    /**
     * @param AppInstance|null                   $application
     * @param OauthProviderConnectionLoader|null $provider
     * @param Request                            $request
     *
     * @return Response
     */
    private function handleGrantAccessAction(AppInstance $application = null, OauthProviderConnectionLoader $provider = null, Request $request)
    {
        if (is_null($application) || empty($provider)) {
            return new Response('Connection not found', 404);
        }

        $callbackUrl = $this->readTokenCallbackCookie($request);
        if (is_null($callbackUrl)) {
            return new Response('Bad request. Missing callback url', 400);
        }

        // prepare the error response builder
        $errorResponseBuilder = OauthResponseBuilder::forResponseType('error', '1.0');

        $oauthToken    = $request->query->get('oauth_token', null);
        $oauthVerifier = $request->query->get('oauth_verifier', null);

        // was there an error ?
        if (empty($oauthVerifier) || empty($oauthToken)) {
            return $errorResponseBuilder->withError(OauthErrorCodes::CODE_GENERIC_FAILURE)->buildPostMessage($callbackUrl);
        }

        $connection = $provider->loadOauth1Connection($application, $this->getUser());
        if (empty($connection)) {
            return $errorResponseBuilder->withError(OauthErrorCodes::CODE_CONNECTION_NOT_FOUND)->buildPostMessage($callbackUrl);
        }

        try {
            $authSession = $this->readAuthSessionCookie($request);
            if (is_null($authSession)) {
                return $errorResponseBuilder
                    ->withError(OauthErrorCodes::CODE_GENERIC_FAILURE,'failed to retrieve token')
                    ->buildPostMessage($callbackUrl)
                ;
            }

            $token = $connection->getAccessToken($authSession, $oauthToken, $oauthVerifier);

            return OauthResponseBuilder::forResponseType('token', '1.0')
                ->withTokenParams($token->jsonSerialize())
                ->buildPostMessage($callbackUrl);
        }  catch (OAuthException $e) {
            return $errorResponseBuilder->withOauthException($e)->buildPostMessage($callbackUrl);
        } catch (\Exception $e) {
            return $errorResponseBuilder
                ->withError(OauthErrorCodes::CODE_GENERIC_FAILURE,'failed to retrieve token')
                ->buildPostMessage($callbackUrl)
            ;
        }
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
        $response = $this->handleGrantAccessAction($application, $provider, $request);

        if ($response instanceof Response) { // cleanup any authorization session cookies
            $this->writeAuthSessionCookie($response);
            $this->writeTokenCallbackCookie($response);
        }

        return $response;
    }
}
