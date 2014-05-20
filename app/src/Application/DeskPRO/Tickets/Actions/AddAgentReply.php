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

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\SnippetFormatter;
use Orb\Util\CheckedOptionsArray;

/**
 * Adds a reply to the ticket
 *
 * @option string reply_text
 * @option int    by_agent_id
 * @option bool   by_assigned_agent
 * @option bool   with_formatter
 */
class AddAgentReply extends AbstractContainerAwareAction implements ActionInterface
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('by_agent_id');
		$options->addRequiredNames('reply_text');
		$options->addValidNames('by_assigned_agent');
		$options->addValidNames('with_formatter');
		return $options;
	}

	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$agent = null;
		if ($this->getActionOption('by_assigned_agent') && $ticket->agent) {
			$agent = $ticket->agent;
		}
		if (!$agent) {
			$agent = $this->getContainer()->getAgentData()->get($this->getActionOption('by_agent_id'));
		}

		if (!$agent) {
			return;
		}

		$em = $this->getContainer()->getEm();

		$message = new TicketMessage();
		$message->person = $agent;
		$message->date_created = new \DateTime('+1 second');

		$reply_text = $this->getActionOption('reply_text');

		if ($this->getActionOption('with_formatter')) {
			$formatter = new SnippetFormatter($this->getContainer()->getTwig());
			$reply_text = $formatter->formatText($reply_text, $ticket);
		}

		$message->setMessageText($reply_text);

		$ticket->addMessage($message);
		$em->persist($message);
	}
}