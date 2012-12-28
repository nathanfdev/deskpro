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

use Application\DeskPRO\Entity\TwitterAccount;
use Application\DeskPRO\Entity\TwitterAccountStatus;
use Application\DeskPRO\Entity\TwitterAccountStatusNote;

/**
 * Handles creating/editing of Twitter Accounts
 */
class TwitterStatusController extends AbstractController
{
	/**
	 * Display inbox for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listInboxAction($account_id)
	{
		$account = $this->getAccountOr404($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		$page = $this->in->getUint('page');
		if (!$page) $page = 1;

		// fetch inbox
		$statuses = $account->getInbox($includeArchived, $includeAccount, $this->getSortByDate(), $page);
		$count = $account->countInbox($includeArchived, $includeAccount);

		return $this->renderList($account, $statuses, $count, $page, 'agent_twitter_inbox_list');
	}

	/**
	 * Display messages for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listMessagesAction($account_id)
	{
		$account = $this->getAccountOr404($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		$count = $account->countMessages($includeArchived, $includeAccount);
		$page = $this->adjustPage($count);

		$messages = $account->getMessages($includeArchived, $includeAccount, $this->getSortByDate(), $page);

		return $this->renderList($account, $messages, $count, $page, 'agent_twitter_messages_list');
	}

	/**
	 * Display replies for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listRepliesAction($account_id)
	{
		$account = $this->getAccountOr404($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		$count = $account->countReplies($includeArchived, $includeAccount);
		$page = $this->adjustPage($count);

		$replies = $account->getReplies($includeArchived, $includeAccount, $this->getSortByDate(), $page);

		return $this->renderList($account, $replies, $count, $page, 'agent_twitter_replies_list');
	}

	/**
	 * Display mentions for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listMentionsAction($account_id)
	{
		$account = $this->getAccountOr404($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		$count = $account->countMentions($includeArchived, $includeAccount);
		$page = $this->adjustPage($count);

		$mentions = $account->getMentions($includeArchived, $includeAccount, $this->getSortByDate(), $page);

		return $this->renderList($account, $mentions, $count, $page, 'agent_twitter_mentions_list');
	}

	/**
	 * Display retweets for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listRetweetsAction($account_id)
	{
		$account = $this->getAccountOr404($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		$count = $account->countRetweets($includeArchived, $includeAccount);
		$page = $this->adjustPage($count);

		$retweets = $account->getRetweets($includeArchived, $includeAccount, $this->getSortByDate(), $page);

		return $this->renderList($account, $retweets, $count, $page, 'agent_twitter_retweets_list');
	}

	/**
	 * Display timeline for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listTimelineAction($account_id)
	{
		$account = $this->getAccountOr404($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		$count = $account->countTimeline($includeArchived, $includeAccount);
		$page = $this->adjustPage($count);

		// fetch user timeline
		$statuses = $account->getTimeline($includeArchived, $includeAccount, $this->getSortByDate(), $page);

		return $this->renderList($account, $statuses, $count, $page, 'agent_twitter_timeline_list');
	}

	/**
	 * Display sent statuses for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listOutgoingAction($account_id)
	{
		$account = $this->getAccountOr404($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$sortByDate = $this->getSortByDate('desc');

		$count = $account->countOutgoing($includeArchived);
		$page = $this->adjustPage($count);

		$statuses = $account->getOutgoing($includeArchived, $sortByDate, $page);

		return $this->renderList($account, $statuses, $count, $page, 'agent_twitter_outgoing_list', $sortByDate);
	}

	/**
	 * @return string
	 */
	protected function getSortByDate($default = 'desc')
	{
		// sort by date, ascending or descending
		$sortByDate = $this->in->getValue('sortbydate');
		if (!$sortByDate) {
			$sortByDate = $default;
		}

		return $sortByDate;
	}

	protected function adjustPage($count, $page = null, $per_page = null)
	{
		if (!$per_page) {
			$per_page = TwitterAccount::DEFAULT_LIMIT;
		}
		if ($page === null) {
			$page = $this->in->getUint('page');
		}
		if (!$page) {
			$page = 1;
		}

		$start = ($page - 1) * $per_page;
		if ($start >= $count) {
			$page = ($count ? ceil($count / $per_page) : 1);
		}

		return $page;
	}

