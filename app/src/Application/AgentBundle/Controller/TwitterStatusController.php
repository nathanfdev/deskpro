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

use Application\DeskPRO\Entity\TwitterAccount,
    \Application\DeskPRO\Entity\TwitterStatusNote;

use Orb\Service\Twitter\Twitter;

/**
 * Handles creating/editing of Twitter Accounts
 */
class TwitterStatusController extends AbstractController
{
	/**
	 * Display statuses for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listAction($account_id)
	{
		$account = $this->getAccount($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		// fetch public timeline
		$statuses = $account->getTimeline($includeArchived, $includeAccount, $this->getSortByDate());

		return $this->renderList($account, $statuses, 'agent_twitter_statuses_list');
	}

	/**
	 * Display messages for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listMessagesAction($account_id)
	{
		$account = $this->getAccount($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		$messages = $account->getMessages($includeArchived, $this->getSortByDate());

		return $this->renderList($account, $messages, 'agent_twitter_messages_list');
	}

	/**
	 * Display replies for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listRepliesAction($account_id)
	{
		$account = $this->getAccount($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		$replies = $account->getReplies($includeArchived, $this->getSortByDate());

		return $this->renderList($account, $replies, 'agent_twitter_replies_list');
	}

	/**
	 * Display mentions for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listMentionsAction($account_id)
	{
		$account = $this->getAccount($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		$mentions = $account->getMentions($includeArchived, $this->getSortByDate());

		return $this->renderList($account, $mentions, 'agent_twitter_mentions_list');
	}

	/**
	 * Display retweets for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listRetweetsAction($account_id)
	{
		$account = $this->getAccount($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		$retweets = $account->getRetweets($includeArchived, $this->getSortByDate());

		return $this->renderList($account, $retweets, 'agent_twitter_retweets_list');
	}

	/**
	 * Display sent statuses for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listOutgoingAction($account_id)
	{
		$account = $this->getAccount($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		$statuses = $account->getOutgoing($includeArchived, $this->getSortByDate());

		return $this->renderList($account, $statuses, 'agent_twitter_outgoing_list');
	}

	/**
	 * @return string
	 */
	protected function getSortByDate()
	{
		// sort by date, ascending or descending
		$sortByDate = $this->in->getValue('sortbydate');
		if (!$sortByDate) {
			$sortByDate = 'asc';
		}

		return $sortByDate;
	}

	/**
	 * @param \Application\DeskPRO\Entity\TwitterAccount $account
	 * @param array $statuses
	 * @param string $route
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function renderList(TwitterAccount $account, array $statuses, $route)
	{
		// view parameters
		$parameters = array(
			'twitter_list_route' => $route,
			'account' => $account,
			'statuses' => $statuses,
            'person' => $this->getPerson(),
		);

		// check if is partial
		if ($this->in->getBool('partial')) {
			return $this->render('AgentBundle:TwitterStatus:part-status.html.twig', $parameters);
		}

		// render html response
		return $this->render('AgentBundle:TwitterStatus:list.html.twig', $parameters);
	}

	/**
	 * @param integer $id
	 * @return void
	 * @see \Application\AgentBundle\Controller\TwitterController::checkAccountPermissions()
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function getStatus($id)
	{
		$status = $this->em->getRepository('DeskPRO:TwitterStatus')->find($id);
		if (!$status) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no status with ID "%d"', $id));
		}

		// @TODO add account <-> person check for status (via timeline/followers)

		return $status;
	}

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

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @see \Application\AgentBundle\Controller\TwitterController::getStatus()
	 */
	public function ajaxSaveNoteAction()
	{
		$success = false;
		$error = null;

		try {
			$status = $this->getStatus($this->in->getValue('status_id'));

			$note = new TwitterStatusNote();
			$note['status'] = $status;
			$note['person'] = $this->person;
			$note['text'] = $this->in->getValue('text');

			$em = App::getOrm();
			$em->persist($note);
			$em->flush();

			$success = true;
		} catch (\Exception $e) {
			$error = $e->getMessage();
		}

		return $this->createJsonResponse(array('success' => $success, 'error' => $error));
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @see \Application\AgentBundle\Controller\TwitterController::getStatus()
	 */
	public function ajaxSaveRetweetAction()
	{
		$success = false;
		$error = null;

		try {
			$status = $this->getStatus($this->in->getValue('status_id'));
			$account = $this->getAccount($this->in->getValue('account_id'));

			$twitter = Twitter::getTwitterService($account->getOauthAccessToken());
			$response = $twitter->status->retweet($status['id']);
			if (isset($response->error)) {
				$error = (string) $response->error;
			} else {
				$success = true;
			}
		} catch (\Exception $e) {
			$error = $e->getMessage();
		}

		return $this->createJsonResponse(array('success' => $success, 'error' => $error));
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @see \Application\AgentBundle\Controller\TwitterController::getStatus()
	 */
	public function ajaxSaveReplyAction()
	{
		$success = false;
		$error = null;

		try {
			$status = $this->getStatus($this->in->getValue('status_id'));
			$account = $this->getAccount($this->in->getValue('account_id'));
			$twitter = Twitter::getTwitterService($account->getOauthAccessToken());

			$type = $this->in->getValue('type');
			if ('private' == $type) {
				$response = $twitter->directMessage->new($status['user']['id'], $this->in->getValue('text'));
			} else {
				$response = $twitter->status->update($this->in->getValue('text'), $status['id']);
			}

			if (isset($response->error)) {
				$error = (string) $response->error;
			} else {
				$success = true;
			}
		} catch (\Exception $e) {
			$error = $e->getMessage();
		}

		return $this->createJsonResponse(array('success' => $success, 'error' => $error));
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @see \Application\AgentBundle\Controller\TwitterController::getStatus()
	 */
	public function ajaxSaveArchiveAction()
	{
		$success = false;
		$error = null;

		try {
			$status = $this->getStatus($this->in->getValue('status_id'));

			if ($status->isArchived()) {
				$status['is_archived'] = false;
			} else {
				$status['is_archived'] = true;
			}

			$em = App::getOrm();
			$em->persist($status);
			$em->flush();

			$success = true;
		} catch (\Exception $e) {
			$error = $e->getMessage();
		}

		return $this->createJsonResponse(array('success' => $success, 'error' => $error));
	}
}
