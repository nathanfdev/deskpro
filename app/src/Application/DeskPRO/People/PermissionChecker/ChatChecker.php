<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People\PermissionChecker;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\ChatConversation;

use Orb\Util\Arrays;

class ChatChecker extends AbstractChecker
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @param \Application\DeskPRO\Entity\ChatConversation $convo
	 * @return bool
	 */
	public function canView(ChatConversation $convo)
	{
		if (!$this->person->hasPerm('agent_chat.use')) {
			return false;
		}

		#------------------------------
		# If the user is part of the chat
		# then we know right away they can view
		#------------------------------

		if ($convo->agent && $convo->agent->id = $this->person->id) {
			return true;
		}

		#------------------------------
		# Can't view certain deps
		#------------------------------

		if ($convo->department && !$this->person->getHelper('AgentPermissions')->isDepartmentAllowed($convo->department, 'chat')) {
			return false;
		}

		#------------------------------
		# Cant view unassigned
		#------------------------------

		if (!$convo->agent && !$this->person->hasPerm('agent_chat.view_unassigned')) {
			return false;
		}

		#------------------------------
		# Cant view others
		#------------------------------

		if ($convo->agent && !$this->person->hasPerm('agent_chat.view_others')) {
			return false;
		}

		// If we got here, then we're allowed
		return true;
	}


	/**
	 * @param \Application\DeskPRO\Entity\ChatConversation $convo
	 * @return bool
	 */
	public function canDelete(ChatConversation $convo)
	{
		if (!$this->canView($convo) || !$this->person->hasPerm('agent_chat.delete')) {
			return false;
		}

		return false;
	}
}
