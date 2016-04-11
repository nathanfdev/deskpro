<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\AgentAlert;
use DeskPRO\Bundle\AppBundle\DataFixtures\DeskProAbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class AgentAlertsFixture.
 */
class AgentAlertsFixture extends DeskProAbstractFixture implements OrderedFixtureInterface
{
    const NUM_ALERTS = 10;

    private $types = ['is_new_ticket', 'is_new_agent_reply', 'is_new_agent_note', 'is_new_user_reply'];

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 90;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $people  = $this->fetchIds(self::TABLE_PEOPLE);
        $tickets = $this->fetchIds(self::TABLE_TICKETS);
        $type    = $this->faker->randomElement($this->types);

        $i = 0;
        while ($i++ < self::NUM_ALERTS) {
            $data = [
                '@fetch_types' => [
                    'ticket'    => 'DeskPRO:Ticket',
                    'performer' => 'DeskPRO:Person',
                    'log_items' => 'DeskPRO:TicketLog',
                ],
                'ticket'             => $this->faker->randomElement($tickets),
                'performer'          => $this->faker->randomElement($people),
                'is_new_ticket'      => $type === 'is_new_ticket',
                'is_new_agent_reply' => $type === 'is_new_agent_reply',
                'is_new_agent_note'  => $type === 'is_new_agent_note',
                'is_new_user_reply'  => $type === 'is_new_user_reply',
                'browser_rendered'   => '<big>Alert title</big><small>Alert summary</small>',
                // 'log_items'          => $log_ids,
            ];
            $alert           = new AgentAlert();
            $alert->person   = $this->getReference('admin');
            $alert->typename = 'tickets';
            $alert->data     = $data;
            $manager->persist($alert);
        }
        $manager->flush();
    }
}
