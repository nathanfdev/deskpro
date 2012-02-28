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
use Application\DeskPRO\Entity\Ticket;

use Orb\Util\Arrays;

class TicketChecker extends AbstractChecker
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	protected function init()
	{
		$this->person->loadHelper('Agent');
	}

	/**
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return bool
	 */
	public function canView(Ticket $ticket)
	{
		if (!$this->person->hasPerm('agent_tickets.use')) {
			return false;
		}

		#------------------------------
		# If the user is part of the ticket
		# then we know right away they can view
		#------------------------------

		if ($ticket->agent && $ticket->agent->id = $this->person->id) {
			return true;
		}

		if ($ticket->agent_team && $this->person->getHelper('Agent')->isTeamMember($ticket->agent_team->id)) {
			return true;
		}

		if ($ticket->hasParticipantPerson($this->person)) {
			return true;
		}

		#------------------------------
		# Can't view certain deps
		#------------------------------

		if ($ticket->department && !$this->person->getHelper('AgentPermissions')->isDepartmentAllowed($ticket->department)) {
			return false;
		}

		#------------------------------
		# Cant view unassigned
		#------------------------------

		if (!$ticket->agent && !$this->person->hasPerm('agent_tickets.view_unassigned')) {
			return false;
		}

		#------------------------------
		# Cant view others
		#------------------------------

		if ($ticket->agent && !$this->person->hasPerm('agent_tickets.view_others')) {
			return false;
		}

		// If we got here, then we're allowed
		return true;
	}


	/**
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return bool
	 */
	public function canDelete(Ticket $ticket)
	{
		if (!$this->canView($ticket)) {
			return false;
		}

		#------------------------------
		# Can delete own
		#------------------------------

		if ($this->person->hasPerm('agent_tickets.delete_own')) {
			if ($ticket->agent && $ticket->agent->id = $this->person->id) {
				return true;
			}

			if ($ticket->agent_team && $this->person->getHelper('Agent')->isTeamMember($ticket->agent_team->id)) {
				return true;
			}
		}

		#------------------------------
		# Can delete unassigned
		#------------------------------

		if (!$ticket->agent && $this->person->hasPerm('agent_tickets.view_unassigned')) {
			return true;
		}

		#------------------------------
		# Can delete others
		#------------------------------

		if ($ticket->agent && $this->person->hasPerm('agent_tickets.view_unassigned')) {
			return true;
		}

		#------------------------------
		# Cant delete
		#------------------------------

		return false;
	}


	/**
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return bool
	 */
	public function canReply(Ticket $ticket)
	{
		if (!$this->canView($ticket)) {
			return false;
		}

		#------------------------------
		# Can delete own
		#------------------------------

		if ($this->person->hasPerm('agent_tickets.reply_own')) {
			if ($ticket->agent && $ticket->agent->id = $this->person->id) {
				return true;
			}

			if ($ticket->agent_team && $this->person->getHelper('Agent')->isTeamMember($ticket->agent_team->id)) {
				return true;
			}
		}

		#------------------------------
		# Can delete unassigned
		#------------------------------

		if (!$ticket->agent && $this->person->hasPerm('agent_tickets.reply_unassigned')) {
			return true;
		}

		#------------------------------
		# Can delete others
		#------------------------------

		if ($ticket->agent && $this->person->hasPerm('agent_tickets.reply_others')) {
			return true;
		}

		#------------------------------
		# Cant delete
		#------------------------------

		return false;
	}


	/**
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return bool
	 */
	public function canModify(Ticket $ticket, $op)
	{
		if (!$this->canView($ticket)) {
			return false;
		}


		#------------------------------
		# Figure out which set of permissions
		# the current ticket falls into
		#------------------------------

		// Own tickets
		if (($ticket->agent && $ticket->agent->id == $this->person->id) || $ticket->agent_team && $this->person->getHelper('Agent')->isTeamMember($ticket->agent_team->id)) {
			$set_suffix = 'own';

		// Unassigned tickets
		} elseif (!$ticket->agent && !$ticket->agent_team) {
			$set_suffix = 'unassigned';

		// Other
		} else {
			$set_suffix = 'other';
		}

		$perm_gloabl   = 'agent_tickets.modify_' . $set_suffix;
		$perm_specific = 'agent_tickets.modify_' . $op . '_' . $set_suffix;

		if ($this->person->hasPerm($perm_gloabl) || $this->person->hasPerm($perm_specific)) {
			return true;
		}

		return false;
	}
}
