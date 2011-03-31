<?php

namespace Orb\Service\Phirehose\Consumer;

use \Doctrine\ORM\EntityManager;

use \Application\DeskPRO\Entity\TwitterAccountFollowing;
use \Application\DeskPRO\Entity\TwitterAccountFollower;
use \Application\DeskPRO\Entity\TwitterStatus;
use \Application\DeskPRO\Entity\TwitterStatusMention;
use \Application\DeskPRO\Entity\TwitterStatusTag;
use \Application\DeskPRO\Entity\TwitterStatusUrl;
use \Application\DeskPRO\Entity\TwitterUser;

/**
 * Concrete Twitter API User Stream consuming class.
 *
 * @author Pierre Minnieur <pm@pierre-minnieur.de>
 */
class UserStream extends \UserstreamPhirehose
{
	/**
	 * @var \Application\DeskPRO\Entity\TwitterAccount
	 */
	protected $account;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var array
	 */
	protected $log = array();

	/**
	 * Suppress Phirehose @error_log output.
	 *
	 * @param string $message
	 * @return void
	 */
	protected function log($message)
	{
		$this->log[] = $message;
	}

	/**
	 * Retrieve the Doctrine EntityManager instance.
	 *
	 * @return \Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->em;
	}

	/**
	 * Inject the Doctrine EntityManager instance.
	 *
	 * @param \Doctrine\ORM\EntityManager $em
	 * @return void
	 */
	public function setEntityManager(\Doctrine\ORM\EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * Retrieve the Twitter account  instance.
	 *
	 * @return \Application\DeskPRO\Entity\TwitterAccount
	 */
	public function getTwitterAccount()
	{
		return $this->account;
	}

	/**
	 * Inject the Twitter account instance.
	 *
	 * @param \Application\DeskPRO\Entity\TwitterAccount $account
	 * @return void
	 */
	public function setTwitterAccount(\Application\DeskPRO\Entity\TwitterAccount $account)
	{
		$this->account = $account;
	}

	/**
	 * @return \Zend_Service_Twitter
	 */
	public function getTwitterService()
	{
		return \Orb\Service\Twitter\Twitter::getTwitterService(
			$this->getTwitterAccount()->getOauthAccessToken()
		);
	}

	/**
	 * Process raw streaming data.
	 *
	 * @param string $status
	 * @return void
	 */
	public function enqueueStatus($status) {
		// skip "ping -> pong"
		$status = trim($status);
		if (!strlen($status)) {
			return;
		}

		// decode json
		$status = json_decode($status, true);

		// check if status is a tweet
		if (isset($status['text'])) {
			return $this->processStatus($status);
		}

		// check direct message
		if (isset($status['direct_message'])) {
			return $this->processDirectMessage($status['direct_message']);
		}

		// check event
		if (isset($status['event'])) {
			return $this->processEvent($status);
		}

		// check friend list
		if (isset($status['friends'])) {
			return $this->processFriends($status['friends']);
		}

		if (isset($status['delete'])) {
			return $this->processDeletion($status['delete']);
		}

		// @TODO add elsewhat handling
		echo 'UNKNOWN STATUS, LOOK:'.PHP_EOL;
		print_r($status); echo PHP_EOL.PHP_EOL;
	}

	/**
	 * Retrieve a Twitter status.
	 *
	 * @param integer|string $id
	 * @return null|\Application\DeskPRO\Entity\TwitterStatus
	 */
	protected function findStatus($id)
	{
		return $this->em->getRepository('DeskPRO:TwitterStatus')->find($id);
	}

	/**
	 * Retrieve a Twitter user.
	 *
	 * @param integer|string $id
	 * @return null|\Application\DeskPRO\Entity\TwitterUser
	 */
	protected function findUser($id)
	{
		return $this->em->getRepository('DeskPRO:TwitterUser')->find($id);
	}

	/**
	 * Process a new Twitter status.
	 *
	 * @param array $tweet The Twitter status
	 * @return Boolean
	 */
	protected function processStatus(array $tweet)
	{
		// skip if Twitter status exists
		if ($this->findStatus($tweet['id_str'])) {
			return false;
		}

		// fetch Twitter user
		$user = $this->findUser($tweet['user']['id_str']);
		if (!$user) {
			// create user entity
			$user = TwitterUser::createFromJson($tweet['user']);
			$this->em->persist($user);
			$this->em->flush();
		}

		// create Twitter status
		$status = TwitterStatus::createFromJson($tweet);
		$status['user'] = $user;
		$this->em->persist($status);
		$this->em->flush();

		// @TODO add in_reply_* handling

		// fetch urls
		foreach ($tweet['entities']['urls'] as $url) {
			$this->processUrl($status, $url);
		}

		// fetch hashtags
		foreach ($tweet['entities']['hashtags'] as $hashtag) {
			$this->processTag($status, $hashtag);
		}

		// fetch mentions
		foreach ($tweet['entities']['user_mentions'] as $mention) {
			$this->processMention($status, $mention);
		}

		return true;
	}

	/**
	 * @param \Application\DeskPRO\Entity\TwitterStatus $status
	 * @param array $url
	 * @return \Application\DeskPRO\Entity\TwitterStatusUrl
	 */
	protected function processUrl(TwitterStatus $status, array $url)
	{
		$entity = TwitterStatusUrl::createFromJson($url);
		$entity['status'] = $status;
		$this->em->persist($entity);
		$this->em->flush();

		return $entity;
	}

	/**
	 * @param \Application\DeskPRO\Entity\TwitterStatus $status
	 * @param array $hashtag
	 * @return \Application\DeskPRO\Entity\TwitterStatusTag
	 */
	protected function processTag(TwitterStatus $status, array $tag)
	{
		$entity = TwitterStatusTag::createFromJson($tag);
		$entity['status'] = $status;
		$this->em->persist($entity);
		$this->em->flush();

		return $entity;
	}

	/**
	 * @param \Application\DeskPRO\Entity\TwitterStatus $status
	 * @param array $hashtag
	 * @return \Application\DeskPRO\Entity\TwitterStatusMention
	 */
	protected function processMention(TwitterStatus $status, array $mention)
	{
		$entity = TwitterStatusMention::createFromJson($mention);
		$entity['status'] = $status;

		// @TODO check if Twitter user exists
		$user = $this->findUser($mention['id_str']);
		if (!$user) {
			// @TODO check limit for API calls / hour
			// create user entity
			$user = TwitterUser::createFromXML($this->getTwitterService()->user->show($mention['id_str']));
			$this->em->persist($user);
			$this->em->flush();
		}

		$entity['user'] = $user;
		$this->em->persist($entity);
		$this->em->flush();

		return $entity;
	}

	/**
	 * Process direct message.
	 *
	 * @param array $message
	 * @param array $hashtag
	 * @return \Application\DeskPRO\Entity\TwitterStatus
	 */
	protected function processDirectMessage(array $message)
	{
		// check if Twitter status exists
		if ($this->findStatus($message['id_str'])) {
			return false;
		}

		// fetch sending Twitter user (sender)
		$sender = $this->findUser($message['sender']['id_str']);
		if (!$sender) {
			// create user entity
			$sender = TwitterUser::createFromJson($message['sender']);
			$this->em->persist($sender);
			$this->em->flush();
		}

		// fetch retrieving Twitter user (recipient)
		$recipient = $this->findUser($message['recipient']['id_str']);
		if (!$recipient) {
			// create user entity
			$recipient = TwitterUser::createFromJson($message['recipient']);
			$this->em->persist($recipient);
			$this->em->flush();
		}

		// create Twitter status
		$status = TwitterStatus::createFromJson($tweet);
		$status['user'] = $sender;
		$status['recipient'] = $recipient;

		// @TODO check if direct messages can have entities (Mentions, URLs, Tags)

		$this->em->persist($status);
		$this->em->flush();

		return true;
	}

	/**
	 * Process event.
	 *
	 * @param array $event
	 * @return mixed
	 */
	protected function processEvent(array $event)
	{
		switch ($event['event']) {
			case 'follow':
				return $this->processFollowEvent($event);
		}

		echo 'UNKNOWN EVENT, LOOK:'.PHP_EOL;
		print_r($event); echo PHP_EOL.PHP_EOL;

		return false;
	}

	/**
	 * Process 'follow' event.
	 *
	 * @param array $event
	 * @return Boolean
	 * @fixme logic needs some revisiting, it seems to not work properly
	 *        followers table is filled twice, following table is not touched.
	 *        maybe parameters must be replaced or something like that.
	 */
	protected function processFollowEvent(array $event)
	{
		// target['following'] => 1 | means target is followed by source
		// source['following'] => 1 | means source is followed by target, also

		// track process
		$processed = false;

		// fetch followed Twitter user (target)
		$target     = $event['target'];
		$targetUser = $this->findUser($target['id_str']);
		if (!$targetUser) {
			// create user entity
			$targetUser = TwitterUser::createFromJson($target);
			$this->em->persist($targetUser);
			$this->em->flush();

			// track process
			$processed = true;
		}

		// fetch following Twitter user (source)
		$source     = $event['source'];
		$sourceUser = $this->findUser($source['id_str']);
		if (!$sourceUser) {
			// create user entity
			$sourceUser = TwitterUser::createFromJson($source);
			$this->em->persist($sourceUser);
			$this->em->flush();

			// track process
			$processed = true;
		}

		// check if is target is followed by source
		if (1 == $target['following']) {
			// check if target user is a registered account
			$targetAccount = $this->em->getRepository('DeskPRO:TwitterAccount')
				->findOneByUser($targetUser['id']);

			if ($targetAccount) {
				// check if target is already followed
				$targetFollowing = $this->em->getRepository('DeskPRO:TwitterAccountFollower')
					->findOneByAccountIdAndUserId($targetAccount['id'], $sourceUser['id']);

				if (!$targetFollowing) {
					$targetFollowing            = new TwitterAccountFollower();
					$targetFollowing['account'] = $targetAccount;
					$targetFollowing['user']    = $sourceUser;
					$this->em->persist($targetFollowing);
					$this->em->flush();

					// track process
					$processed = true;
				}
			}
		}

		// check if is source is followed by target
		if (1 == $source['following']) {
			// check if source user is a registered account
			$sourceAccount = $this->em->getRepository('DeskPRO:TwitterAccount')
				->findOneByUser($sourceUser['id']);

			if ($sourceAccount) {
				// check if source is already followed
				$sourceFollowing = $this->em->getRepository('DeskPRO:TwitterAccountFollowing')
					->findOneByAccountIdAndUserId($sourceAccount['id'], $targetUser['id']);

				if (!$sourceFollowing) {
					$sourceFollowing            = new TwitterAccountFollowing();
					$sourceFollowing['account'] = $sourceAccount;
					$sourceFollowing['user']    = $targetUser;
					$this->em->persist($sourceFollowing);
					$this->em->flush();

					// track process
					$processed = true;
				}
			}
		}

		return $processed;
	}

	/**
	 * Process friend list (list of Twitter users following).
	 *
	 * @param array $friendIds List of Twitter users following
	 * @return Boolean
	 */
	protected function processFriends(array $friendIds)
	{
		return false;
	}

	/**
	 * Process a deletion.
	 *
	 * @param array $deletion Data of deletion
	 * @return Boolean
	 */
	protected function processDeletion(array $deletion)
	{
		// delete a Twitter status
		if (isset($deletion['status'])) {
			$status = $this->findStatus($deletion['status']['id_str']);
			if (!$status) {
				return false;
			}

			// delete mentions
			if ($status['mentions']->count()) {
				foreach ($status['mentions'] as $mention) {
					$this->em->remove($mention);
				}

				$this->em->flush();
			}

			// delete URLs
			if ($status['urls']->count()) {
				foreach ($status['urls'] as $url) {
					$this->em->remove($url);
				}

				$this->em->flush();
			}

			// delete tags
			if ($status['tags']->count()) {
				foreach ($status['tags'] as $tag) {
					$this->em->remove($tag);
				}

				$this->em->flush();
			}

			$this->em->remove($status);
			$this->em->flush();

			return true;
		}

		echo 'UNKNOWN DELETION, LOOK:'.PHP_EOL;
		print_r($deletion); echo PHP_EOL.PHP_EOL;

		return false;
	}
}
