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

		return $this->renderList($account, $statuses, 'statuses');
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
		$messages = $account->getMessages($this->getSortByDate());

		return $this->renderList($account, $messages, 'messages');
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
		$replies = $account->getReplies($this->getSortByDate());

		return $this->renderList($account, $replies, 'replies');
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
		$mentions = $account->getMentions($this->getSortByDate());

		return $this->renderList($account, $mentions, 'mentions');
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
		$retweets = $account->getRetweets($this->getSortByDate());

		return $this->renderList($account, $retweets, 'retweets');
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
		$statuses = $account->getOutgoing($this->getSortByDate());

		return $this->renderList($account, $statuses, 'outgoing');
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
	 * @param string $type
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function renderList(TwitterAccount $account, array $statuses, $type)
	{
		// view parameters
		$parameters = array(
			'type' => $type,
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
		$status = App::getOrm()->getRepository('DeskPRO:TwitterStatus')->find($id);
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
		$account = App::getOrm()->getRepository('DeskPRO:TwitterAccount')->find($id);
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
			$status = $this->getStatus($this->in->getInt('status_id'));

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
			$status = $this->getStatus($this->in->getInt('status_id'));
			$account = $this->getAccount($this->in->getInt('account_id'));

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
			$status = $this->getStatus($this->in->getInt('status_id'));
			$account = $this->getAccount($this->in->getInt('account_id'));
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
