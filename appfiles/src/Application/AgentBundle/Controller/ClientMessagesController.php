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

use Orb\Util\Strings;
use Orb\Util\Arrays;

use Application\DeskPRO\App;

/**
 * Handles AJAX serving of client messages
 */
class ClientMessagesController extends AbstractController
{
	public function getNewMessagesAction()
	{
		$new_since = $this->in->getUint('since');
		$last_since = $this->person->getPref('agent.ui.last_message_id');

		$data = App::getEntityRepository('DeskPRO:ClientMessage')->getMessageData(
			$this->person,
			$this->session,
			$new_since,
			($this->in->getBool('is_initial_poll') ? $last_since : null)
		);

		// We inject a rendered view for new chats, so loop through the messages to do that
		foreach ($data['messages'] as &$item) {
			$channel = $item[1];
			if ($channel == 'chat.new') {
				$cid = $item[2]['conversation_id'];
				$item[2]['html'] = $this->forward('AgentBundle:UserChat:getChatAlert', array('id' => $cid))->getContent();
			}
		}

		// We save the last message we know a user got because we need to know
		// to deliver offline messages (such as chats) the next time the user logs in
		if ($new_since && $new_since > $last_since) {
			$pref = $this->person->setPreference('agent.ui.last_message_id', $new_since);
			$this->em->persist($pref);
			$this->em->flush();
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

	public function unsubscribeChannelsAction()
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
