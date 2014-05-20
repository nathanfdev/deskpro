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
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;
use Zend\Validator\EmailAddress;

/**
 * Adds and removed CC'ed users to the ticket, creating users as necessary.
 *
 * @option string[] add_emails       Array of email addresses of users to add
 * @option string[] remove_emails    Array of email addresses of users to remove
 * @option bool     add_org_managers True to add all org managers
 */
class SetCcs extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addValidNames('add_emails', 'remove_emails', 'add_org_managers');
		return $options;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		#------------------------------
		# Add org managers
		#------------------------------

		if ($this->getActionOption('add_org_managers') && $ticket->organization) {
			$managers = $this->getContainer()->getEm()->getRepository('DeskPRO:Organization')->getManagers($ticket->organization);
			foreach ($managers AS $manager) {
				if (!$ticket->hasParticipantPerson($manager)) {
					$ticket->addParticipantPerson($manager);
				}
			}
		}

		#------------------------------
		# Add people
		#------------------------------

		$reg_closed = !$this->getContainer()->getSetting('core.reg_enabled');
		foreach ($this->getActionOption('add_emails') as $email) {
			if ($ticket->hasParticipantEmailAddress($email)) {
				continue;
			}

			$person = $this->getContainer()->getEm()->getRepository('DeskPRO:Person')->findOneByEmail($email);
			if ($person) {
				$ticket->addParticipantPerson($person);
			} else {
				if ($reg_closed) {
					continue;
				}
				$person_processor = new PersonFromEmailProcessor();

				$eml = new EmailAddress();
				$eml->email = $email;
				$person = $person_processor->createPerson($eml, true);

				if ($person) {
					$ticket->addParticipantPerson($person);
				}
			}
		}

		#------------------------------
		# Remove people
		#------------------------------

		foreach ($this->getActionOption('remove_emails') as $email) {
			foreach ($ticket->participants as $k => $p) {
				if ($p->person->findEmailAddress($email)) {
					$ticket->removeParticipantPerson($p->person);
				}
			}
		}
	}


	/**
	 * {@inheritDoc}
	 */
	public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'cc')) {
			return array('cc');
		}

		return array();
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyMacro(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		$this->applyAction($ticket, $context);
	}
}