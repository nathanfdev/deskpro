<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage WorkerProcess
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Log\Logger;

use \Application\DeskPRO\Entity\TwitterAccount;
use \Application\DeskPRO\Entity\TwitterAccountFriend;
use \Application\DeskPRO\Entity\TwitterAccountFollower;
use \Application\DeskPRO\Entity\TwitterStatus;
use \Application\DeskPRO\Entity\TwitterStatusMention;
use \Application\DeskPRO\Entity\TwitterStatusTag;
use \Application\DeskPRO\Entity\TwitterStatusUrl;
use \Application\DeskPRO\Entity\TwitterUser;
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
			WHERE account_id IS NOT NULL
			ORDER BY date_created ASC
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
				$success = false;
			}

			if (true === $success) {
				$this->db->delete('twitter_stream', array(
					'id' => $event['id']
				));

				$processed++;
			}
		}

		// $this->logStatus('processed events: '.$processed);
	}

	/**
	 * @param integer $id
	 * @return \Zend_Service_Twitter
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
		if ($this->findStatus($data['id_str'])) {
			return false;
		}

		if (!($user = $this->findUser($data['user']['id_str']))) {
			$user = TwitterUser::createFromJson($data['user']);
			$this->em->persist($user);
		}

		$status = TwitterStatus::createFromJson($data);
		$status['user'] = $user;
		$this->em->persist($status);

		// fetch mentions
		foreach ($data['entities']['user_mentions'] as $mention) {
			$mention = $this->processStatusMention($account, $status, $mention);
		}

		// fetch hashtags
		foreach ($data['entities']['hashtags'] as $hashtag) {
			$tag = $this->processStatusTag($status, $hashtag);
		}

		// fetch urls
		foreach ($data['entities']['urls'] as $url) {
			$url = $this->processStatusUrl($status, $url);
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
		return false;
	}
}
