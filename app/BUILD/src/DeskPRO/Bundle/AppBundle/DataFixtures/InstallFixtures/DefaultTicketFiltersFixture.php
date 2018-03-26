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
        $f->setDisplayOrder(10);
        $f->setQuery('ticket.status = \'awaiting_agent\' AND ticket.agent = $me');
        $set->addFilter($f);
        $manager->persist($f);

        $f = new TicketFilter();
        $f->setTitle('Tickets I Follow');
        $f->setDisplayOrder(20);
        $f->setQuery('ticket.status = \'awaiting_agent\' AND ticket.followers HAS $me');
        $set->addFilter($f);
        $manager->persist($f);

        $f = new TicketFilter();
        $f->setTitle('Assigned To Team');
        $f->setDisplayOrder(30);
        $f->setQuery('ticket.status = \'awaiting_agent\' AND ticket.agent_team IN $my_teams');
        $set->addFilter($f);
        $manager->persist($f);

        $f = new TicketFilter();
        $f->setTitle('Unassigned');
        $f->setDisplayOrder(40);
        $f->setQuery('ticket.status = \'awaiting_agent\' AND ticket.agent IS EMPTY');
        $set->addFilter($f);
        $manager->persist($f);

        $f = new TicketFilter();
        $f->setTitle('All Awaiting Agent');
        $f->setDisplayOrder(50);
        $f->setQuery('ticket.status = \'awaiting_agent\'');
        $set->addFilter($f);
        $manager->persist($f);

        $manager->flush();
    }
}
