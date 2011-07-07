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
		$data = App::getEntityRepository('DeskPRO:ClientMessage')->getMessageData(
			$this->person,
			$this->session,
			$this->in->getUint('since')
		);

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