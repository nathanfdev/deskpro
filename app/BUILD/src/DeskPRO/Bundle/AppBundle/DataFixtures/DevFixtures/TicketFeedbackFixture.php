<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\TicketFeedback;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TicketFeedbackFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    const FEEDBACK_PER_TICKET_MIN = 0;
    const FEEDBACK_PER_TICKET_MAX = 5;

    /**
     * @var int[]
     */
    private $personIds;

    /**
     * @var int[]
     */
    private $ticketIds;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 120;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->initIds();
        $this->loadTicketFeedback();
    }

    private function initIds()
    {
        $this->personIds = $this->fetchIds(self::TABLE_PEOPLE, [['field' => 'is_agent', 'value' => 0]]);
        $this->ticketIds = $this->fetchIds(self::TABLE_TICKETS);
    }

    private function loadTicketFeedback()
    {
        $batch = [];

        shuffle($this->ticketIds);

        foreach ($this->ticketIds as $ticketId) {
            $feedbackPerTicket = $this->faker->numberBetween(self::FEEDBACK_PER_TICKET_MIN, self::FEEDBACK_PER_TICKET_MAX);

            for ($i = 0; $i < $feedbackPerTicket; ++$i) {
                $batch[] = [
                    'ticket_id'    => $ticketId,
                    'rating'       => $this->faker->randomElement([TicketFeedback::RATE_NEGATIVE, TicketFeedback::RATE_POSITIVE, TicketFeedback::RATE_NEUTRAL]),
                    'person_id'    => $this->faker->randomElement($this->personIds),
                    'message'      => $this->faker->realText(50),
                    'date_created' => $this->faker->boolean(30) ? $this->faker->dateTimeBetween('-1 month')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                ];
            }
        }

        $this->db->batchInsert(self::TABLE_TICKET_FEEDBACK, $batch);
    }
}
