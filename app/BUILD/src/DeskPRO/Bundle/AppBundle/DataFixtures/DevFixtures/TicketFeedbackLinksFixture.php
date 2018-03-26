<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TicketFeedbackLinksFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    const FEEDBACK_PER_TICKET_MIN = 0;
    const FEEDBACK_PER_TICKET_MAX = 5;

    /**
     * @var int[]
     */
    private $agentIds;

    /**
     * @var int[]
     */
    private $ticketIds;

    /**
     * @var int[]
     */
    private $feedbackIds;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 150;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->initIds();
        $this->loadTicketFeedbackLink();
    }

    private function initIds()
    {
        $this->agentIds    = $this->fetchIds(self::TABLE_PEOPLE, [['field' => 'is_agent', 'value' => 1]]);
        $this->ticketIds   = $this->fetchIds(self::TABLE_TICKETS);
        $this->feedbackIds = $this->fetchIds(self::TABLE_FEEDBACK);
    }

    private function loadTicketFeedbackLink()
    {
        $batch = [];

        shuffle($this->ticketIds);

        foreach ($this->ticketIds as $ticketId) {
            $feedbackPerTicket = $this->faker->numberBetween(self::FEEDBACK_PER_TICKET_MIN, self::FEEDBACK_PER_TICKET_MAX);
            $feedbackIds       = $this->faker->randomElements($this->feedbackIds, $feedbackPerTicket);

            foreach ($feedbackIds as $feedbackId) {
                $batch[] = [
                    'ticket_id'    => $ticketId,
                    'feedback_id'  => $feedbackId,
                    'person_id'    => $this->faker->randomElement($this->agentIds),
                    'date_created' => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                ];
            }
        }

        $this->db->batchInsert(self::TABLE_TICKET_FEEDBACK_LINKS, $batch);
    }
}
