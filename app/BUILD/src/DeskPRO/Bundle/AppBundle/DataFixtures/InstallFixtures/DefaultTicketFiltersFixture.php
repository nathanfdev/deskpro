<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use Doctrine\Common\Persistence\ObjectManager;

class DefaultTicketFiltersFixture extends AbstractDpFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->loadInboxSet($manager);
    }

    public function loadInboxSet(ObjectManager $manager)
    {
        $set = new TicketFilterSet();
        $set->setDisplayOrder(0);
        $set->setTitle('Inbox');
        $set->enableGlobalSharing();
        $manager->persist($set);

        $f = new TicketFilter();
        $f->setTitle('Assigned To Me');
        $f->setQuery('ticket.status = \'awaiting_agent\' AND ticket.agent = $me');
        $set->addFilter($f, 10);
        $manager->persist($f);

        $f = new TicketFilter();
        $f->setTitle('Tickets I Follow');
        $f->setQuery('ticket.status = \'awaiting_agent\' AND ticket.followers HAS $me');
        $set->addFilter($f, 20);
        $manager->persist($f);

        $f = new TicketFilter();
        $f->setTitle('Assigned To Team');
        $f->setQuery('ticket.status = \'awaiting_agent\' AND ticket.agent_team IN $my_teams');
        $set->addFilter($f, 30);
        $manager->persist($f);

        $f = new TicketFilter();
        $f->setTitle('Unassigned');
        $f->setQuery('ticket.status = \'awaiting_agent\' AND ticket.agent IS EMPTY');
        $set->addFilter($f, 40);
        $manager->persist($f);

        $f = new TicketFilter();
        $f->setTitle('All Awaiting Agent');
        $f->setQuery('ticket.status = \'awaiting_agent\'');
        $set->addFilter($f, 50);
        $manager->persist($f);

        $manager->flush();
    }
}
