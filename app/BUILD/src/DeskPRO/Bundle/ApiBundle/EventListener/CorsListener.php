<?php

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use DeskPRO\Bundle\ApiBundle\Security\Authentication\ApiAuthenticator;
use DeskPRO\Bundle\ApiBundle\Security\Token\ApiKeySecurityToken;
use DeskPRO\Bundle\ApiBundle\Security\Token\ApiTokenSecurityToken;
use Doctrine\ORM\EntityManager;
use Nelmio\CorsBundle\EventListener\CorsListener as NelmioCorsListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Overwrite Nelmio CorsListener and enable CORS only for requests with ApiKey or with OauthToken.
 */
class CorsListener extends NelmioCorsListener
{
    /**
     * @var ApiAuthenticator
     */
    protected $apiAuthenticator;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param ApiAuthenticator $apiAuthenticator
     */
    public function setApiAuthenticator(ApiAuthenticator $apiAuthenticator)
    {
        $this->apiAuthenticator = $apiAuthenticator;
    }

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Enable CORS only for requests with ApiKey or with OauthToken.
     *
     * {@inheritdoc}
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        if (!$this->isSupportedAuthenticationType($event->getRequest())) {
            return;
        }

        return parent::onKernelResponse($event);
    }

    /**
     * Enable CORS only for requests with ApiKey or with OauthToken.
     *
     * {@inheritdoc}
     */
    public function forceAccessControlAllowOriginHeader(FilterResponseEvent $event)
    {
        // 'OPTIONS' preflight requests not authenticated, we can't detect auth type
        // enabled CORS for such requests
        // https://stackoverflow.com/questions/13994507/how-do-you-send-a-custom-header-in-a-cross-domain-cors-xmlhttprequest
        $isOptionsRequest = $event->getRequest()->getMethod() === 'OPTIONS';

        if (!$isOptionsRequest && !$this->isSupportedAuthenticationType($event->getRequest())) {
            return;
        }

        return parent::forceAccessControlAllowOriginHeader($event);
    }

    /**
     * Enable CORS only for ApiKey or OAuth Token
     * even if authentication failed.
     *
     * We can't just check type of Token from TokenStorage
     * because it can be null in case of failed authentication
     * but we still need to setup CORS to proper show 403 error
     * We need to know authentication attempt type
     */
    protected function isSupportedAuthenticationType(Request $request)
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            // no token means:
            // 1) this check called during `kernel.request` event and authentication was not executed yet
            // 2) this check called during `kernel.response` event and authentication failed
            // in any case try to create fake preauthenticated token to get it type (this token is not saved anywhere)
            try {
                $token = $this->apiAuthenticator->createToken($request, 'some_fake');
            } catch (\Exception $e) {
                // exception means - no authentication at all
            }
        }

        if ($token instanceof ApiKeySecurityToken) {
            return true;
        }

        if ($token instanceof ApiTokenSecurityToken && $this->isOauthToken($token->getCredentials())) {
            return true;
        }

        return false;
    }

    /**
     * @param string $tokenString
     */
    protected function isOauthToken($tokenString)
    {
        if (!$this->tokenStorage->getToken()) {
            // if there is no token in tokenStorage - means authentication failed or not executed
            // we don't know for sure was this attempt with oauth token or with regular token
            // enable cors for all tokens in this case
            return true;
        }

        $apiToken = $this->em->getRepository('DeskPRO:ApiToken')->findByTokenString($tokenString);
        if (!$apiToken) {
            return false;
        }

        // If token was created through the OAuth flow
        // then there is a connected oauth token exist
        $oauthToken = $this->em->getRepository('AppBundle:OAuthAccessToken')->findByApiToken($apiToken);

        return $oauthToken != false;
    }
}
