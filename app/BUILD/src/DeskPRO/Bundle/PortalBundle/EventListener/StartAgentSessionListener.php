<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

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
class StartAgentSessionListener implements EventSubscriberInterface
{
    /**
     * @var Session
     */
    private $session;

    /**
     * StartAgentSessionListener constructor.
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
        }
    }
}
