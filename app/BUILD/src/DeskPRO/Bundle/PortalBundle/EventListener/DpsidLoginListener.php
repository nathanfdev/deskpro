<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\EventListener;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\AppBundle\Helper\IsProxyRequestHelper;
use DeskPRO\Bundle\AppBundle\Request\RequestUtils;
use DeskPRO\Bundle\PortalBundle\Mode\PortalModeStorage;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class DpsidLoginListener.
 */
class DpsidLoginListener implements EventSubscriberInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var TokenStorageInterface
     */
    private $token_storage;

    /**
     * @var PortalModeStorage
     */
    private $portal_mode_storage;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $token_storage
     * @param PortalModeStorage     $portal_mode_storage
     */
    public function __construct(EntityManager $em, TokenStorageInterface $token_storage, PortalModeStorage $portal_mode_storage)
    {
        $this->em                  = $em;
        $this->token_storage       = $token_storage;
        $this->portal_mode_storage = $portal_mode_storage;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest'],
        ];
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest() || IsProxyRequestHelper::check($event->getRequest())) {
            // don't set a portal mode for sub requests
            // also, don't set a portal mode for master requests that are a proxy (ESI)
            return;
        }
        if (RequestUtils::isLowRequest($event->getRequest())) {
            // don't run on low level
            return;
        }
        if (!$this->portal_mode_storage) {
            return;
        }

        $portal_mode = $this->portal_mode_storage->getMode();
        if (!$portal_mode || !$portal_mode->isFocusWindow()) {
            return;
        }

        $request = $event->getRequest();

        // Trying to get external auth code from the request
        $session_code = $request->query->get('dpsid');
        if ($session_code) {
            // Save auth code in the current session
            $request->getSession()->set('widget_sid', $session_code);
        } else {
            // If user logged in, try to get external auth code from the current session
            $session_code = $request->getSession()->get('widget_sid');
        }

        if (!$session_code) {
            return;
        }

        $token = $this->token_storage->getToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();
        if ($user instanceof Person) {
            // Success auth, remove the external auth code
            $request->getSession()->remove('widget_sid');

            // Modify external session entity and set the current session's person
            /** @var \Application\DeskPRO\EntityRepository\Session $repository */
            $repository = $this->em->getRepository(Session::class);
            $session    = $repository->getSessionFromCode($session_code);

            if (!$session || $session->getPerson()) {
                return;
            }

            $session->setPerson($user);

            $this->em->persist($session);
            $this->em->flush();
        }
    }
}
