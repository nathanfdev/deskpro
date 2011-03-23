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
	 * @return \Zend_Oauth_Consumer
	 */
	protected function getConsumer()
	{
		$callbackUrl = $this->generateUrl('admin_twitter_accounts_authorize', array(), true);
		$consumer    = \Orb\Service\Twitter\Oauth::getConsumer($callbackUrl);

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
		$consumer     = $this->getConsumer();
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
			// get access token
			$consumer    = $this->getConsumer();
			$accessToken = $consumer->getAccessToken(
				$this->request->query->all(),
				unserialize($this->session->get(self::TWITTER_REQUEST_TOKEN))
			);

			// initialize Twitter service
			$twitter = \Orb\Service\Twitter\Twitter::getTwitterService($accessToken, $consumer);

			// Entity Manager & Repository
			$em    = App::getORM();
			$repos = $em->getRepository('DeskPRO:TwitterUser');

			// check if Twitter user already exists
			$twitterUser = $twitter->user->show($accessToken->getParam('screen_name'));
			$user        = $repos->find((integer) $twitterUser->id);
			if (!$user) {
				$user = \Orb\Service\Twitter\User::createEntityFromXML($twitterUser);
			}

			// persist entity
			$em->persist($user);

			// check if Twitter account already exists
			$account = $em->getRepository('DeskPRO:TwitterAccount')->findOneByUser($user['id']);
			if (!$account) {
				// create Twitter account
				$account         = new TwitterAccount();
				$account['user'] = $user;
			}

			// update OAuth credentials, regardless if its a new or an old account
			$account['oauth_token']        = $accessToken->getParam('oauth_token');
			$account['oauth_token_secret'] = $accessToken->getParam('oauth_token_secret');

			// add person to account
			if (!in_array($this->person['id'], $account->getPersonIds())) {
				$account['persons']->add($this->person);
				$em->persist($this->person);
			}

			// persist entities
			$em->persist($account);

			// flush changes
			$em->flush();

			// fetch friends (following)
			// @TODO check pagination (we only recieve 100 friends at once)
			foreach ($twitter->user->friends()->user as $twitterFollowing) {
				// check if Twitter user already exists
				$following = $repos->find((integer) $twitterFollowing->id);
				if (!$following) {
					$following = \Orb\Service\Twitter\User::createEntityFromXML($twitterFollowing);
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
					$follower = \Orb\Service\Twitter\User::createEntityFromXML($twitterFollower);
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