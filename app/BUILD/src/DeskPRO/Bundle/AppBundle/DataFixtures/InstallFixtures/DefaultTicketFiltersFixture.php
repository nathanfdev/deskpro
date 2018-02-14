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
        $f->setDisplayOrder(10);
        $f->setQuery('ticket.status = \'awaiting_agent\' AND ticket.followers HAS $me');
        $set->addFilter($f);
        $manager->persist($f);

        $f = new TicketFilter();
        $f->setTitle('Assigned To Team');
        $f->setDisplayOrder(10);
        $f->setQuery('ticket.status = \'awaiting_agent\' AND ticket.agent_team IN $my_teams');
        $set->addFilter($f);
        $manager->persist($f);

        $f = new TicketFilter();
        $f->setTitle('Unassigned');
        $f->setDisplayOrder(10);
        $f->setQuery('ticket.status = \'awaiting_agent\' AND ticket.agent IS EMPTY');
        $set->addFilter($f);
        $manager->persist($f);

        $f = new TicketFilter();
        $f->setTitle('All Awaiting Agent');
        $f->setDisplayOrder(10);
        $f->setQuery('ticket.status = \'awaiting_agent\'');
        $set->addFilter($f);
        $manager->persist($f);

        $manager->flush();
    }
}
