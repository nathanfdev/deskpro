<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use \Orb\Util\Strings;
use \Orb\Util\Arrays;

use Application\DeskPRO\App;

/**
 * Handles AJAX serving of client messages
 */
class ClientMessagesController extends AbstractController
{
	public function getNewMessagesAction()
	{
		// Automatically ping
		// AJAX clients dont send ping manually, it's just part of this call
		$this->person->loadHelper('ClientChannelSubscriptions', array('session' => $this->session));
		$this->person->getClientChannelSubs()->pingSubscriptions();

		// Not uint because -1 will be used when no messages have ever existed
		$since = $this->in->getInt('since');

		// if $since is 0, the client is new and asking for us to send it the last id
		if ($since == 0) {
			$data = array('messages' => array(), 'last_id' => -1);
			$last_id = App::getDb()->fetchColumn("SELECT id FROM client_messages ORDER BY id DESC LIMIT 1");
			if ($last_id) {
				$data['last_id'] = $last_id;
			}
			
		} else {

			$data = array();
			if ($since) {
				$data = array('messages' => array(), 'last_id' => -1);

				$all_messages = App::getEntityRepository('DeskPRO:ClientMessage')->getMessagesForClient($this->session->getEntityId(), $this->person['id'], $since);
				foreach ($all_messages as $message) {
					$handler = $message->getHandler();

					if ($message['created_by_client'] != $this->session->getEntityId()) {
						$data['messages'][] = array(
							$message['channel'],
							$handler->getMessage('ajax')
						);
					}

					if ($message['id'] > $data['last_id']) {
						$data['last_id'] = $message['id'];
					}
				}

				if ($data['last_id'] == -1) {
					unset($data['last_id']);
				}
			}
		}

		return $this->createJsonResponse($data);
	}

	public function pingSubscriptionsAction()
	{
		$this->person->loadHelper('ClientChannelSubscriptions', array('session' => $this->session));
		$this->person->getClientChannelSubs()->pingSubscriptions();

		$sub_channels = array();
		foreach ($subs as $sub) {
			$sub_channels[] = $sub['channel'];
		}

		return $this->createJsonResponse(array('channels' => $sub_channels));
	}

	public function subscribeChannelsAction()
	{
		$channels = $this->in->getCleanValueArray('channels', 'string', 'discard');

		$this->person->loadHelper('ClientChannelSubscriptions', array('session' => $this->session));
		$subs = $this->person->getClientChannelSubs()->subscribeChannels($channels);

		$names = array();
		foreach ($subs as $sub) {
			$names[] = $sub['channel'];
		}

		return $this->createJsonResponse(array('subscribed_channels' => $names));
	}

	public function unsubscribeChannelAction()
	{
		$channels = $this->in->getCleanValueArray('channels', 'string', 'discard');

		$this->person->loadHelper('ClientChannelSubscriptions', array('session' => $this->session));
		$subs = $this->person->getClientChannelSubs()->unsubscribeChannels($channels);

		$names = array();
		foreach ($subs as $sub) {
			$names[] = $sub['channel'];
		}

		return $this->createJsonResponse(array('unsubscribed_channels' => $names));
	}
}