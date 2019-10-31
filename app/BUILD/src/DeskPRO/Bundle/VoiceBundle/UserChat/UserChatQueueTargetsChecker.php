<?php

namespace DeskPRO\Bundle\VoiceBundle\UserChat;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueueAgent;

/**
 * Class UserChatQueueTargetsChecker.
 */
class UserChatQueueTargetsChecker
{
    /**
     * @var array
     */
    private $queueAgentsMap = [];

    /**
     * @param UserChatQueue $chatQueue
     * @param Person        $agent
     *
     * @return bool
     */
    public function isAgentMemberOfChatQueue(UserChatQueue $chatQueue, Person $agent)
    {
        if (!$chatQueue->isAllAgents()) {
            if (!isset($this->queueAgentsMap[$chatQueue->getId()])) {
                $this->queueAgentsMap[$chatQueue->getId()] = [];
                foreach ($chatQueue->getTargets() as $target) {
                    if ($target instanceof UserChatQueueAgent) {
                        $this->queueAgentsMap[$chatQueue->getId()][] = $target->getAgent()->getId();
                    }
                }
            }

            if (in_array($agent->getId(), $this->queueAgentsMap[$chatQueue->getId()])) {
                return true;
            }
        } else {
            return true;
        }
    }
}
