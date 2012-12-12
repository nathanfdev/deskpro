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
 * @subpackage
 */

namespace Application\DeskPRO\Service;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TwitterAccount;
use Application\DeskPRO\Entity\TwitterAccountStatus;
use Application\DeskPRO\Entity\TwitterUser;
use Application\DeskPRO\Entity\TwitterStatus;
use Application\DeskPRO\Entity\TwitterStatusMention;
use Application\DeskPRO\Entity\TwitterStatusTag;
use Application\DeskPRO\Entity\TwitterStatusUrl;

class Twitter
{
	protected $_user_cache = array();
	protected $_tweet_cache = array();

	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	protected $em;

	public function __construct()
	{
		$this->em = App::getOrm();
	}

	/**
	 * Retrieve Twitter Application OAuth Consumer Key.
	 *
	 * @return string
	 */
	public static function getConsumerKey()
	{
		return 'XefO1lVIzumaZ2zLkntbQ';
	}

	/**
	 * Retrieve Twitter Application OAuth Consumer Secret.
	 *
	 * @return string
	 */
	public static function getConsumerSecret()
	{
		return 'OyJd8ocBXT5qjYpceo6nCbUIHz4ukekkrc27SpREG74';
	}

	public static function getTwitterApi($token = null, $secret = null)
	{
		$api = new \EpiTwitter(self::getConsumerKey(), self::getConsumerSecret());
		if ($token && $secret) {
			$api->setToken($token, $secret);
		}

		return $api;
	}

	/**
	 * @param integer $id
	 * @return \Application\DeskPRO\Entity\TwitterUser
	 */
	protected function findUser($id)
	{
		if (!array_key_exists($id, $this->_user_cache)) {
			$this->_user_cache[$id] = $this->em->getRepository('DeskPRO:TwitterUser')->find($id);
		}

		return $this->_user_cache[$id];
	}

	/**
	 * @param integer $twitter_status_id
	 *
	 * @return \Application\DeskPRO\Entity\TwitterStatus
	 */
	protected function findStatus($id)
	{
		if (!array_key_exists($id, $this->_tweet_cache)) {
			$this->_tweet_cache[$id] = $this->em->getRepository('DeskPRO:TwitterStatus')->find($id);
		}

		return $this->_tweet_cache[$id];
	}

	public function processStatus(\EpiTwitter $api, $data, $do_persist = true, $depth = 0)
	{
		$status = $this->findStatus($data->id_str);
		if ($status) {
			return $status;
		}

		$status = TwitterStatus::createFromJson($data);
		if ($do_persist) {
			$this->_tweet_cache[$data->id_str] = $status;
		}

		$user = $this->findUser($data->user->id_str);
		if (!$user) {
			$user = TwitterUser::createFromJson($data->user);
			if ($do_persist) {
				$this->em->persist($user);
				$this->_user_cache[$data->user->id_str] = $user;
			}
		}

		$status->user = $user;

		// retweet
		if (!empty($data->retweeted_status)) {
			// @todo process retweet entities
			$status['retweet'] = $this->processStatus($api, $data->retweeted_status);
			if ($do_persist) {
				$this->em->persist($status['retweet']);
			}
		}

		// reply
		if (!empty($data->in_reply_to_status_id_str)) {
			$reply = $this->findStatus($data->in_reply_to_status_id_str);
			if (!$reply) {
				try {
					// todo: defer and bulk fetch?
					$reply_result = $api->get_statusesShow(array(
						'id' => $data->in_reply_to_status_id_str,
						'include_entities' => true
					));

					if (!empty($reply_result->id_str)) {
						$reply = $this->processStatus($api, $reply_result, $do_persist, $depth + 1);
						if ($do_persist) {
							$this->em->persist($reply);
						}
					}
				} catch (\EpiTwitterException $e) {
					// likely, the status was private so we can't grab it
				}
			}

			if ($reply) {
				$status['in_reply_to_status'] = $reply;
				$status['in_reply_to_user'] = $reply->user;
			}
		}

		// fetch mentions
		if (isset($data->entities) && $depth <= 1) {
			foreach ($data->entities->user_mentions as $mention) {
				$status->addMention($this->processStatusMention($api, $status, $mention, $do_persist));
			}

			// fetch hashtags
			foreach ($data->entities->hashtags as $hashtag) {
				$status->addTag($this->processStatusTag($status, $hashtag, $do_persist));
			}

			// fetch urls
			foreach ($data->entities->urls as $url) {
				$status->addUrl($this->processStatusUrl($status, $url, $do_persist));
			}
		}

		if ($do_persist) {
			$this->em->persist($status);
		}

		return $status;
	}

