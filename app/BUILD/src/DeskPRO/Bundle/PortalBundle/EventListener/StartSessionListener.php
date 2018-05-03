<?php

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * If a user doesnt have a portal session yet, then they are treated as a guest even if they
 * are logged in as an agent. So if we don't have a dpsid cookie but we DO have a dpsid-agent cookie,
 * we need to force-start a session so the system automatically runs the code to transfer the agent session
 * into a portal sesion/.
 */
class StartSessionListener implements EventSubscriberInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * Constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 150], // relatively high priority
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        // TODO this is still flawed, the initial request doesnt seem to
        // cause TransferSessionAuthProvider to execute and actually transfer the agent session

        if (!$request->cookies->has('dpsid-portal')
            && ($request->cookies->has('dpsid-agent') || $request->cookies->has('dpsid-admin'))
        ) {
            if (!$this->session->isStarted()) {
                $this->session->start();
            }
        } elseif (!$request->cookies->get('dpsid-portal')) {
            // if empty session id then remove the cookie to prevent session start w/ empty id
            $request->cookies->remove('dpsid-portal');
        }
    }
}
