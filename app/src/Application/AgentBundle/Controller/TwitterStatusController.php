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
	 * Display statuses for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function listAction($account_id)
	{
		$account = $this->getAccountOr404($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		// fetch public timeline
		$statuses = $account->getInbox($includeArchived, $includeAccount, $this->getSortByDate());

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
		$account = $this->getAccountOr404($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		$messages = $account->getMessages($includeArchived, $includeAccount, $this->getSortByDate());

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
		$account = $this->getAccountOr404($account_id);

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
		$account = $this->getAccountOr404($account_id);

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
		$account = $this->getAccountOr404($account_id);

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
		$account = $this->getAccountOr404($account_id);

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getValue('include.archived');
		$includeAccount  = $this->in->getBool('include.account');
		$sortByDate = $this->getSortByDate('desc');

		$statuses = $account->getOutgoing($includeArchived, $sortByDate);

		return $this->renderList($account, $statuses, 'agent_twitter_outgoing_list', $sortByDate);
	}

	/**
	 * @return string
	 */
	protected function getSortByDate($default = 'asc')
	{
		// sort by date, ascending or descending
		$sortByDate = $this->in->getValue('sortbydate');
		if (!$sortByDate) {
			$sortByDate = $default;
		}

		return $sortByDate;
	}

	/**
	 * @param \Application\DeskPRO\Entity\TwitterAccount $account
	 * @param array $statuses
	 * @param string $route
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	protected function renderList(TwitterAccount $account, array $statuses, $route, $sort_by_date = null)
	{
		if ($sort_by_date === null) {
			$sort_by_date = $this->getSortByDate();
		}

		// view parameters
		$parameters = array(
			'twitter_list_route' => $route,
			'account' => $account,
			'statuses' => $statuses,
			'person' => $this->getPerson(),
			'sort_by_date' => $sort_by_date
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
		$success = false;
		$error = null;

		try {
			$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'), 'retweet');
			$account = $account_status->account;

			if (!$account_status->retweeted) {
				$api = $account->getTwitterApi();
				$response = $api->post("/statuses/retweet/{$account_status->status->id}.json");
				if (!empty($response->error)) {
					$error = $response->error;
				} else {
					$success = true;

					$twitter_service = new \Application\DeskPRO\Service\Twitter();
					$new_status = $twitter_service->processStatus($api, $response);

					$new_account_status = new TwitterAccountStatus();
					$new_account_status->status = $new_status;
					$new_account_status->account = $account;
					$new_account_status->status_type = 'sent';

					$account_status->retweeted = $new_account_status;

					$this->em->persist($new_status);
					$this->em->persist($new_account_status);
					$this->em->persist($account_status);
					$this->em->flush();
				}
			} else {
				$success = true;
			}
		} catch (\EpiTwitterException $e) {
			$error = $e->getMessage();
		}  catch (\EpiOAuthException $e) {
			$error = $e->getMessage();
		}

		return $this->createJsonResponse(array('success' => $success, 'error' => $error));
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function ajaxSaveUnretweetAction()
	{
		$success = false;
		$error = null;

		$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'), 'retweet');
		$account = $account_status->account;

		try {
			if ($account_status->retweeted) {
				$account_retweet = $account_status->retweeted;

				$response = $account->getTwitterApi()->post("/statuses/destroy/{$account_retweet->status->id}.json");
				if (!empty($response->error)) {
					$error = $response->error;
				} else {
					$success = true;

					$this->em->remove($account_retweet);
					$this->em->remove($account_retweet->status);
					$this->em->flush();
				}
			} else {
				$success = true;
			}
		} catch (\EpiTwitterException $e) {
			$error = $e->getMessage();
		}  catch (\EpiOAuthException $e) {
			$error = $e->getMessage();
		}

		return $this->createJsonResponse(array('success' => $success, 'error' => $error));
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function ajaxSaveReplyAction()
	{
		$success = false;
		$error = null;
		$html = null;

		$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'));
		$account = $account_status->account;

		$text = $this->in->getString('text');
		if (strlen($text)) {
			try {
				$type = $this->in->getValue('type');
				if ($type == 'public') {
					if (strpos($text, '@'.$account_status->status->user->screen_name) === false) {
						$text = '@' . $account_status->status->user->screen_name . ' ' . $text;
					}

					if (\Orb\Util\Strings::utf8_strlen($text) > 140) {
						$error = 'Long statuses are todo'; // todo
					} else {
						$params = array(
							'status' => $text
						);
						if (!$account_status->status->recipient) {
							// only if not a DM
							$params['in_reply_to_status_id'] = $account_status->status->id;
						}

						$api = $account->getTwitterApi();
						$response = $api->post_statusesUpdate($params);
						if (!empty($response->error)) {
							$error = $response->error;
						} else {
							$success = true;

							$twitter_service = new \Application\DeskPRO\Service\Twitter();
							$new_status = $twitter_service->processStatus($api, $response);

							$new_account_status = new TwitterAccountStatus();
							$new_account_status->status = $new_status;
							$new_account_status->account = $account;
							$new_account_status->status_type = 'sent';
							$new_account_status->in_reply_to = $account_status;

							$this->em->persist($new_status);
							$this->em->persist($new_account_status);
							$this->em->flush();

							$html = $this->renderView('AgentBundle:TwitterStatus:reply-li.html.twig', array(
								'account_status' => $account_status,
								'reply' => $new_account_status
							));
						}
					}
				} else {
					$error = 'Private responses are TODO'; // todo
				}
			} catch (\EpiTwitterException $e) {
				$error = $e->getMessage();
			}  catch (\EpiOAuthException $e) {
				$error = $e->getMessage();
			}
		} else {
			$error = 'No tweet specified.';
		}

		return $this->createJsonResponse(array(
			'success' => $success,
			'html' => $html,
			'error' => $error
		));
	}

	public function ajaxSaveArchiveAction()
	{
		$success = false;
		$error = null;

		$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'), 'archive');

		$account_status['is_archived'] = $this->in->getBool('archive');

		$this->em->persist($account_status);
		$this->em->flush();

		$success = true;

		return $this->createJsonResponse(array('success' => $success, 'error' => $error));
	}

	public function ajaxSaveFavoriteAction()
	{
		$success = false;
		$error = null;

		$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'), 'favorite');

		try {
			$api = $account_status->account->getTwitterApi();

			$favorite = $this->in->getBool('favorite');

			if (!$account_status->status->isMessage()) {
				if ($favorite) {
					$response = $api->post_favoritesCreate(array('id' => $account_status->status->id));
				} else {
					$response = $api->post_favoritesDestroy(array('id' => $account_status->status->id));
				}
				if (!empty($response->error)) {
					$error = $response->error;
				}
			}

			if (empty($error)) {
				$account_status['is_favorited'] = $favorite;

				$this->em->persist($account_status);
				$this->em->flush();

				$success = true;
			}
		} catch (\EpiTwitterException $e) {
			$error = $e->getMessage();
		}  catch (\EpiOAuthException $e) {
			$error = $e->getMessage();
		}

		return $this->createJsonResponse(array('success' => $success, 'error' => $error));
	}

	public function ajaxSaveAssignAction()
	{
		$success = false;
		$error = null;

		$account_status = $this->getAccountStatusOr404($this->in->getValue('account_status_id'), 'assign');

		list($type, $id) = explode(':', $this->in->getValue('assign'));
		if ($type == 'agent') {
			$account_status->setAgentId($id);
		} else {
			$account_status->setAgentTeamId($id);
		}

		$this->em->persist($account_status);
		$this->em->flush();

		$success = true;

		return $this->createJsonResponse(array('success' => $success, 'error' => $error));
	}
}
