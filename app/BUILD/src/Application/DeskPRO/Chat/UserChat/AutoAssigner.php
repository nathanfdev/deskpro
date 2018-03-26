<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Chat\UserChat;

use Application\DeskPRO\Entity\ChatConversation;

/**
 * Manages how chats are assigned automatically.
 */
class AutoAssigner
{
    const MODE_ROUND_ROBIN = 'round_robin';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var string
     */
    protected $mode;

    public function __construct($mode, EntityManager $em)
    {
        $this->em   = $em;
        $this->mode = $mode;
    }

    public function getAgent(ChatConversation $convo)
    {
        switch ($this->mode) {
            case self::MODE_ROUND_ROBIN:
                $assign_agent = $this->em->getRepository('DeskPRO:Person')->getChatAgentRoundRobin();

                return $assign_agent;
                break;

            default:
                return;
        }
    }
}
