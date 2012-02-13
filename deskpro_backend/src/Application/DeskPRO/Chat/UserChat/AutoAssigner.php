<?php

namespace Application\DeskPRO\Chat\UserChat;

use Application\DeskPRO\App;
use Orb\Util\Util;

use Application\DeskPRO\Entity\Session as SessionEntity;
use Application\DeskPRO\Entity\Visitor;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\ClientMessage;

/**
 * Manages how chats are assigned automatically
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
		$this->em = $em;
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
				return null;
		}
	}
}
