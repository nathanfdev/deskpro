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
 * @category HipChat
 */

namespace deskpro_hipchat\Ticket\Actions;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\AbstractAction;
use Application\DeskPRO\Tickets\Actions\ActionInterface;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class HipChatAction extends AbstractAction implements ActionInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$app = $this->getApp($context);
		if (!$app) {
			return;
		}

		$message = $this->renderMessage($ticket, $context);

		try {
			$api = new \HipChatApi($app->getSetting('api_token'));
			$api->message_room(
				$this->getActionOption('room'),
				'DeskPRO',
				$message,
				$app->getSetting('notify')
			);
		} catch (\Exception $e) {}
	}


	/**
	 * @param ExecutorContextInterface $context
	 * @return \Application\DeskPRO\Entity\AppInstance|null
	 */
	private function getApp(ExecutorContextInterface $context)
	{
		$app_manager = $context->getContainer()->getAppManager();
		$app_id = $this->getActionOption('app_id');

		if (!$app_manager->hasApp($app_id)) {
			return null;
		}

		$app = $app_manager->getApp($app_id);
		return $app;
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContextInterface $context
	 * @return string
	 */
	public function renderMessage(Ticket $ticket, ExecutorContextInterface $context)
	{
		$statechange = $ticket->getStateChangeRecorder();

		$message = '#' . $ticket->id . ' <a href="' . $context->getContainer()->getSetting('core.deskpro_url') . 'agent/#app.tickets,t:' . $ticket->id . '">';
		$message .= htmlspecialchars($ticket->subject);
		$message .= "</a><br/>";

		if ($context->getEventType() == 'newticket') {
			$message .= 'New ticket';
		} else if ($context->getEventType() == 'newreply') {
			if ($statechange->hasNewAgentNote()) {
				$message .= 'New agent note';
			} else if ($statechange->hasNewAgentReply()) {
				$message .= 'New agent reply';
			} else {
				$message .= 'New user reply';
			}
		} else {
			$message .= 'Ticket updated';
		}
		if ($context->getPersonContext()) {
			$message .= ' by ' . htmlspecialchars($context->getPersonContext()->getDisplayContact());
		} else {
			$message .= ' by system';
		}

		// hipchat wants entities for unicode characters so convert them
		$message = preg_replace_callback('/[\x{80}-\x{FFFFFF}]/u', function($match) {
			$string = $match[0];
			$c1 = ord($string[0]);
			if ($c1 < 0x80) {
				return $c1;
			}

			$code = null;

			if (($c1 & 0xF8) == 0xF0) {
				// 4 bytes
				$code = (($c1 & 0x07) << 18) | ((ord($string[1]) & 0x3F) << 12) | ((ord($string[2]) & 0x3F) << 6) | (ord($string[3]) & 0x3F);
			} else if (($c1 & 0xF0) == 0xE0) {
				// 3 bytes
				$code = (($c1 & 0x0F) << 12) | ((ord($string[1]) & 0x3F) << 6) | (ord($string[2]) & 0x3F);
			} else if (($c1 & 0xE0) == 0xC0) {
				// 2 bytes
				$code = (($c1 & 0x1F) << 6) | (ord($string[1]) & 0x3F);
			}

			if ($code) {
				return '&#' . $code . ';';
			} else {
				return '?';
			}
		}, $message);

		return $message;
	}
}