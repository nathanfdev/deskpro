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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonPref;
use Application\DeskPRO\Entity\TicketFlagged;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TicketStarsFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    /**
     * @var int[]
     */
    private $ticketIds;

    /**
     * @var int[]
     */
    private $agentIds;

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
        $this->manager   = $manager;
        $this->agentIds  = $this->fetchIds(self::TABLE_PEOPLE, [['field' => 'is_agent', 'value' => 1]]);
        $this->ticketIds = $this->fetchIds(self::TABLE_TICKETS);
        $this->ticketStars();
        $this->setCustomLabelsForStars();
    }

    private function ticketStars()
    {
        $batch = [];

        foreach ($this->agentIds as $agentId) {
            foreach (TicketFlagged::$colorMap as $colorName) {
                $numTickets = $this->faker->numberBetween(1, 30);
                for ($x = 0; $x < $numTickets; ++$x) {
                    $batch[] = [
                        'person_id' => $agentId,
                        'ticket_id' => $this->faker->randomElement($this->ticketIds),
                        'color'     => $colorName,
                    ];
                }
            }
        }
        $this->db->batchInsert('tickets_flagged', $batch, true);
    }

    private function setCustomLabelsForStars()
    {

        /** @var Person $admin */
        $admin = $this->getReference('admin');
        foreach (TicketFlagged::$colorMap as $color => $colorName) {
            if ($color % 2 === 0) {
                $personPref = new PersonPref();
                $personPref
                    ->setPerson($admin)
                    ->setName('agent.ui.flag.'.$colorName)
                    ->setValueStr(ucfirst($this->faker->word))
                ;

                $this->manager->persist($personPref);
            }
        }
        $this->manager->flush();
    }
}
