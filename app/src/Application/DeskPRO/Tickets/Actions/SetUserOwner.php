<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Actions;

use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;
use Orb\Validator\StringEmail;

/**
 * Sets the user owner of a ticket
 *
 * @option int email_address
 * @option bool add_cc
 */
class SetUserOwner extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('email_address');
		$options->addValidNames('add_cc');
		return $options;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$user_email = $this->getActionOption('email_address');

		$reg_closed = !$this->getContainer()->getSetting('core.reg_enabled');
		$person = $this->getContainer()->getEm()->getRepository('DeskPRO:Person')->findOneByEmail($user_email);

		if (!$person) {
			if ($reg_closed) {
				return;
			}
			$person_processor = new PersonFromEmailProcessor();

			$eml = new EmailAddress();
			$eml->email = $user_email;
			$person = $person_processor->createPerson($eml, true);
		}

		$orig_person = $ticket->person;

		if ($person) {
			$ticket->person = $person;
			$ticket->person_email = null;
			$ticket->person_email_validating = null;

			if ($this->getActionOption('add_cc')) {
				if (!$ticket->hasParticipantPerson($orig_person)) {
					$ticket->addParticipantPerson($orig_person);
				}
			}
		}
	}


	/**
	 * {@inheritDoc}
	 */
	public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
	{
		$user_email = $this->getActionOption('email_address');
		if (!StringEmail::isValueValid($user_email)) {
			return true;
		}
		if ($ticket->person->hasEmailAddress($user_email)) {
			return true;
		}

		return false;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		$set_agent_id = $this->getActionOption('agent_id');
		if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_agent')) {
			if ($set_agent_id == $person->getId()) {
				if ($person->PermissionsManager->TicketChecker->canModify($ticket, 'assign_self')) {
					return null;
				}
				return array('assign_self');
			}

			return array('assign_agent');
		}

		return null;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		$this->applyAction($ticket, $context);
	}
}