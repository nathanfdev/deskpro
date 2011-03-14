<?php

namespace Orb\Service\Phirehose\Consumer;

use \Doctrine\ORM\EntityManager;

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
			echo 'TWEET ADDED W/ ID: '.$status['id_str'].PHP_EOL;
			return $this->processStatus($status);
		}

		// check friend list
		if (isset($status['friends'])) {
			return $this->processFriends($status['friends']);
		}

		if (isset($status['delete'])) {
			return $this->processDeletion($status['delete']);
		}

		// @TODO add elsewhat handling
		echo 'SOMETHING ELSE, LOOK:'.PHP_EOL;
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
		// check if Twitter status exists
		if ($this->findStatus($tweet['id_str'])) {
			return true;
		}

		// fetch Twitter user
		$user = $this->findUser($tweet['user']['id_str']);
		if (!$user) {
			// create user entity
			$user = \Orb\Service\Twitter\User::createEntityFromJson($tweet['user']);

			// persist entity
			$this->em->persist($user);
		}

		// create Twitter status
		$status                 = new TwitterStatus();
		$status['id']           = $tweet['id_str'];
		$status['user']         = $user;
		$status['text']         = $tweet['text'];
		$status['is_truncated'] = $tweet['truncated'];
		$status['is_favorited'] = $tweet['favorited'];
		$status['is_archived']  = false;
		$status['date_created'] = new \DateTime($tweet['created_at']);
		$status['source']       = $tweet['source'];

		// @TODO add geo informations
		// $status['geo_latitude'] = $tweet['geo'][];
		// $status['geo_longitude'] = $tweet['geo'][];

		// persist entity
		$this->em->persist($status);

		// flush changes
		$this->em->flush();

		// @TODO add in_reply_* handling

		// fetch urls
		foreach ($tweet['entities']['urls'] as $url) {
			$this->processUrl($status, $url);
		}

		// flush changes
		$this->em->flush();

		// fetch hashtags
		foreach ($tweet['entities']['hashtags'] as $hashtag) {
			$this->processTag($status, $hashtag);
		}

		// flush changes
		$this->em->flush();

		// fetch mentions
		foreach ($tweet['entities']['user_mentions'] as $mention) {
			$this->processMention($status, $mention);
		}

		// flush changes
		$this->em->flush();
	}

	/**
	 * @param \Application\DeskPRO\Entity\TwitterStatus $status
	 * @param array $url
	 * @return \Application\DeskPRO\Entity\TwitterStatusUrl
	 */
	protected function processUrl(TwitterStatus $status, array $url)
	{
		$entity           = new TwitterStatusUrl();
		$entity['status'] = $status;
		$entity['url']    = $url['url'];
		$entity['starts'] = $url['indices'][0];
		$entity['ends']   = $url['indices'][1];

		// persist entity
		$this->em->persist($entity);

		return $entity;
	}

	/**
	 * @param \Application\DeskPRO\Entity\TwitterStatus $status
	 * @param array $hashtag
	 * @return \Application\DeskPRO\Entity\TwitterStatusTag
	 */
	protected function processTag(TwitterStatus $status, array $tag)
	{
		$entity           = new TwitterStatusTag();
		$entity['status'] = $status;
		$entity['hash']   = $tag['text'];
		$entity['starts'] = $tag['indices'][0];
		$entity['ends']   = $tag['indices'][1];

		// persist entity
		$this->em->persist($entity);

		return $entity;
	}

	/**
	 * @param \Application\DeskPRO\Entity\TwitterStatus $status
	 * @param array $hashtag
	 * @return \Application\DeskPRO\Entity\TwitterStatusMention
	 */
	protected function processMention(TwitterStatus $status, array $mention)
	{
		$entity           = new TwitterStatusMention();
		$entity['status'] = $status;

		// @TODO check if Twitter user exists
		$user = $this->findUser($mention['id_str']);
		if (!$user) {
			// @TODO check if we need an access token
			$twitter = new \Zend_Service_Twitter(array(), \Orb\Service\Twitter\Oauth::getConsumer(true));

			// fetch user data
			// @TODO check limit for API calls / hour
			$xml = $twitter->user->show($mention['id_str']);

			// create user entity
			$user = \Orb\Service\Twitter\User::createEntityFromXML($xml);

			// persist entity
			$this->em->persist($entity);
		}

		$entity['user']   = $user;
		$entity['starts'] = $mention['indices'][0];
		$entity['ends']   = $mention['indices'][1];

		// persist entity
		$this->em->persist($entity);

		return $entity;
	}

	/**
	 * Process friend list (list of Twitter users following).
	 *
	 * @param array $friendIds List of Twitter users following
	 * @return Boolean
	 */
	protected function processFriends(array $friendIds)
	{
		return true;
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

			$this->em->delete($status);
			$this->em->flush();

			return true;
		}
	}
}
