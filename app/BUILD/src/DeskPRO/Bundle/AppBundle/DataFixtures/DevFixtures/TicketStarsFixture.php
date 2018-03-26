<?php

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
