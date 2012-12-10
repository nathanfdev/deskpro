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
 * @subpackage AgentBundle
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;

use Application\DeskPRO\Entity\TwitterAccountFriend;

use Orb\Service\Twitter\Twitter;

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
		$account = $this->em->getRepository('DeskPRO:TwitterAccount')->find($id);
		if (!$account) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no account with ID "%d"', $id));
		}

		return $account;
	}

	protected function getUser($id)
	{
		$user = $this->em->getRepository('DeskPRO:TwitterUser')->find($id);
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
				->findOneByAccountIdAndUserId($account['id'], $user['id']);

			if ($friend) {
				$em->remove($friend);
				$em->flush();

				$success = true;
			}
		}

		return $this->createJsonResponse(array('success' => $success));
	}

	/**
	 * List the followers for an account
	 */
	public function listFollowersAction($account_id)
	{
		$account = $this->getAccount($account_id);

		return $this->render('AgentBundle:TwitterUser:list-followers.html.twig', array(
			'account'	=> $account,
		));

	}
}
