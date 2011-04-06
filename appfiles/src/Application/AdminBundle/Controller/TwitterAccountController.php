<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Pierre Minnieur <pm@pierre-minnieur.de>
 */

namespace Application\AdminBundle\Controller;

use \Application\DeskPRO\App;

use \Application\DeskPRO\Entity\TwitterAccount;
use \Application\DeskPRO\Entity\TwitterAccountFriend;
use \Application\DeskPRO\Entity\TwitterAccountFollower;
use \Application\DeskPRO\Entity\TwitterStatus;
use \Application\DeskPRO\Entity\TwitterStatusMention;
use \Application\DeskPRO\Entity\TwitterStatusTag;
use \Application\DeskPRO\Entity\TwitterStatusUrl;
use \Application\DeskPRO\Entity\TwitterUser;

use \Orb\Service\Twitter\Oauth,
	\Orb\Service\Twitter\Twitter;

use \Application\AdminBundle\Form\EditTwitterAccountForm;

/**
 * Handles creating/editing of Twitter Accounts
 */
class TwitterAccountController extends AbstractController
{
	const TWITTER_REQUEST_TOKEN = 'twitter_request_token';

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
	 * @return \Zend_Oauth_Consumer
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
			$twitter = Twitter::getTwitterService($accessToken, $consumer);

			// check if Twitter user already exists
			$twitterUser = $twitter->user->show($accessToken->getParam('screen_name'));
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
			$this->importTimeline($account, $twitter->status->publicTimeline());
			$this->importTimeline($account, $twitter->status->friendsTimeline());
			$this->importTimeline($account, $twitter->status->userTimeline());

			// fetch friends (following)
			// @TODO check pagination (we only recieve 100 friends at once)
			$friendIds = $account->getFriendIds();
			foreach ($twitter->user->friends()->user as $user) {
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
			foreach ($twitter->user->followers()->user as $user) {
				if (!in_array((integer) $user->id, $followerIds)) {
					$follower = new TwitterAccountFollower();
					$follower['account'] = $account;
					$follower['user'] = $this->getOrCreateUser($user);
					$em->persist($follower);
				}
			}
			$em->flush();
		} catch (\Exception $e) { // Zend_Oauth_Exception
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
	 * @param \SimpleXMLElement|\Zend_Rest_Client_Result $timeline
	 * @return void
	 */
	protected function importTimeline(TwitterAccount $account, $timeline)
	{
		$em = App::getOrm();

		foreach ($timeline->status as $status) {
			$entity = $em->getRepository('DeskPRO:TwitterStatus')->find((string) $status->id);
                
			if (!$entity) {
				$entity = TwitterStatus::createFromXML($status);
				$entity['user'] = $this->getOrCreateUser($status->user);

				if (isset($status->retweeted_status)) {
					$retweet = TwitterStatus::createFromXML($status->retweeted_status);
					$retweet['user'] = $this->getOrCreateUser($status->retweeted_status->user);
					$entity['retweet'] = $retweet;
					$em->persist($retweet);
				}

				$em->persist($entity);
			}
		}

		$em->flush();
	}

	/**
	 * @param \SimpleXMLElement|\Zend_Rest_Client_Result $user
	 * @return \Application\DeskPRO\Entity\TwitterUser
	 */
	protected function getOrCreateUser($user)
	{
		$entity = App::getOrm()->getRepository('DeskPRO:TwitterUser')->find((string) $user->id);
		if (!$entity) {
			$entity = TwitterUser::createFromXML($user);
			App::getOrm()->persist($entity);
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

		$form = EditTwitterAccountForm::create($this->get('form.context'), 'account', array('account' => $account));
		$form->bind($this->get('request'), $account);

		$is_edited = false;
		$row_html = false;

		if ($this->in->getBool('process')) {
			$is_edited = true;
			App::getOrm()->persist($account);
			App::getOrm()->flush();

			$row_html = $this->renderView('AdminBundle:TwitterAccount:list-row.html.twig', array(
				'account' => $account
			));
		}

		return $this->render('AdminBundle:TwitterAccount:edit.html.twig', array(
			'account' => $account,
			'form' => $form,
			'is_edited' => $is_edited,
			'row_html' => $row_html
		));
	}
}