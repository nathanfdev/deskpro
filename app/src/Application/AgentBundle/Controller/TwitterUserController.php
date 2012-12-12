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

		try {
			$account->getTwitterApi()->post_friendshipsCreate(array(
				'user_id' => $user->id
			));
		} catch (\EpiTwitterException $e) {
			// likely already following
		}

		$friend = $this->em->getRepository('DeskPRO:TwitterAccountFriend')
			->findOneByAccountIdAndUserId($account['id'], $user['id']);

		if (!$friend) {
			$friend = new TwitterAccountFriend();
			$friend['account'] = $account;
			$friend['user'] = $user;

			$em = App::getOrm();
			$this->em->persist($friend);
			$this->em->flush();
		}

		$success = true;

		return $this->createJsonResponse(array('success' => $success));
	}

	public function ajaxSaveUnfollowAction()
	{
		$account = $this->getAccount($this->in->getInt('account_id'));
		$user = $this->getUser($this->in->getInt('user_id'));

		try {
			$account->getTwitterApi()->post_friendshipsDestroy(array(
				'user_id' => $user->id
			));
		} catch (\EpiTwitterException $e) {
			// likely not following already
		}

		$friend = $this->em->getRepository('DeskPRO:TwitterAccountFriend')
			->findOneByAccountIdAndUserId($account['id'], $user['id']);

		if ($friend) {
			$this->em->remove($friend);
			$this->em->flush();
		}

		$success = true;

		return $this->createJsonResponse(array('success' => $success));
	}

	public function ajaxSaveArchiveAction()
	{
		$account = $this->getAccount($this->in->getInt('account_id'));
		$user = $this->getUser($this->in->getInt('user_id'));

		$follower = $this->em->getRepository('DeskPRO:TwitterAccountFollower')
			->findOneByAccountIdAndUserId($account['id'], $user['id']);

		if ($follower) {
			$follower->is_archived = $this->in->getBool('archive');

			$this->em->persist($follower);
			$this->em->flush();
		}

		return $this->createJsonResponse(array('success' => true));
	}

	public function ajaxSaveMessageAction()
	{
		$account = $this->getAccount($this->in->getInt('account_id'));
		$user = $this->getUser($this->in->getInt('user_id'));

		$success = false;
		$error = null;

		$text = $this->in->getString('text');
		$type = $this->in->getValue('type');
		if (strlen($text)) {
			if ($type == 'public' && strpos($text, '@'.$user->screen_name) === false) {
				$text = '@' . $user->screen_name . ' ' . $text;
			}

			$twitter_service = new \Application\DeskPRO\Service\Twitter();
			$response = $twitter_service->sendAccountMessage($type, $text, $account, null, $user);

			$success = $response['success'];
			$error = $response['error'];
		} else {
			$error = 'No text specified.';
		}

		return $this->createJsonResponse(array(
			'success' => $success,
			'error' => $error
		));
	}

	/**
	 * List the followers for an account
	 */
	public function listFollowersAction($account_id)
	{
		$account = $this->getAccount($account_id);

		$page = 1;
		$limit = 25;

		return $this->render('AgentBundle:TwitterUser:list-followers.html.twig', array(
			'account'	=> $account,
			'followers' => $account->getFollowers($page, $limit)
		));
	}

	public function listNewFollowersAction($account_id)
	{
		$account = $this->getAccount($account_id);

		$page = 1;
		$limit = 25;

		return $this->render('AgentBundle:TwitterUser:list-new-followers.html.twig', array(
			'account'	=> $account,
			'new_followers' => $account->getNewFollowers($page, $limit)
		));
	}
}
