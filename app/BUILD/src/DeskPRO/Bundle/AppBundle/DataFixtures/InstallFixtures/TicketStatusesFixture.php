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
        $spam->persist($spam);
    }
}
