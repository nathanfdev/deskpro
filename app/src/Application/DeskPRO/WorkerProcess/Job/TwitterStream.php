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
 * @subpackage WorkerProcess
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\Logger;

use Application\DeskPRO\Entity\TwitterAccount;
use Application\DeskPRO\Entity\TwitterAccountFriend;
use Application\DeskPRO\Entity\TwitterAccountFollower;
use Application\DeskPRO\Entity\TwitterStatus;
use Application\DeskPRO\Entity\TwitterStatusMention;
use Application\DeskPRO\Entity\TwitterStatusTag;
use Application\DeskPRO\Entity\TwitterStatusUrl;
use Application\DeskPRO\Entity\TwitterUser;
/**
 * Processes Twitter stream events.
 */
class TwitterStream extends AbstractJob
{
	const DEFAULT_INTERVAL = 10;

	/**
	 * @todo make benchmarks and adjust
	 */
	const EVENT_LIMIT = 50;

	protected $em;
	protected $db;

	protected $accounts = array();
	protected $twitter = array();

	public function run()
	{
		$this->db = App::getDb();
		$this->em = App::getOrm();

		$events = $this->db->fetchAll(sprintf("
			SELECT *
			FROM twitter_stream
			WHERE account_id IS NOT NULL AND event != 'unknown'
			ORDER BY date_created ASC, id ASC
			LIMIT %d
		", self::EVENT_LIMIT));

		$processed = 0;
		foreach ($events as $event) {
			$method = 'process'.ucfirst($event['event']);
			if (!method_exists($this, $method)) {
				// $this->logStatus('unknown event type', $event);
				continue;
			}

			try {
				$success = call_user_func(
					array($this, $method),
					$this->getAccount($event['account_id']),
					unserialize($event['data'])
				);
			} catch (\Exception $e) {
				echo $e->getMessage().PHP_EOL;
				// $this->logStatus('exception catched', $e);
				$success = false;
			}

		//	$this->em->clear();

			if (true === $success) {
				$this->db->delete('twitter_stream', array(
					'id' => $event['id']
				));

				$processed++;
			}
		}

		// $this->logStatus('processed events', $processed);
	}

	/**
	 * @param integer $id
	 * @return \Zend\Service\Twitter
	 */
	protected function getTwitter($id)
	{
		if (!isset($this->twitter[$id])) {
			$this->twitter[$id] = \Orb\Service\Twitter\Twitter::getTwitterService(
				$this->getAccount($id)->getOauthAccessToken()
			);
		}

		return $this->twitter[$id];
	}

	/**
	 * @param integer $id
	 * @return \Application\DeskPRO\Entity\TwitterAccount
	 */
	protected function getAccount($id)
	{
		if (!isset($this->accounts[$id])) {
			$this->accounts[$id] = $this->em->getRepository('DeskPRO:TwitterAccount')->find($id);
		}

		return $this->accounts[$id];
	}

	/**
	 * @param integer $id
	 * @return \Application\DeskPRO\Entity\TwitterStatus
	 */
	protected function findStatus($id)
	{
		return $this->em->getRepository('DeskPRO:TwitterStatus')->find($id);
	}

	/**
	 * @param integer $id
	 * @return \Application\DeskPRO\Entity\TwitterUser
	 */
	protected function findUser($id)
	{
		return $this->em->getRepository('DeskPRO:TwitterUser')->find($id);
	}

	/**
	 * @param \Application\DeskPRO\Entity\TwitterAccount $account
	 * @param array $data
	 * @return Boolean
	 *
	 * @todo add in_reply_* handling
	 */
	protected function processStatus(TwitterAccount $account, array $data)
	{
		// event could be removed if status exists
		if ($this->findStatus($data['id_str'])) {
			return true;
		}

		if (!($user = $this->findUser($data['user']['id_str']))) {
			$user = TwitterUser::createFromJson($data['user']);
			$this->em->persist($user);
		}

		$status = TwitterStatus::createFromJson($data);
		$status['user'] = $user;
		$this->em->persist($status);

		// retweet
		if (isset($data['retweeted_status'])) {
			if (!$this->processStatus($account, $data['retweeted_status'])) {
				return false;
			}

			if (!($retweet = $this->findStatus($data['retweeted_status']['id_str']))) {
				return false;
			}

			$status['retweet'] = $retweet;
			$this->em->persist($retweet);

			// @todo process retweet entities
		}

		// reply
		if (null !== $data['in_reply_to_status_id_str']) {
			if (!($reply = $this->findStatus($data['in_reply_to_status_id_str']))) {
				$replyXml = $this->getTwitter($account['id'])->status->show(
					$data['in_reply_to_status_id_str'],
					array('include_entities' => true)
				);

				if (!($replyUser = $this->findUser((string) $replyXml->user->id))) {
					$replyUser = TwitterUser::createFromXML($replyXml->user);
					$this->em->persist($replyUser);
				}

				$reply = TwitterStatus::createFromXML($replyXml);
				$reply['user'] = $replyUser;
				$this->em->persist($reply);

				// @todo process reply entities
			}

			$status['in_reply_to_status'] = $reply;
			$status['in_reply_to_user'] = $user;
		}

		// fetch mentions
		if (isset($data['entities'])) {
			foreach ($data['entities']['user_mentions'] as $mention) {
				$this->processStatusMention($account, $status, $mention);
			}

			// fetch hashtags
			foreach ($data['entities']['hashtags'] as $hashtag) {
				$this->processStatusTag($status, $hashtag);
			}

			// fetch urls
			foreach ($data['entities']['urls'] as $url) {
				$this->processStatusUrl($status, $url);
			}
		}

		$this->em->flush();

		return true;
	}

	/**
	 * @param \Application\DeskPRO\Entity\TwitterAccount $account
	 * @param \Application\DeskPRO\Entity\TwitterStatus $status
	 * @param array $mention
	 * @return \Application\DeskPRO\Entity\TwitterStatusMention
	 */
	protected function processStatusMention(TwitterAccount $account, TwitterStatus $status, array $mention)
	{
		$entity = TwitterStatusMention::createFromJson($mention);
		$entity['status'] = $status;

		if (!($user = $this->findUser($mention['id_str']))) {
			$xml = $this->getTwitter($account['id'])->user->show($mention['id_str']);
			$user = TwitterUser::createFromXML($xml);
			$this->em->persist($user);
		}

		$entity['user'] = $user;
		$this->em->persist($entity);

		return $entity;
	}

	/**
	 * @param \Application\DeskPRO\Entity\TwitterStatus $status
	 * @param array $tag
	 * @return \Application\DeskPRO\Entity\TwitterStatusTag
	 */
	protected function processStatusTag(TwitterStatus $status, array $tag)
	{
		$entity = TwitterStatusTag::createFromJson($tag);
		$entity['status'] = $status;
		$this->em->persist($entity);

		return $entity;
	}


	/**
	 * @param \Application\DeskPRO\Entity\TwitterStatus $status
	 * @param array $url
	 * @return \Application\DeskPRO\Entity\TwitterStatusUrl
	 */
	protected function processStatusUrl(TwitterStatus $status, array $url)
	{
		$entity = TwitterStatusUrl::createFromJson($url);
		$entity['status'] = $status;
		$this->em->persist($entity);

		return $entity;
	}

	protected function processMessage(TwitterAccount $account, array $data)
	{
		return false;
	}

	protected function processEvent(TwitterAccount $account, array $data)
	{
		$source = $data['source'];
		$target = $data['target'];

		$targetObject = $data['target_object'];

		$eventType = $data['event'];
		$createdAt = $data['created_at'];

		// Check source user exists
		if (!($user = $this->findUser($source['id_str']))) {
			$xml = $this->getTwitter($account['id'])->user->show($source['id_str']);
			$user = TwitterUser::createFromXML($xml);
			$this->em->persist($user);
		}

		// Check target user exists
		if (!($user = $this->findUser($target['id_str']))) {
			$xml = $this->getTwitter($account['id'])->user->show($target['id_str']);
			$user = TwitterUser::createFromXML($xml);
			$this->em->persist($user);
		}

		// Get the status, or create it
		$status = $this->findStatus($targetObject['id_str']);
		if (!$status) {
			$this->processStatus($account, $targetObject);
			$status = $this->findStatus($targetObject);
		}

		switch ($eventType) {
			// Process a favorite
			case 'favorite':
				$status->setIsFavorited(true);
				$this->em->persist($status);
				return true;
				break;
		}

		return false;
	}

	protected function processFriends(TwitterAccount $account, array $data)
	{
		if (!count($data['friends'])) {
			return true;
		}

		$diff = array_diff(array_unique($data['friends']), $account->getFriendIds(false));
		foreach ($diff as $id) {
			if (!($user = $this->findUser($id))) {
				$user = TwitterUser::createFromXML($this->getTwitter($account['id'])->user->show($id));
				$this->em->persist($user);
			}

			$friend = new TwitterAccountFriend();
			$friend['account'] = $account;
			$friend['user'] = $user;
			$this->em->persist($friend);
			$this->em->flush();
		}

		return true;
	}

	protected function processDelete(TwitterAccount $account, array $data)
	{
		if (isset($data['delete']['status'])) {
			return $this->processDeleteStatus($data['delete']['status']);
		}

		return false;
	}

	protected function processDeleteStatus(array $data)
	{
		// event could be removed if status does not exist
		if (!($status = $this->findStatus($data['id_str']))) {
			return true;
		}

		// delete long status
		if ($status['long']) {
			$this->em->remove($status['long']);
		}

		// delete notes
		if ($status['notes']->count()) {
			foreach ($status['notes'] as $tag) {
				$this->em->remove($tag);
			}
		}

		// delete mentions
		if ($status['mentions']->count()) {
			foreach ($status['mentions'] as $mention) {
				$this->em->remove($mention);
			}
		}

		// delete URLs
		if ($status['urls']->count()) {
			foreach ($status['urls'] as $url) {
				$this->em->remove($url);
			}
		}

		// delete tags
		if ($status['tags']->count()) {
			foreach ($status['tags'] as $tag) {
				$this->em->remove($tag);
			}
		}

		$this->em->remove($status);
		$this->em->flush();

		return true;
	}
}
