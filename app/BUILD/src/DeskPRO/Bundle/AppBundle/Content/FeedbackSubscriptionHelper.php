<?php

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

    /**
     * @param Feedback $feedback
     * @param Person   $person
     */
    public function unsubscribePerson(Feedback $feedback, Person $person)
    {
        $subscriptions = $this->em->getRepository(FeedbackSubscription::class)
            ->findBy([
                'feedback' => $feedback,
                'person'   => $person,
            ]);

        foreach ($subscriptions as $subscription) {
            $this->em->remove($subscription);
        }

        $this->em->flush();
    }
}
