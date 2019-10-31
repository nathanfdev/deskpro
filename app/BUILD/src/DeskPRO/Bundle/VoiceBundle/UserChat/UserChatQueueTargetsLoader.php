<?php

namespace DeskPRO\Bundle\VoiceBundle\UserChat;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueueAgent;
use Doctrine\ORM\EntityManager;

/**
 * Class UserChatQueueTargetsLoader.
 */
class UserChatQueueTargetsLoader
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return array
     */
    public function getActiveAgentIdsForUserChat()
    {
        return $this->getPersonRepo()->getActiveAgentIdsForUserChat();
    }

    /**
     * @param UserChatQueue $chatQueue
     *
     * @return array
     */
    public function getChatQueueTargets(UserChatQueue $chatQueue)
    {
        if ($chatQueue->isAllAgents()) {
            // all agents are available so dynamically create chat queue targets
            // based on the agents list
            $targets = [];

            $agentIds = $this->getPersonRepo()->getActiveAgentIdsForUserChat();
            $agents   = $this->getPersonRepo()->findBy([
                'id' => $agentIds,
            ]);

            foreach ($agents as $agent) {
                $target = new UserChatQueueAgent();
                $target->setAgent($agent);

                $targets[] = $target;
            }
        } else {
            $targets = $chatQueue->getTargets()->toArray();
        }

        return $targets;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository|\Application\DeskPRO\EntityRepository\Person
     */
    private function getPersonRepo()
    {
        return $this->em->getRepository(Person::class);
    }
}
