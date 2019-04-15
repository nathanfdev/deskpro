<?php

namespace DeskPRO\Bundle\VoiceBundle\Model;

use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use JMS\Serializer\Annotation as JMS;

/**
 * Class AverageWaitingTime.
 */
class AverageWaitingTime
{
    /**
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\VoiceQueue>")
     *
     * @var VoiceQueue
     */
    private $queue;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $usersCount;

    /**
     * @JMS\Type("integer")
     *
     * @var int
     */
    private $averageTime;

    /**
     * Constructor.
     *
     * @param VoiceQueue $queue
     * @param array      $waitingUsers
     */
    public function __construct(VoiceQueue $queue, array $waitingUsers)
    {
        $totalWaitingTime = array_reduce($waitingUsers, function ($carry, $item) {
            $carry += strtotime('now') - strtotime($item['date_created']);

            return $carry;
        });

        $this->queue       = $queue;
        $this->usersCount  = count($waitingUsers);
        $this->averageTime = ceil($totalWaitingTime / $this->usersCount / 60);
    }
}
