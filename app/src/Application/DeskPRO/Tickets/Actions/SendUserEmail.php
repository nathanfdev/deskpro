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
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketEmailBuilder;
use Orb\Util\CheckedOptionsArray;

/**
 * Send an email to the user
 *
 * @option bool template       The template to send
 * @option bool from_name      Who to send the email from
 * @option bool from_account   The account to send from (falsey for ticket account)
 * @option bool do_cc_users    True to CC the email to other user parts in the ticket
 */
class SendUserEmail extends AbstractEmailAction
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addValidNames('template', 'from_name', 'from_account', 'do_cc_users');
		return $options;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$context->getLogger()->debug("[SendUserEmail] Begin");
		$start_time = microtime(true);

		try {
			$from_account = $this->getFromEmailAccountOption($ticket, $context);
		} catch (\InvalidArgumentException $e) {
			$context->getLogger()->warn("[SendUserEmail] Error {$e->getMessage()}");
			return;
		}

		try {
			$template = $this->getEmailTemplateOption($ticket, $context, false);
		} catch (\InvalidArgumentException $e) {
			$context->getLogger()->warn("[SendUserEmail] Error {$e->getMessage()}");
			return;
		}

		#-------------------------
		# Vars
		#-------------------------

		$default_vars = $this->getStandardEmailVars($ticket, $context, 'user');

		#-------------------------
		# Send emails
		#-------------------------

		$build = TicketEmailBuilder::createFromContainer($this->getContainer())
			->setTicket($ticket)
			->setToPerson($ticket->person)
			->setUserMode()
			->setTemplateName($template)
			->setFromName($this->renderFromName($this->getActionOption('from_name'), $ticket, $context, 'user'))
			->setMaxAttachSize($this->getContainer()->getSetting('core.sendemail_attach_maxsize'))
			->setLogger($context->getLogger())
			->setFromEmailAccount($from_account);

		if ($this->getActionOption('do_cc_users')) {
			$build->enableUserCc();
		}

		// If this is from a user reply, then mark the email as auto and handle disable auto setting
		if ($context->getEventPerformer() == 'user' && $ticket->getStateChangeRecorder()->hasNewReply()) {
			$context->getLogger()->info("[SendUserEmail] Identified as an automatic email");
			$build->setIsAuto();

			if ($ticket->person->disable_autoresponses) {
				$context->getLogger()->info("Skipping email because user is marked as an auto-responder");
				return;
			}
		}

		$ticket_email = $build->buildTicketEmail();

		try {
			$ticket_email->send($default_vars);
			$this->recordEmailTicketLog($ticket_email, $ticket, $context);
		} catch (\Exception $e) {
			$context->getLogger()->error(
				sprintf("Exception: [%s] %s", $e->getCode(), $e->getMessage()),
				array('exception' => $e)
			);

			throw $e;
		}

		$context->getLogger()->info(sprintf("[SendUserEmail] Sent message in %.3fs", microtime(true)-$start_time));
	}


	/**
	 * {@inheritDoc}
	 */
	public function isNoop(Ticket $ticket, ExecutorContextInterface $context)
	{
		if (!$this->getContainer()->getEmailAccountManager()->countOutgoingAccounts()) {
			$context->getLogger()->debug("[SendUserEmail] no outgoing email accounts are defined");
			return true;
		}

		if ($context->getVars()->get('mute_user_emails')) {
			$context->getLogger()->debug("[SendUserEmail] mute_user_emails = true");
			return true;
		}

		return false;
	}
}