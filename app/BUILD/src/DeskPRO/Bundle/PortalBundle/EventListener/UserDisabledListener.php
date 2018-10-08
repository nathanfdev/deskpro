<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\People\PersonGuest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * If a user is logged-in but is disabled, show error page.
 */
class UserDisabledListener implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', -1],
        ];
    }

    /**
     * @internal
     *
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (!$this->tokenStorage->getToken()) {
            return;
        }

        $request = $event->getRequest();

        $person = $this->tokenStorage->getToken()->getUser();
        if ($person && $person instanceof Person && !$person instanceof PersonGuest && $person->isDisabled()) {
            if (!preg_match('#^(/.{2,4})?/profile/disabled#', $request->getPathInfo())) {
                $event->setResponse(new RedirectResponse($request->getUriForPath('/profile/disabled')));
                $event->stopPropagation();
            }
        }
    }
}
