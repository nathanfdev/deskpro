<?php

namespace DeskPRO\Bundle\AppBundle\Content;

use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\CommunityTopicSubscription;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketParticipant;
use DeskPRO\Bundle\AppBundle\Security\Permissions\Portal\PortalPermissionsManager;
use Doctrine\ORM\EntityManager;

/**
 * Subscription helper.
 */
class CommunitySubscriptionHelper
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
     * @param CommunityTopic $communityTopic
     * @param Ticket         $ticket
     * @param bool           $isSubscribeOwner
     * @param bool           $isSubscribeParticipants
     */
    public function subscribeTicketPersons(
        CommunityTopic $communityTopic,
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

        $this->subscribePersons($communityTopic, $persons);
    }

    /**
     * @param CommunityTopic $communityTopic
     * @param Person[]       $persons
     */
    public function subscribePersons(CommunityTopic $communityTopic, $persons)
    {
        if (!count($persons)) {
            return;
        }

        $subscribedIds = $this->em->getRepository(CommunityTopicSubscription::class)->getSubscribedPersonIds($communityTopic);

        foreach ($persons as $person) {
            if (in_array($person->getId(), $subscribedIds)) {
                continue;
            }

            // remember newly added person to prevent duplicates
            $subscribedIds[] = $person->getId();

            // @TODO: optimize permission check for each person
            if ($this->portalPermissionsManager
                    ->getPermissionsBagForPerson($person)
                    ->hasContentCategoryAccess($communityTopic)) {
                $communityTopicSubscription = new CommunityTopicSubscription();
                $communityTopicSubscription->setTopic($communityTopic);
                $communityTopicSubscription->setPerson($person);
                $this->em->persist($communityTopicSubscription);
            }
        }

        $this->em->flush();
    }

    /**
     * @param CommunityTopic $communityTopic
     * @param Person         $person
     */
    public function unsubscribePerson(CommunityTopic $communityTopic, Person $person)
    {
        $subscriptions = $this->em->getRepository(CommunityTopicSubscription::class)
            ->findBy([
                'topic'  => $communityTopic,
                'person' => $person,
            ]);

        foreach ($subscriptions as $subscription) {
            $this->em->remove($subscription);
        }

        $this->em->flush();
    }
}
