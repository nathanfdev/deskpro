<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People\Helpers;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Works with client subscriptions
 */
class ClientChannelSubscriptions implements \Orb\Helper\ShortCallableInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @var \Application\DeskPRO\Entity\Session
	 */
	protected $session;

	public function __construct(Entity\Person $person, array $options = array())
	{
		$this->person = $person;
		$this->session = $options['session'];
	}

	public function getShortCallableNames()
	{
		return array(
			'getClientChannelSubs' => '_getthis',
		);
	}

	// we use this because we implement arrayaccess
	// so the caller gets this, and can use it as an array.
	// So if the caller gets it through a another array access, it means
	// we support $whatever['thishelper']['thisobject'];
	public function _getthis() { return $this; }



	/**
	 * Get all of the active subscriptions
	 *
	 * @return array
	 */
	public function getSubscriptions()
	{
		$subs = App::getEntityRepository('DeskPRO:ClientChannelSubscription')->getSubscriptionsForClient($this->session->getEntityId());

		return $subs;
	}



	/**
	 * Ping all subscriptions to mark them as 'active'
	 *
	 * @return bool
	 */
	public function pingSubscriptions()
	{
		$subs = App::getEntityRepository('DeskPRO:ClientChannelSubscription')->getSubscriptionsForClient($this->session->getEntityId());

		App::getOrm()->beginTransaction();

		foreach ($subs as $sub) {
			$sub->updatePingTime();
			App::getOrm()->persist($sub);
		}

		App::getOrm()->flush();
		App::getOrm()->commit();

		return true;
	}



	/**
	 * Subscribe the client to a channel or channels.
	 *
	 * @param string|array $channel A channel or array of channels to subscribe to
	 * @return Entity\ClientChannelSubscription The new subscriptions
	 */
	public function subscribeChannels($channels)
	{
		$single = false;
		if (!is_array($channels)) {
			$single = true;
			$channels = (array)$channels;
		}

		App::getOrm()->beginTransaction();

		$subs = array();
		foreach ($channels as $channel) {
			$sub = App::getEntityRepository('DeskPRO:ClientChannelSubscription')->findSubscriptionForClient($channel, $this->session->getEntityId());
			if (!$sub) {
				$sub = new Entity\ClientChannelSubscription();
			}

			$sub->updatePingTime();
			$sub['channel'] = $channel;
			$sub['session_id'] = $this->session->getEntityId();

			App::getOrm()->persist($sub);

			$subs[] = $sub;
		}

		App::getOrm()->flush();
		App::getOrm()->commit();

		// If we were given only one (not an array), then
		// return the one instead of an array
		if ($single) {
			return $subs[0];
		}

		return $subs;
	}



	/**
	 * Unsubscribe the client from a channel or channels.
	 *
	 * @param string|array $channel A channel or array of channels to subscribe to
	 * @return Entity\ClientChannelSubscription The removed subscriptions
	 */
	public function unsubscribeChannels($channels)
	{
		$single = false;
		if (!is_array($channels)) {
			$single = true;
			$channels = (array)$channels;
		}

		App::getOrm()->beginTransaction();

		$subs = array();
		foreach ($channels as $channel) {
			$sub = App::getEntityRepository('DeskPRO:ClientChannelSubscription')->findSubscriptionForClient($channel, $this->session->getEntityId());
			if (!$sub) continue;

			App::getOrm()->remove($sub);

			$subs[] = $sub;
		}

		App::getOrm()->flush();
		App::getOrm()->commit();

		// If we were given only one (not an array), then
		// return the one instead of an array
		if ($single) {
			return $subs[0];
		}

		return $subs;
	}
}
