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
use \Application\DeskPRO\Entity\TwitterAccountFollowing;
use \Application\DeskPRO\Entity\TwitterAccountFollower;
use \Application\DeskPRO\Entity\TwitterUser;

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

		return $this->render('AdminBundle:TwitterAccount:list.html.twig', array(
			'accounts' => $accounts
		));
	}

	/**
	 * Retrieve a pre-configured Zend OAuth Consumer instance.
	 * May be used with Zend Service Twitter.
	 *
	 * @return \Zend_Oauth_Consumer
	 */
	protected function getOauthConsumer()
	{
		// @TODO make configurable
		$config = array(
			'callbackUrl'    => $this->generateUrl('admin_twitter_accounts_authorize', array(), true),
			'siteUrl'        => 'http://twitter.com/oauth',
			'consumerKey'    => '8F0tLXjdjVDDsovNjWJw',
			'consumerSecret' => '2naz7yxp6TBnEXwP9EnzLC0WU2Vwg60vm17tMntOo'
		);

		$consumer = new \Zend_Oauth_Consumer($config);

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
		$consumer     = $this->getOauthConsumer();
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
		// this pretty much sucks, but I can't figure out how to access $_GET
		parse_str(parse_url($this->request->getRequestUri(), PHP_URL_QUERY), $get);

		try {
			// request access token
			$consumer    = $this->getOauthConsumer();
			$accessToken = $consumer->getAccessToken($get, unserialize($this->session->get(self::TWITTER_REQUEST_TOKEN)));

			// initialize Twitter service
			$twitter = new \Zend_Service_Twitter(array(
				'accessToken' => $accessToken
			), $consumer);

			// Entity Manager & Repository
			$em    = App::getORM();
			$repos = $em->getRepository('DeskPRO:TwitterUser');

			// check if Twitter user already exists
			$twitterUser = $twitter->user->show($accessToken->getParam('screen_name'));
			$user        = $repos->find((integer) $twitterUser->id);
			if (!$user) {
				$user = $this->mapTwitterUserToEntity($twitterUser);
			}

			// persist entity
			$em->persist($user);

			// create Twitter account
			$account                       = new TwitterAccount();
			$account['oauth_token']        = $accessToken->getParam('oauth_token');
			$account['oauth_token_secret'] = $accessToken->getParam('oauth_token_secret');
			$account['user']               = $user;

			// persist entity
			$em->persist($account);

			// flush changes
			$em->flush();

			// fetch friends (following)
			// @TODO check pagination (we only recieve 100 friends at once)
			foreach ($twitter->user->friends()->user as $twitterFollowing) {
				// check if Twitter user already exists
				$following = $repos->find((integer) $twitterFollowing->id);
				if (!$following) {
					$following = $this->mapTwitterUserToEntity($twitterFollowing);
				}

				// create Twitter account following
				$accountFollowing            = new TwitterAccountFollowing();
				$accountFollowing['account'] = $account;
				$accountFollowing['user']    = $following;

				// persist entities
				$em->persist($following);
				$em->persist($accountFollowing);
			}

			// flush changes
			$em->flush();

			// fetch followers
			// @TODO check pagination (we only recieve 100 followers at once)
			foreach ($twitter->user->followers()->user as $twitterFollower) {
				// check if Twitter user already exists
				$follower = $repos->find((integer) $twitterFollower->id);
				if (!$follower) {
					$follower = $this->mapTwitterUserToEntity($twitterFollower);
				}

				// create Twitter account follower
				$accountFollower            = new TwitterAccountFollower();
				$accountFollower['account'] = $account;
				$accountFollower['user']    = $follower;

				// persist entities
				$em->persist($follower);
				$em->persist($accountFollower);
			}

			// flush changes
			$em->flush();
		} catch (\Exception $e) { // Zend_Oauth_Exception
			return $this->render('AdminBundle:TwitterAccount:authorize-error.html.twig', array(
				'error' => $e
			));
		}

		return $this->createResponse('<script language="javascript">window.close()</script>');
	}

	/**
	 * @param \SimpleXMLElement|\Zend_Rest_Client_Result $twitterUser
	 * @return \Application\DeskPRO\Entity\TwitterUser
	 */
	protected function mapTwitterUserToEntity($twitterUser)
	{
		$user                      = new TwitterUser();
		$user['id']                = (integer) $twitterUser->id;
		$user['name']              = (string) $twitterUser->name;
		$user['screen_name']       = (string) $twitterUser->screen_name;
		$user['profile_image_url'] = (string) $twitterUser->profile_image_url;
		$user['language']          = (string) $twitterUser->lang;
		$user['is_protected']      = (Boolean) (integer) $twitterUser->protected;
		$user['is_verified']       = (Boolean) (integer) $twitterUser->verified;
		$user['location']          = (string) $twitterUser->location;
		$user['is_geo_enabled']    = (Boolean) (integer) $twitterUser->geo_enabled;

		return $user;
	}

	/**
	 * Modify a Twitter account.
	 *
	 * @param integer $account_id Twitter account ID
	 * @return Symfony\Component\HttpFoundation\Response
	 */
	public function editAction($account_id)
	{
		$account = App::getORM()->getRepository('DeskPRO:TwitterAccount')
			->findById($account_id);

		if (!$account) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Twitter Account "'.$account_id.'" not found.');
		}

		$form = EditTwitterAccountForm::create($this->get('form.context'), 'account', array());
		$form->bind($this->get('request'), $account);

		return $this->render('AdminBundle:TwitterAccount:edit.html.twig', array(
			'account' => $account,
			'form' => $form
		));
	}
}