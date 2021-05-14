<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use DeskPRO\Bundle\AppBundle\Entity\TicketStatus;
use Doctrine\Common\Persistence\ObjectManager;

class TicketStatusesFixture extends AbstractDpFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $deleted = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $deleted->setSysId('deleted');
        $deleted->setTitle('Deleted');
        $manager->persist($deleted);

        $spam = new TicketStatus(TicketStatus::STATUS_TYPE_HIDDEN);
        $spam->setSysId('spam');
        $spam->setTitle('Spam');
        $manager->persist($spam);

        $awaitingAgent = new TicketStatus(TicketStatus::STATUS_TYPE_AWAITING_AGENT);
        $awaitingAgent->setSysId('awaiting_agent');
        $awaitingAgent->setTitle('awaiting_agent');
        $manager->persist($awaitingAgent);

        $awaitingUser = new TicketStatus(TicketStatus::STATUS_TYPE_AWAITING_USER);
        $awaitingUser->setSysId('awaiting_user');
        $awaitingUser->setTitle('awaiting_user');
        $manager->persist($awaitingUser);

        $pending = new TicketStatus(TicketStatus::STATUS_TYPE_PENDING);
        $pending->setSysId('pending');
        $pending->setTitle('Pending');
        $manager->persist($pending);

        $resolved = new TicketStatus(TicketStatus::STATUS_TYPE_RESOLVED);
        $resolved->setSysId('resolved');
        $resolved->setTitle('Resolved');
        $manager->persist($resolved);

        $manager->flush();
    }
}
