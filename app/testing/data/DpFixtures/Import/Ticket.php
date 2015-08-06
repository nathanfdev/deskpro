<?php

namespace DpFixtures\Import;

use Application\DeskPRO\Entity;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class Ticket
 * @package DpFixtures\Import
 */
class Ticket extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $ticket = new Entity\Ticket();
        $ticket
            ->disableAutoTicketProcess()
            ->setSubject('Subject 1')
            ->setRef('AAABBBCCC')
            ->setStatus(Entity\Ticket::STATUS_AWAITING_USER)
        ;

        $manager->persist($ticket);
        $manager->flush();

        foreach (array('old label 1', 'old label 2') as $label) {
            $label_entity = new Entity\LabelTicket();
            $label_entity->setLabel($label);

            $ticket->addLabel($label_entity);
            $manager->persist($label_entity);
        }

        $manager->persist($ticket);
        $manager->flush();
    }
}
