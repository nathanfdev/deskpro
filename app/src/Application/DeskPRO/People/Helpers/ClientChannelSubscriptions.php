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
 * @category Entities
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
