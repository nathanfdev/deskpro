<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class WidgetLoginListener.
 */
class WidgetLoginListener implements EventSubscriberInterface
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
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $token_storage
     */
    public function __construct(EntityManager $em, TokenStorageInterface $token_storage)
    {
        $this->em            = $em;
        $this->token_storage = $token_storage;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($request->getPathInfo() !== '/focus-win/login') {
            return;
        }

        $session_code = $request->query->get('__sid');
        if (!$session_code) {
            return;
        }

        $token = $this->token_storage->getToken();
        if (!$token) {
            return;
        }

        $user = $token->getUser();
        if (!$user) {
            return;
        }

        /** @var \Application\DeskPRO\EntityRepository\Session $session_repository */
        $session_repository = $this->em->getRepository('DeskPRO:Session');

        $session = $session_repository->getSessionFromCode($session_code);
        if ($session && !$session->getPerson()) {
            $session->setPerson($user);

            $this->em->persist($session);
            $this->em->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse'],
        ];
    }
}