	public function processDm(\EpiTwitter $api, $data, $do_persist = true)
	{
		$dm = !empty($data->direct_message) ? $data->direct_message : $data;

		$status = $this->findStatus($dm->id_str);
		if ($status) {
			return $status;
		}

		$status = TwitterStatus::createFromDmJson($dm);
		if ($do_persist) {
			$this->_tweet_cache[$dm->id_str] = $status;
		}

		$user = $this->findUser($dm->sender->id_str);
		if (!$user) {
			$user = TwitterUser::createFromJson($dm->sender);
			if ($do_persist) {
				$this->em->persist($user);
				$this->_user_cache[$dm->sender->id_str] = $user;
			}
		}

		$status->user = $user;

		$recipient = $this->findUser($dm->recipient->id_str);
		if (!$user) {
			$user = TwitterUser::createFromJson($dm->recipient);
			if ($do_persist) {
				$this->em->persist($user);
				$this->_user_cache[$dm->recipient->id_str] = $user;
			}
		}

		$status->recipient = $recipient;

		// fetch mentions
		if (isset($data->entities)) {
			foreach ($data->entities->user_mentions as $mention) {
				$status->addMention($this->processStatusMention($api, $status, $mention, $do_persist));
			}

			// fetch hashtags
			foreach ($data->entities->hashtags as $hashtag) {
				$status->addTag($this->processStatusTag($status, $hashtag, $do_persist));
			}

			// fetch urls
			foreach ($data->entities->urls as $url) {
				$status->addUrl($this->processStatusUrl($status, $url, $do_persist));
			}
		}

		if ($do_persist) {
			$this->em->persist($status);
		}

		return $status;
	}

	public function processStatusMention(\EpiTwitter $api, TwitterStatus $status, $mention, $do_persist = true)
	{
		$entity = TwitterStatusMention::createFromJson($mention);
		$entity['status'] = $status;

		$user = $this->findUser($mention->id_str);
		if (!$user) {
			$user = TwitterUser::createStub($mention->id_str);
			if ($do_persist) {
				$this->em->persist($user);
				$this->_user_cache[$mention->id_str] = $user;
			}
		}

		$entity['user'] = $user;

		if ($do_persist) {
			$this->em->persist($entity);
		}

		return $entity;
	}

	public function processStatusTag(TwitterStatus $status, $tag, $do_persist = true)
	{
		$entity = TwitterStatusTag::createFromJson($tag);
		$entity['status'] = $status;

		if ($do_persist) {
			$this->em->persist($entity);
		}

		return $entity;
	}

	public function processStatusUrl(TwitterStatus $status, $url, $do_persist = true)
	{
		$entity = TwitterStatusUrl::createFromJson($url);
		$entity['status'] = $status;

		if ($do_persist) {
			$this->em->persist($entity);
		}

		return $entity;
	}

	public function sendAccountMessage($type, $text, TwitterAccount $account, TwitterAccountStatus $reply = null, TwitterUser $user = null)
	{
		$success = false;
		$error = null;
		$new_account_status = null;

		$api = $account->getTwitterApi();
		$em = App::getOrm();

		try {
			if ($type == 'public') {
				if (\Orb\Util\Strings::utf8_strlen($text) > 140) {
					$error = 'Long statuses are todo'; // todo
				} else {
					$params = array(
						'status' => $text
					);
					if ($reply && !$reply->status->recipient) {
						// only if not a DM
						$params['in_reply_to_status_id'] = $reply->status->id;
					}

					$response = $api->post_statusesUpdate($params);
					if (!empty($response->error)) {
						$error = $response->error;
					} else {
						$success = true;

						$new_status = $this->processStatus($api, $response);

						$new_account_status = new TwitterAccountStatus();
						$new_account_status->status = $new_status;
						$new_account_status->account = $account;
						$new_account_status->status_type = 'sent';
						$new_account_status->in_reply_to = $reply;

						$em->persist($new_status);
						$em->persist($new_account_status);
						$em->flush();
					}
				}
			} else {
				if (\Orb\Util\Strings::utf8_strlen($text) <= 140) {
					if ($user) {
						$user_id = $user->id;
					} else if ($reply) {
						$user_id = $reply->status->user->id;
					} else {
						throw new \Exception('No user to send private message to.');
					}

					try {
						$response = $api->post_direct_messagesNew(array(
							'user_id' => $user_id,
							'text' => $text
						));
						if (!empty($response->error)) {
							$error = $response->error;
						} else {
							$success = true;

							$twitter_service = new \Application\DeskPRO\Service\Twitter();
							$new_status = $twitter_service->processDm($api, $response);

							$new_account_status = new TwitterAccountStatus();
							$new_account_status->status = $new_status;
							$new_account_status->account = $account;
							$new_account_status->status_type = 'direct';
							$new_account_status->in_reply_to = $reply;

							$em->persist($new_status);
							$em->persist($new_account_status);
							$em->flush();
						}
					} catch (\EpiTwitterException $e) {
						// user isn't following so we can't send a DM
					}
				}

				if (!$success && !$error) {
					$error = 'Non-DM private responses are TODO'; // todo
				}
			}
		} catch (\EpiTwitterException $e) {
			$error = $e->getMessage();
		}  catch (\EpiOAuthException $e) {
			$error = $e->getMessage();
		}

		return array(
			'success' => $success,
			'error' => $error,
			'new_account_status' => $new_account_status
		);
	}
}