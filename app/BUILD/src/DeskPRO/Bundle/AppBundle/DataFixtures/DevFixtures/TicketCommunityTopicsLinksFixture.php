<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class TicketCommunityTopicsLinksFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    const TOPICS_PER_TICKET_MIN = 0;
    const TOPICS_PER_TICKET_MAX = 5;

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
    private $topicIds;

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
        $this->loadTicketCommunityTopicsLink();
    }

    private function initIds()
    {
        $this->agentIds  = $this->fetchIds(self::TABLE_PEOPLE, [['field' => 'is_agent', 'value' => 1]]);
        $this->ticketIds = $this->fetchIds(self::TABLE_TICKETS);
        $this->topicIds  = $this->fetchIds(self::TABLE_COMMUNITY_TOPICS);
    }

    private function loadTicketCommunityTopicsLink()
    {
        $batch = [];

        shuffle($this->ticketIds);

        foreach ($this->ticketIds as $ticketId) {
            $communityTopicsPerTicket = $this->faker->numberBetween(self::TOPICS_PER_TICKET_MIN, self::TOPICS_PER_TICKET_MAX);
            $communityTopicIds        = $this->faker->randomElements($this->topicIds, $communityTopicsPerTicket);

            foreach ($communityTopicIds as $communityTopicId) {
                $batch[] = [
                    'ticket_id'    => $ticketId,
                    'topic_id'     => $communityTopicId,
                    'person_id'    => $this->faker->randomElement($this->agentIds),
                    'date_created' => $this->faker->boolean(15) ? $this->faker->dateTimeBetween('-2 days')->format('Y-m-d H:i:s') : $this->faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                ];
            }
        }

        $this->db->batchInsert(self::TABLE_TICKET_COMMUNITY_TOPICS_LINKS, $batch);
    }
}
