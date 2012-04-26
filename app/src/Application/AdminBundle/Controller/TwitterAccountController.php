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
 * @subpackage AdminBundle
 */

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;

use Application\DeskPRO\Entity\TwitterAccount;
use Application\DeskPRO\Entity\TwitterAccountFriend;
use Application\DeskPRO\Entity\TwitterAccountFollower;
use Application\DeskPRO\Entity\TwitterStatus;
use Application\DeskPRO\Entity\TwitterStatusMention;
use Application\DeskPRO\Entity\TwitterStatusTag;
use Application\DeskPRO\Entity\TwitterStatusUrl;
use Application\DeskPRO\Entity\TwitterUser;

use Orb\Service\Twitter\Oauth,
	\Orb\Service\Twitter\Twitter;

use Application\AdminBundle\Form\EditTwitterAccountType;

/**
 * Handles creating/editing of Twitter Accounts
 */
class TwitterAccountController extends AbstractController
{
	const TWITTER_REQUEST_TOKEN = 'twitter_request_token';

	/**
	 * @var \Orb\Service\Twitter\Twitter
	 */
	protected $twitter;

	/**
	 * List of Twitter accounts.
	 *
	 * @return Symfony\Component\HttpFoundation\Response
	 */
	public function listAction()
	{
		$accounts = App::getORM()->getRepository('DeskPRO:TwitterAccount')->findAll();
		$verified = array();
		foreach ($accounts as $account) {
			$credentials = Twitter::getTwitterService($account->getOauthAccessToken())
				->account->verifyCredentials();

			$verified[$account['id']] = !isset($credentials->error);
		}

		return $this->render('AdminBundle:TwitterAccount:list.html.twig', array(
			'accounts' => $accounts,
			'verified' => $verified,
		));
	}

	/**
	 * @return \Zend\Oauth\Consumer
	 */
	protected function getConsumer()
	{
		$callbackUrl = $this->generateUrl('admin_twitter_accounts_authorize', array(), true);
		$consumer	= \Orb\Service\Twitter\Oauth::getConsumer($callbackUrl);

		return $consumer;
	}

	/**
	 * Request permission from Twitter for DeskPRO application.
	 *
	 * @return Symfony\Component\HttpFoundation\Response
	 */
	public function newAction()
	{
		// generate request token
		$consumer	 = $this->getConsumer();
		$requestToken = $consumer->getRequestToken();

		// store request token in session
		$this->session->set(self::TWITTER_REQUEST_TOKEN, serialize($requestToken));

		// redirect to Twitter authorization page
		return $this->redirect($consumer->getRedirectUrl());
	}

	/**
	 * Callback from Twitter if authentication was granted.
	 *
	 * @return Symfony\Component\HttpFoundation\Response
	 */
	public function authorizeAction()
	{
		try {
			// get OAuth access token
			$consumer = $this->getConsumer();
			$accessToken = $consumer->getAccessToken(
				$this->request->query->all(),
				unserialize($this->session->get(self::TWITTER_REQUEST_TOKEN))
			);

			// initialize Twitter service
			$this->twitter = Twitter::getTwitterService($accessToken, $consumer);

			// check if Twitter user already exists
			$twitterUser = $this->twitter->user->show($accessToken->getParam('screen_name'));
			$user = $this->getOrCreateUser($twitterUser);

			$em = App::getOrm();
			$em->persist($user);

			// check if Twitter account already exists
			$account = $em->getRepository('DeskPRO:TwitterAccount')->findOneByUser($user['id']);
			if (!$account) {
				$account = new TwitterAccount();
				$account['user'] = $user;
			}

			// update OAuth credentials, regardless if its a new or an old account
			$account['oauth_token'] = $accessToken->getParam('oauth_token');
			$account['oauth_token_secret'] = $accessToken->getParam('oauth_token_secret');

			// add person to account
			if (!in_array($this->person['id'], $account->getPersonIds())) {
				$account['persons']->add($this->person);
				$em->persist($this->person);
			}

			$em->persist($account);
			$em->flush();

			// fetch user timelines
			$this->importTimeline($account, 'public');
			$this->importTimeline($account, 'home');
			$this->importTimeline($account, 'friends');
			$this->importTimeline($account, 'user');

			// fetch friends (following)
			// @TODO check pagination (we only recieve 100 friends at once)
			$friendIds = $account->getFriendIds();
			foreach ($this->twitter->user->friends()->user as $user) {
				if (!in_array((integer) $user->id, $friendIds)) {
					$friend = new TwitterAccountFriend();
					$friend['account'] = $account;
					$friend['user'] = $this->getOrCreateUser($user);
					$em->persist($friend);
				}
			}
			$em->flush();

			// fetch followers
			// @TODO check pagination (we only recieve 100 followers at once)
			$followerIds = $account->getFollowerIds();
			foreach ($this->twitter->user->followers()->user as $user) {
				if (!in_array((integer) $user->id, $followerIds)) {
					$follower = new TwitterAccountFollower();
					$follower['account'] = $account;
					$follower['user'] = $this->getOrCreateUser($user);
					$em->persist($follower);
				}
			}
			$em->flush();
		} catch (\Exception $e) {
			return $this->render('AdminBundle:TwitterAccount:authorize-error.html.twig', array(
				'error' => array(
					'class' => get_class($e),
					'message' => $e->getMessage(),
					'code' => $e->getCode(),
				)
			));
		}

		return $this->createResponse('<script language="javascript">window.close()</script>');
	}

