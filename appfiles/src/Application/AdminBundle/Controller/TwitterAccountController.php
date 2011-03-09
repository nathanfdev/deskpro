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
use \Application\DeskPRO\Entity\TwitterUser;

use \Application\AdminBundle\Form\EditTwitterAccountForm;

/**
 * Handles creating/editing of Twitter Accounts
 */
class TwitterAccountController extends AbstractController
{
	public function listAction()
	{
		$accounts = App::getORM()->getRepository('DeskPRO:TwitterAccount')->findAll();

		return $this->render('AdminBundle:TwitterAccount:list.html.twig', array(
			'accounts' => $accounts
		));
	}

	protected function getOAuthConsumer()
	{
		// @TODO make configurable
		$config = array(
			'callbackUrl' => $this->generateUrl('admin_twitter_accounts_authorize', array(), true),
    		'siteUrl' => 'http://twitter.com/oauth',
			'consumerKey' => 'VlVgYGmZBQqFUbMvVopzdQ',
			'consumerSecret' => 'shgfckgHZrNz8UzNkwUYVQPi90fLJrZOaIUmzAVU9fE'
		);

		$consumer = new \Zend_Oauth_Consumer($config);

		return $consumer;
	}

	public function newAction()
	{
		$consumer = $this->getOAuthConsumer();
		$requestToken = $consumer->getRequestToken();
		$this->session->set('twitter_token', serialize($requestToken));

		return $this->redirect($consumer->getRedirectUrl());
	}

	public function authorizeAction()
	{
		// this pretty much sucks, but I can't figure out how to access $_GET
		parse_str(parse_url($this->request->getRequestUri(), PHP_URL_QUERY), $get);

		try {
			$consumer = $this->getOAuthConsumer();

			// request access token
			$accessToken = $consumer->getAccessToken($get, unserialize($this->session->get('twitter_token')));

			// initialize Twitter service
			$twitter = new \Zend_Service_Twitter(array(
				'accessToken' => $accessToken
			), $consumer);

			// fetch account data
			$twitterUser = $twitter->user->show($accessToken->getParam('screen_name'));

			// create Twitter account
			$account = new TwitterAccount();
			$account['oauth_token'] = $accessToken->getParam('oauth_token');
			$account['oauth_token_secret'] = $accessToken->getParam('oauth_token_secret');

			// create Twitter user
			$user = new TwitterUser();
			$user['id'] = (integer) $twitterUser->id;
			$user['name'] = (string) $twitterUser->name;
			$user['screen_name'] = (string) $twitterUser->screen_name;
			$user['profile_image_url'] = (string) $twitterUser->profile_image_url;
			$user['language'] = (string) $twitterUser->lang;
			$user['is_protected'] = (Boolean) (integer) $twitterUser->protected;
			$user['is_verified'] = (Boolean) (integer) $twitterUser->verified;
			$user['location'] = (string) $twitterUser->location;
			$user['is_geo_enabled'] = (Boolean) (integer) $twitterUser->geo_enabled;

			// link account & user
			$account['user'] = $user;

			// @TODO gather more data, e.g. followers, following, timeline, etc

			// persist account & user
			$em = App::getORM();
			$em->persist($account);
			$em->persist($user);
			$em->flush();
		} catch (\Zend_Oauth_Exception $e) {
		}

		return $this->createResponse('<script language="javascript">window.close()</script>');
	}

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