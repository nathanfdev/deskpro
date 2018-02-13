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

use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TicketsToFeedbacksFixture extends AbstractDpFixture implements OrderedFixtureInterface
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
        $this->loadTicketToFeedback();
    }

    private function initIds()
    {
        $this->agentIds    = $this->fetchIds(self::TABLE_PEOPLE, [['field' => 'is_agent', 'value' => 1]]);
        $this->ticketIds   = $this->fetchIds(self::TABLE_TICKETS);
        $this->feedbackIds = $this->fetchIds(self::TABLE_FEEDBACK);
    }

    private function loadTicketToFeedback()
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

        $this->db->batchInsert(self::TABLE_TICKETS_TO_FEEDBACK, $batch);
    }
}
