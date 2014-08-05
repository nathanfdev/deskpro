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
use Application\DeskPRO\ORM\StateChange\ChangeEmailLog;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketEmail;
use Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\Strings;

abstract class AbstractEmailAction extends AbstractContainerAwareAction implements ActionInterface, NoopableInterface
{
	/**
	 * @param Ticket $ticket
	 * @param ExecutorContextInterface $context
	 * @return \Application\DeskPRO\Entity\EmailAccount
	 * @throws \InvalidArgumentException
	 */
	protected function getFromEmailAccountOption(Ticket $ticket, ExecutorContextInterface $context)
	{
		$from_account = $this->getActionOption('from_account') ?: null;
		if ($from_account) {

			if (Numbers::isInteger($from_account)) {
				$from_account_id = $from_account;
				try {
					$from_account = $this->getContainer()->getEmailAccountManager()->getAccount($from_account_id);
				} catch (\OutOfBoundsException $e) {
					$context->getLogger()->debug("[AbstractEmailAction] Invalid account: $from_account_id");
					throw new \InvalidArgumentException('invalid_account');
				}
			}

			$context->getLogger()->debug("[AbstractEmailAction] Sending with email account: $from_account");

			if (!$from_account->is_enabled) {
				$context->getLogger()->warn("[AbstractEmailAction] Email account is not enabled");
				throw new \InvalidArgumentException('account_disabled');
			}

			if (!$from_account->outgoing_account) {
				$context->getLogger()->warn("[AbstractEmailAction] Email account is not an outgoing account");
				throw new \InvalidArgumentException('account_not_outgoing');
			}
		}

		return $from_account;
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContextInterface $context
	 * @param bool $allow_blank
	 * @return string|null
	 * @throws \InvalidArgumentException
	 */
	protected function getEmailTemplateOption(Ticket $ticket, ExecutorContextInterface $context, $allow_blank = false)
	{
		$template = $this->getActionOption('template');
		if (!$template) {
			if ($allow_blank) {
				return null;
			}

			$context->getLogger()->warn("[AbstractEmailAction] No template specified");
			throw new \InvalidArgumentException('no_template_specified');
		}

		$context->getLogger()->debug("[AbstractEmailAction] Using template: $template");
		if (!$this->getContainer()->getTemplating()->exists($template)) {
			$context->getLogger()->warn("[AbstractEmailAction] Template does not exist");
			throw new \InvalidArgumentException('invalid_templte');
		}

		return $template;
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContextInterface $context
	 * @param string $mode 'user' or 'agent'
	 * @return array
	 */
	protected function getStandardEmailVars(Ticket $ticket, ExecutorContextInterface $context, $mode)
	{
		#------------------------------
		# Build up some type flags
		#------------------------------

		$state = $ticket->getStateChangeRecorder();
		if ($state->isNewTicket()) {
			$type = 'newticket';
		} else if ($state->hasChangedField('message')) {
			$type = 'newreply';
		} else {
			$type = 'updated';
		}

		$context->getLogger()->info("[AbstractEmailAction] Type: $type");
		$context->getLogger()->info(sprintf("[AbstractEmailAction] Performer: %s", $context->getEventPerformer()));

		#------------------------------
		# Set reply flags
		#------------------------------

		$new_replies = $state->getNewReplies();
		$is_new_ticket      = $state->isNewTicket();
		$is_new_agent_reply = false;
		$is_new_agent_note  = false;
		$is_new_user_reply  = false;

		foreach ($new_replies as $message) {
			if ($message->is_agent_note) {
				$is_new_agent_note = true;
			} else if ($message->person->is_agent) {
				$is_new_agent_reply = true;
			} else {
				$is_new_user_reply = true;
			}
		}

		// In user mode, never show notes
		if ($mode == 'user') {
			$new_replies = array_filter($new_replies, function($r) { return !$r->is_agent_note; });
			$ticket_logs = null;

		// Agent mode - include ticket logs
		} else {
			$ticketlog_generator = new TicketLogGenerator($ticket, $context);
			$ticket_logs = $ticketlog_generator->getLogEntries();
		}

		#------------------------------
		# Build map of mentions
		#------------------------------

		$vars = array(
			'type'               => $type,
			'performer_type'     => $context->getEventPerformer(),
			'is_new_ticket'      => $is_new_ticket,
			'is_new_agent_reply' => $is_new_agent_reply,
			'is_new_agent_note'  => $is_new_agent_note,
			'is_new_user_reply'  => $is_new_user_reply,
			'is_status_change'   => $state->hasChangedField('status'),
			'action_performer'   => $context->getPersonContext(),
			'new_message'        => Arrays::getLastItem($new_replies),
			'new_messages'       => $new_replies,
			'ticket_logs'        => $ticket_logs,
		);

		return $vars;
	}


	/**
	 * @param TicketEmail $ticket_email
	 * @param Ticket $ticket
	 * @param ExecutorContextInterface $context
	 */
	protected function recordEmailTicketLog(TicketEmail $ticket_email, Ticket $ticket, ExecutorContextInterface $context)
	{
		$state = $ticket->getStateChangeRecorder();

		$change = new ChangeEmailLog(
			'ticket_email',
			$ticket_email->getUserMode(),
			$ticket_email->getSentToName(),
			$ticket_email->getSentToEmail(),
			$ticket_email->getSentWithCcs(),
			$ticket_email->getFromName(),
			$ticket_email->getFromEmailAccount()->getUseEmailAddress(),
			$ticket_email->getTemplateName()
		);

		$state->recordChange($change);
	}


	/**
	 * @param string $name
	 * @param Ticket $ticket
	 * @param ExecutorContextInterface $context
	 * @param string $email_mode 'user' or 'agent'
	 * @return string
	 */
	protected function renderFromName($name, Ticket $ticket, ExecutorContextInterface $context, $email_mode)
	{
		if (!$name) {
			return '';
		}

		switch ($name) {
			case 'performer':
				$person = $context->getPersonContext();
				if (!$person) {
					return '';
				}

				if ($email_mode == 'agent') {
					return $person->getDisplayName();
				} else {
					return $person->getDisplayNameUser();
				}
			case 'helpdesk_name':
				return $this->getContainer()->getSetting('core.deskpro_name');
			case 'site_name':
				return $this->getContainer()->getSetting('core.site_name');
			default:
				try {
					$name = $this->getContainer()->getTwig()->renderStringTemplate($name, array(
						'performer'     => $context->getPersonContext(),
						'ticket'        => $ticket,
						'helpdesk_name' => $this->getContainer()->getSetting('core.deskpro_name'),
						'site_name'     => $this->getContainer()->getSetting('core.site_name'),
						'user_vars'     => $context->getUserVars(),
					));

					return trim(Strings::collapseWhitespace(Strings::removeLineBreaks($name)));
				} catch (\Exception $e) {
					$context->getLogger()->warn('Invalid name pattern syntax: ' . $name . '. Exception: ' . $e->getMessage(), array('exception' => $e));
					return '';
				}
		}
	}
}