	/**
	 * @param \Application\DeskPRO\Entity\TwitterAccount $account
	 * @param string $timeline
	 * @return void
	 */
	protected function importTimeline(TwitterAccount $account, $method)
	{
		$method = sprintf('status%sTimeline', ucfirst(strtolower($method)));
		$timeline = call_user_func(
			array($this->twitter, $method),
			array('include_entities' => true)
		);

		foreach ($timeline->status as $status) {
			$this->processStatus($status);
		}

		$this->em->flush();
	}

	/**
	 * @param \SimpleXMLElement|\Zend\Rest\Client\Result $status
	 * @return \Application\DeskPRO\Entity\TwitterStatus
	 */
	protected function processStatus($status)
	{
		$em = App::getOrm();
		$entity = $em->getRepository('DeskPRO:TwitterStatus')->find((string) $status->id);

		if (!$entity) {
			$entity = TwitterStatus::createFromXML($status);
			$entity['user'] = $this->getOrCreateUser($status->user);
			$em->persist($entity);

			// retweet
			if (!empty($status->retweeted_status)) {
				$retweet = $this->processStatus($status->retweeted_status);
				$entity['retweet'] = $retweet;
				$em->persist($retweet);
			}

			// reply
			if (!empty($status->in_reply_to_status_id)) {
				$replyXml = $this->twitter->status->show(
					(string) $status->in_reply_to_status_id,
					array('include_entities' => true)
				);

				if (!isset($replyXml->error)) {
					$reply = $this->processStatus($replyXml);
					$entity['reply'] = $reply;
					$em->persist($reply);
				}
			}

			// mentions
			if (!empty($status->entities->user_mentions)) {
				foreach ($status->entities->user_mentions->user_mention as $mention) {
					if (!($mentionUser = $em->getRepository('DeskPRO:TwitterUser')->find((string) $mention->id))) {
						$mentionUserXml = $this->twitter->user->show((string) $mention->id);
						$mentionUser = TwitterUser::createFromXML($mentionUserXml);
						$em->persist($mentionUser);
					}

					$mention = TwitterStatusMention::createFromXML($mention);
					$mention['status'] = $entity;
					$mention['user'] = $mentionUser;
					$em->persist($mention);
				}
			}

			// tags
			if (!empty($status->entities->hashtags)) {
				foreach ($status->entities->hashtags->hashtag as $tag) {
					$tag = TwitterStatusTag::createFromXML($tag);
					$tag['status'] = $entity;
					$em->persist($tag);
				}
			}

			// urls
			if (!empty($status->entities->urls)) {
				foreach ($status->entities->urls->url as $url) {
					$url = TwitterStatusUrl::createFromXML($url);
					$url['status'] = $entity;
					$em->persist($url);
				}
			}
		}

		return $entity;
	}

	/**
	 * @param \SimpleXMLElement|\Zend\Rest\Client\Result $user
	 * @return \Application\DeskPRO\Entity\TwitterUser
	 */
	protected function getOrCreateUser($user)
	{
		$entity = $this->em->getRepository('DeskPRO:TwitterUser')->find((string) $user->id);
		if (!$entity) {
			$entity = TwitterUser::createFromXML($user);
			$this->em->persist($entity);
		}

		return $entity;
	}

	/**
	 * Modify a Twitter account.
	 *
	 * @param integer $account_id Twitter account ID
	 * @return Symfony\Component\HttpFoundation\Response
	 */
	public function editAction($account_id)
	{
		if (!($account = App::getORM()->getRepository('DeskPRO:TwitterAccount')->find($account_id))) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Twitter Account "'.$account_id.'" not found.');
		}

		$form = $this->get('form.factory')->create(new EditTwitterAccountType(), $account);

		$is_edited = false;
		$row_html = false;

		if ($this->in->getBool('process')) {
			$form->bindRequest($this->get('request'));

			if ($form->isValid()) {
				$is_edited = true;
				$this->em->persist($account);
				$this->em->flush();

				$row_html = $this->renderView('AdminBundle:TwitterAccount:list-row.html.twig', array(
					'account' => $account
				));
			}
		}

		return $this->render('AdminBundle:TwitterAccount:edit.html.twig', array(
			'account' => $account,
			'form' => $form->createView(),
			'is_edited' => $is_edited,
			'row_html' => $row_html
		));
	}
}
