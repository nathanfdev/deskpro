<?php

namespace DeskPRO\Bundle\PortalBundle\HttpCache;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PortalCacheHelper
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var null|bool used to only make guest decision once per master request
     */
    private $isGuest;

    /**
     * @param RequestStack          $requestStack
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(RequestStack $requestStack, TokenStorageInterface $tokenStorage)
    {
        $this->requestStack = $requestStack;
        $this->tokenStorage = $tokenStorage;
        $this->isGuest      = null;
    }

    /**
     * Is the master request a guest request?
     *
     * NOTE: if the portal http cache is disabled, this will always return false
     *
     * @return bool true if in a guest request
     */
    public function isGuestRequest()
    {
        // only make the decision once per php run
        if (null !== $this->isGuest) {
            return $this->isGuest;
        }

        return $this->isGuest = $this->determineIfGuestRequest();
    }

    /**
     * @return null|string
     */
    public function getUserContextHash()
    {
        $currentRequest = $this->requestStack->getMasterRequest();

        if (!$currentRequest->headers->has(PortalHttpCache::USER_CONTEXT_HASH_HEADER)) {
            return;
        }

        return $currentRequest->headers->get(PortalHttpCache::USER_CONTEXT_HASH_HEADER);
    }

    /**
     * Is the master request a guest request, or not?
     *
     * @return bool true if guest
     */
    private function determineIfGuestRequest()
    {
        if (!$userHash = $this->getUserContextHash()) {
            // portal cache is disabled. In that case treat nobody as a guest.
            return false;
        }

        if ($token = $this->tokenStorage->getToken()) {
            if ($token->getUser() instanceof UserInterface) {
                return false;
            }
        }

        return $this->isGuestHash($userHash);
    }

    public function isGuestHash($hash)
    {
        return in_array($hash, [PortalHttpCache::ANON_NO_SESSION_HASH]);
    }
}
