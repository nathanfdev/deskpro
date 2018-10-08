<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class BrandAccessListener.
 */
class BrandAccessListener implements EventSubscriberInterface
{
    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param BrandStack            $brandStack
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(BrandStack $brandStack, TokenStorageInterface $tokenStorage)
    {
        $this->brandStack   = $brandStack;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -1],
        ];
    }

    /**
     * @internal
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $brand = $this->brandStack->getActive()->getBrand();
        $token = $this->tokenStorage->getToken();

        if (!$token || !$brand) {
            return;
        }

        $person = $token->getUser();
        if (!$person instanceof Person || !$person->getId()) {
            return;
        }

        // user doesn't have access to this brand
        // unset authorised token
        if (!$person->isAdmin() && !$person->hasBrand($brand)) {
            $this->tokenStorage->setToken(new AnonymousToken('anon.', 'anon.'));
        }
    }
}
