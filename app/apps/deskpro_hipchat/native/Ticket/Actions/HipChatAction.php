<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\Actions\AbstractContainerAwareAction;
use Application\DeskPRO\Tickets\Actions\ActionInterface;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Actions\AppActionInterface;
use Orb\Util\Strings;
use Orb\Util\Util;

class HipChatAction extends AbstractContainerAwareAction implements ActionInterface, AppActionInterface
{
	/**
	 * @var
	 */
	private $app;

	/**
	 * @return AppInstance
	 */
	private function getApp()
	{
		if ($this->app !== null) {
			return $this->app;
		}

		$this->app = false;
		$app_manager = $this->getContainer()->getAppManager();
		$app_id = $this->getMetaData()->get('app_id', 0);

		if ($app_manager->hasApp($app_id)) {
			$this->app = $app_manager->getApp($app_id);
		}

		return $this->app === false ? null : $this->app;
	}


	/**
	 * {@inheritDoc}
	 */
	public function applyAction(Ticket $ticket, ExecutorContextInterface $context)
	{
		$app = $this->getApp();
		if (!$app) {
			$context->getLogger()->debug(sprintf('[HipChatAction] No app (app id: %d)', $this->getMetaData()->get('app_id')));
			return;
		}

		$message = $this->renderMessage($ticket, $context);
		$room_id = $this->getActionOption('room');

		$context->getLogger()->debug("[HipChatAction] Sending message to room: $room_id");

		try {
			$api = new \HipChatApi($app->getSetting('api_token'));
			$api->message_room(
				$room_id,
				'DeskPRO',
				$message,
				$app->getSetting('notify')
			);

			$ticket->getStateChangeRecorder()->recordData('app_message', array(
				'app_id'        => $app->id,
				'app_title'     => $app->title,
				'package_name'  => $app->package->name,
				'package_title' => $app->package->title,
				'message'       => "Send message to room \"$room_id\""
			));
		} catch (\Exception $e) {
			$context->getLogger()->notice("[HipChatAction] Error sending HipChat message: {$e->getMessage()}");

			$ticket->getStateChangeRecorder()->recordData('app_message',
			array( 'app_id'        => $app->id, 'app_title' => $app->title, 'package_name' => $app->package->name,
			       'package_title' => $app->package->title, 'message' => "Failed sending message to room \"$room_id\"" ));
		}
	}


	/**
	 * @param Ticket $ticket
	 * @param ExecutorContextInterface $context
	 * @return string
	 */
	public function renderMessage(Ticket $ticket, ExecutorContextInterface $context)
	{
		$statechange = $ticket->getStateChangeRecorder();

		$message = '#' . $ticket->id . ' <a href="' . $this->getContainer()->getSetting('core.deskpro_url') . 'agent/#app.tickets,t:' . $ticket->id . '">';
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
		$message = Strings::htmlEntityEncodeUtf8($message);

		return $message;
	}


	/**
	 * @return string
	 */
	public function getActionType()
	{
		return Util::getBaseClassname($this) . $this->getMetaData()->get('app_id', 0);
	}
}
