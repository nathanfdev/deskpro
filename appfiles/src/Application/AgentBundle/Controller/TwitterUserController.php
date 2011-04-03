<?php

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Pierre Minnieur <pm@pierre-minnieur.de>
 */

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\App;

/**
 * Handles creating/editing of Twitter Users
 */
class TwitterUserController extends AbstractController
{
	/**
	 * Check account security.
	 *
	 * @param integer $id The account id.
	 * @return \Application\DeskPRO\Entity\TwitterAccount
	 * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function getAccount($id)
	{
		// check if account id is in persons account id list
		if (!in_array($id, $this->person->getTwitterAccountIds())) {
			throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
		}

		// check if account exists
		$account = App::getOrm()->getRepository('DeskPRO:TwitterAccount')->find($id);
		if (!$account) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no account with ID "%d"', $id));
		}

		return $account;
	}

	protected function getUser($id)
	{
		$user = App::getOrm()->getRepository('DeskPRO:TwitterUser')->find($id);
		if (!$user) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no user with ID "%d"', $id));
		}

		return $user;
	}

	public function viewAction($user_id)
	{
		return $this->render('AgentBundle:TwitterUser:view.html.twig', array(
			'user' => $this->getUser($user_id)
		));
	}

	public function ajaxSaveFollowAction()
	{
		$account = $this->getAccount($this->in->getInt('account_id'));
		$user = $this->getUser($this->in->getInt('user_id'));
		$success = false;

		if (!in_array($user['id'], $account->getFriendIds())) {
			$twitter = Twitter::getTwitterService($account->getOauthAccessToken());
			/* $response = */ $twitter->friendship->create($user['id']);

			$friend = new TwitterAccountFriend();
			$friend['account'] = $account;
			$friend['user'] = $user;

			$em = App::getOrm();
			$em->persist($friend);
			$em->flush();

			$success = true;
		}

		return $this->createJsonResponse(array('success' => $success));
	}

	public function ajaxSaveUnfollowAction()
	{
		$account = $this->getAccount($this->in->getInt('account_id'));
		$user = $this->getUser($this->in->getInt('user_id'));
		$success = false;

		if (in_array($user['id'], $account->getFriendIds())) {
			$twitter = Twitter::getTwitterService($account->getOauthAccessToken());
			/* $response = */ $twitter->friendship->destroy($user['id']);

			$em = App::getOrm();
			$friend = $em->getRepository('DeskPRO:TwitterAccountFriend')
				->findOneByAccountIdAndUserId($account['id'], $userId);

			$em->remove($friend);
			$em->flush();

			$success = true;
		}

		return $this->createJsonResponse(array('success' => $success));
	}
}
