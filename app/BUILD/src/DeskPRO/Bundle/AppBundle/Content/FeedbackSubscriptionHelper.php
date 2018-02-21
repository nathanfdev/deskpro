<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Content;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackSubscription;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketParticipant;
use DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager;
use Doctrine\ORM\EntityManager;

/**
 * Subscription helper.
 */
class FeedbackSubscriptionHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var PortalPermissionsManager
     */
    private $portalPermissionsManager;

    /**
     * @param EntityManager            $em
     * @param PortalPermissionsManager $portalPermissionsManager
     */
    public function __construct(
        EntityManager $em,
        PortalPermissionsManager $portalPermissionsManager)
    {
        $this->em                       = $em;
        $this->portalPermissionsManager = $portalPermissionsManager;
    }

    /**
     * @param Feedback $feedback
     * @param Ticket   $ticket
     * @param bool     $isSubscribeOwner
     * @param bool     $isSubscribeParticipants
     */
    public function subscribeTicketPersons(
        Feedback $feedback,
        Ticket $ticket,
        $isSubscribeOwner,
        $isSubscribeParticipants)
    {
        $persons = [];

        if ($isSubscribeOwner) {
            $persons[] = $ticket->getPerson();
        }

        if ($isSubscribeParticipants) {
            // need only Person Id's to create subscription's
            // so, we don't need some extra joins and can use this magic func
            $participants = $this->em->getRepository(TicketParticipant::class)->findByTicket($ticket);

            $persons = array_merge(
                $persons,
                array_map(function ($p) {
                    return $p->getPerson();
                }, $participants)
            );
        }

        $this->subscribePersons($feedback, $persons);
    }

    /**
     * @param Feedback $feedback
     * @param Person[] $persons
     */
    public function subscribePersons(Feedback $feedback, $persons)
    {
        if (!count($persons)) {
            return;
        }

        $subscribedIds = $this->em->getRepository(FeedbackSubscription::class)->getSubscribedPersonIds($feedback);

        foreach ($persons as $person) {
            if (in_array($person->getId(), $subscribedIds)) {
                continue;
            }

            // remember newly added person to prevent duplicates
            $subscribedIds[] = $person->getId();

            // @TODO: optimize permission check for each person
            if ($this->portalPermissionsManager
                    ->getPermissionsBagForPerson($person)
                    ->hasContentCategoryAccess($feedback)) {
                $feedbackSubscription = new FeedbackSubscription();
                $feedbackSubscription->setFeedback($feedback);
                $feedbackSubscription->setPerson($person);
                $this->em->persist($feedbackSubscription);
            }
        }

        $this->em->flush();
    }
}