	/**
	 * @param \Application\DeskPRO\Entity\TwitterAccount $account
	 * @param array $statuses
	 * @param integer $total_count
	 * @param integer $page
	 * @param string $route
	 * @param string|null $sort_by_date
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function renderList(TwitterAccount $account, array $statuses, $total_count, $page, $route, $sort_by_date = null)
	{
		if ($sort_by_date === null) {
			$sort_by_date = $this->getSortByDate();
		}

		$per_page = TwitterAccount::DEFAULT_LIMIT;

		$parameters = array(
			'twitter_list_route' => $route,
			'account' => $account,
			'statuses' => $statuses,
			'person' => $this->getPerson(),
			'sort_by_date' => $sort_by_date,
			'total_count' => $total_count,
			'per_page' => $per_page,
			'page' => $page,
			'showing_to' => min($total_count, $page * $per_page)
		);

		if ($this->in->getBool('last')) {
			$parameters['account_status'] = end($statuses);
			return $this->render('AgentBundle:TwitterStatus:list-row.html.twig', $parameters);
		}

		// check if is partial
		if ($this->in->getBool('partial')) {
			return $this->render('AgentBundle:TwitterStatus:part-status.html.twig', $parameters);
		}

		// render html response
		return $this->render('AgentBundle:TwitterStatus:list.html.twig', $parameters);
	}

	/**
	 * @param integer $id
	 * @return \Application\DeskPRO\Entity\TwitterAccountStatus
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	protected function getAccountStatusOr404($id, $check_perm = '')
	{
		$status = $this->em->getRepository('DeskPRO:TwitterAccountStatus')->find($id);
		if (!$status) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no status with ID "%d"', $id));
		}

		$account = $status->account;
		if (!$account || !$account->hasPerson($this->person)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no status with ID "%d"', $id));
		}

		// todo: more fine grained permissions?

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
	protected function getAccountOr404($id)
	{
		// check if account exists
		$account = $this->em->getRepository('DeskPRO:TwitterAccount')->find($id);
		if (!$account || !$account->hasPerson($this->person)) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no account with ID "%d"', $id));
		}

		return $account;
	}

	public function ajaxMassSaveAction()
	{
		$account_status_ids = $this->in->getCleanValueArray('result_ids', 'int', 'discard');
		$account_statuses = $this->em->getRepository('DeskPRO:TwitterAccountStatus')->getByIds($account_status_ids);
		$action = $this->in->getString('action');

		$twitter_service = new \Application\DeskPRO\Service\Twitter();

		foreach ($account_statuses AS $account_status) {
			/** @var $account_status TwitterAccountStatus */
			if (!$account_status->account->hasPerson($this->person)) {
				continue;
			}

			switch ($action) {
				case 'retweet':
					if (!$account_status->retweeted && $account_status->canRetweet()) {
						$twitter_service->sendRetweet($account_status->account, $account_status);
					}
					break;

				case 'unretweet':
					if ($account_status->retweeted) {
						$twitter_service->unsendRetweet($account_status->account, $account_status);
					}
					break;

				case 'reply':
					$text = $this->in->getString('text');
					$type = $this->in->getValue('type');
					$split = $this->in->getBool('split');
					if (strlen($text)) {
						if ($type == 'public' && strpos($text, '@'.$account_status->status->user->screen_name) === false) {
							$text = '@' . $account_status->status->user->screen_name . ' ' . $text;
						}

						$twitter_service = new \Application\DeskPRO\Service\Twitter();
						$twitter_service->sendAccountMessage($type, $text, $split, $account_status->account, $account_status);
					}
					break;

				case 'favorite':
					if (!$account_status->is_favorited) {
						$twitter_service->setFavorite($account_status->account, $account_status, true);
					}
					break;

				case 'unfavorite':
					if ($account_status->is_favorited) {
						$twitter_service->setFavorite($account_status->account, $account_status, false);
					}
					break;

				case 'archive':
					$account_status->is_archived = true;
					break;

				case 'unarchive':
					$account_status->is_archived = false;
					break;

				case 'assign':
					list($type, $id) = explode(':', $this->in->getValue('assign'));
					if ($type == 'agent') {
						$agent = $this->em->find('DeskPRO:Person', $id);
						if ($agent && $agent->is_agent && $account_status->account->hasPerson($agent)) {
							$account_status->agent = $agent;
						}
					} else {
						$account_status->setAgentTeamId($id);
					}
					break;
			}

