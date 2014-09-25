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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\SnippetFormatter;
use Orb\Util\CheckedOptionsArray;
use Orb\Util\Strings;

/**
 * Set the subject.
 *
 * @option string subject
 */
class SetSubject extends AbstractContainerAwareAction implements ActionInterface, MacroActionInterface, NoopableInterface
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('subject');
		$options->addValidNames('with_formatter');
		return $options;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		if (!$this->getActionOption('subject')) {
			return;
		}

		$subject = $this->getActionOption('subject');

		if ($this->getActionOption('with_formatter')) {
			$formatter = new SnippetFormatter($this->getContainer()->getTwig());
			$formatter->addVar('user_vars', $context->getUserVars());
			$subject = $formatter->formatText($subject, $ticket);
			$subject = preg_replace("#[\r\n]#", ' ', $subject);
			$subject = preg_replace("#\\s{2,}#", ' ', $subject);
			$subject = trim($subject);

			if (!$subject) {
				$context->getLogger()->notice("[SetSubject] Subject pattern evaluates to an empty string");
				return;
			}
		}

		if ($subject != $ticket->subject) {
			$ticket->subject = $subject;
		}
	}


	/**
	 * {@inheritDoc}
	 */
	public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
	{
		if ($ticket->subject == $this->getActionOption('subject') || !$this->getActionOption('subject')) {
			return true;
		}

		return false;
	}


	/**
	 * {@inheritDoc}
	 */
	public function getMacroPermissionErrors(Person $person, Ticket $ticket, ExecutorContextInterface $context)
	{
		if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
			return array('fields');
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