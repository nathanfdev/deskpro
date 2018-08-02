<?php

namespace DeskPRO\Bundle\ApiBundle\EventListener;

use DeskPRO\Bundle\ApiBundle\Security\Authentication\ApiAuthenticator;
use DeskPRO\Bundle\ApiBundle\Security\Token\ApiKeySecurityToken;
use DeskPRO\Bundle\ApiBundle\Security\Token\ApiTokenSecurityToken;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use Doctrine\ORM\EntityManager;
use Nelmio\CorsBundle\EventListener\CorsListener as NelmioCorsListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
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
    protected $em;

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
        if (!$event->isMasterRequest()) {
            return;
        }
        if ($event->getResponse()->getStatusCode() !== Response::HTTP_UNAUTHORIZED && !$this->isSupportedAuthenticationType()) {
            throw new UnauthorizedHttpException('key,oauth token realm="DeskPRO API"', ErrorsCodes::INVALID_CORS_AUTH_TYPE);
        }

        parent::onKernelResponse($event);
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

        if (!$isOptionsRequest && !$this->isSupportedAuthenticationType()) {
            return;
        }

        parent::forceAccessControlAllowOriginHeader($event);
    }

    /**
     * Enable CORS only for ApiKey or OAuth Token
     * even if authentication failed.
     *
     * We can't just check type of Token from TokenStorage
     * because it can be null in case of failed authentication
     * but we still need to setup CORS to proper show 403 error
     * We need to know authentication attempt type
     *
     * @return bool
     */
    protected function isSupportedAuthenticationType()
    {
        $token = $this->tokenStorage->getToken();

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
     *
     * @return bool
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
        $oauthToken = $this->em->getRepository('AppBundle:OAuthAccessToken')->findOneByApiToken($apiToken);

        return $oauthToken !== null;
    }
}