			$this->em->persist($account_status);
		}

		$this->em->flush();

		return $this->createJsonResponse(array(
			'success' => true
		));
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function ajaxSaveNoteAction()
	{
		$success = false;
		$error = null;
		$html = null;

		$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'), 'note');

		$note = new TwitterAccountStatusNote();
		$note['account_status'] = $account_status;
		$note['person'] = $this->person;
		$note['text'] = $this->in->getValue('text');

		$em = App::getOrm();
		$em->persist($note);
		$em->flush();

		$success = true;

		$html = $this->renderView('AgentBundle:TwitterStatus:note-li.html.twig', array(
			'account_status' => $account_status,
			'note' => $note
		));

		return $this->createJsonResponse(array(
			'success' => $success,
			'error' => $error,
			'html' => $html
		));
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function ajaxSaveRetweetAction()
	{
		$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'), 'retweet');
		$account = $account_status->account;

		$success = true;
		$error = null;
		$retweet = false;
		$html = array();

		if ($this->in->getBool('retweet')) {
			if (!$account_status->retweeted) {
				$twitter_service = new \Application\DeskPRO\Service\Twitter();
				$output = $twitter_service->sendRetweet($account, $account_status);
				$success = $output['success'];
				$error = $output['error'];
				if (!$error) {
					$retweet = true;
				}
			}
		} else {
			$text = $this->in->getString('text');
			if (strlen($text)) {
				$twitter_service = new \Application\DeskPRO\Service\Twitter();
				$output = $twitter_service->sendAccountMessage('public', $text, true, $account, $account_status);
				$success = $output['success'];
				$error = $output['error'];

				if ($output['new_account_statuses']) {
					foreach ($output['new_account_statuses'] AS $new_account_status) {
						$html[] = $this->renderView('AgentBundle:TwitterStatus:reply-li.html.twig', array(
							'account_status' => $account_status,
							'reply' => $new_account_status
						));
					}
				}
			}
		}

		return $this->createJsonResponse(array(
			'success' => $success,
			'error' => $error,
			'retweet' => $retweet,
			'html' => $html
		));
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function ajaxSaveUnretweetAction()
	{
		$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'), 'retweet');
		$account = $account_status->account;

		$twitter_service = new \Application\DeskPRO\Service\Twitter();
		$output = $twitter_service->unsendRetweet($account, $account_status);

		$success = $output['success'];
		$error = $output['error'];

		return $this->createJsonResponse(array('success' => $success, 'error' => $error));
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function ajaxSaveReplyAction()
	{
		$success = false;
		$error = null;
		$html = array();

		$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'));
		$account = $account_status->account;

		$text = $this->in->getString('text');
		$type = $this->in->getValue('type');
		$split = $this->in->getBool('split');
		if (strlen($text)) {
			if ($type == 'public' && strpos($text, '@'.$account_status->status->user->screen_name) === false) {
				$text = '@' . $account_status->status->user->screen_name . ' ' . $text;
			}

			$twitter_service = new \Application\DeskPRO\Service\Twitter();
			$response = $twitter_service->sendAccountMessage($type, $text, $split, $account, $account_status);

			$success = $response['success'];
			$error = $response['error'];
			if ($response['new_account_statuses']) {
				foreach ($response['new_account_statuses'] AS $new_account_status) {
					$html[] = $this->renderView('AgentBundle:TwitterStatus:reply-li.html.twig', array(
						'account_status' => $account_status,
						'reply' => $new_account_status
					));
				}
			}
		} else {
			$error = 'No text specified.';
		}

		return $this->createJsonResponse(array(
			'success' => $success,
			'html' => $html,
			'error' => $error
		));
	}

	public function ajaxSaveArchiveAction()
	{
		$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'), 'archive');

		$account_status['is_archived'] = $this->in->getBool('archive');

		$this->em->persist($account_status);
		$this->em->flush();

		return $this->createJsonResponse(array('success' => true));
	}

	public function ajaxSaveDeleteAction()
	{
		$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'), 'delete');

		$twitter_service = new \Application\DeskPRO\Service\Twitter();
		$output = $twitter_service->deleteStatus($account_status->account, $account_status);

		$success = $output['success'];
		$error = $output['error'];

		return $this->createJsonResponse(array('success' => $success, 'error' => $error));
	}

	public function ajaxSaveEditAction()
	{
		$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'), 'edit');

		if (!$account_status->status->long) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		if ($this->in->getBool('process')) {
			$text = $this->in->getString('text');

			if (strlen($text)) {
				$account_status->status->long->text = $text;
				$this->em->persist($account_status->status->long);
				$this->em->flush();
			}

			return $this->createJsonResponse(array(
				'success' => true,
				'error' => null,
				'parsed_text' => $account_status->status->long->getParsedText())
			);
		} else {
			return $this->render('AgentBundle:TwitterStatus:edit-overlay.html.twig', array(
				'long' => $account_status->status->long
			));
		}
	}

	public function ajaxSaveFavoriteAction()
	{
		$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'), 'favorite');
		$account = $account_status->account;

		$twitter_service = new \Application\DeskPRO\Service\Twitter();
		$output = $twitter_service->setFavorite($account, $account_status, $this->in->getBool('favorite'));

		$success = $output['success'];
		$error = $output['error'];

		return $this->createJsonResponse(array('success' => $success, 'error' => $error));
	}

	public function ajaxSaveAssignAction()
	{
		$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'), 'assign');

		list($type, $id) = explode(':', $this->in->getValue('assign'));
		if ($type == 'agent') {
			$account_status->setAgentId($id);
		} else {
			$account_status->setAgentTeamId($id);
		}

		$this->em->persist($account_status);

		return $this->createJsonResponse(array('success' => true));
	}
}
