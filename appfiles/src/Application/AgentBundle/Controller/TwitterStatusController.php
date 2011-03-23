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

use \Application\DeskPRO\Entity\TwitterStatusNote;

use \Orb\Service\Twitter\Twitter;

/**
 * Handles creating/editing of Twitter Accounts
 */
class TwitterStatusController extends AbstractController
{
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
		$response = array('success' => true);

		try {
			$status = $this->getStatus($this->in->getInt('status_id'));

			$note                 = new TwitterStatusNote();
			$note['status']       = $status;
			$note['person']       = $this->person;
			$note['text']         = $this->in->getValue('text');

			$em = App::getOrm();
			$em->persist($note);
			$em->flush();
		} catch (\Exception $e) {
			$response = array(
				'success' => false,
				'error'   => $e->getMessage()
			);
		}

		return $this->createJsonResponse($response);
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @see \Application\AgentBundle\Controller\TwitterController::getStatus()
	 */
	public function ajaxSaveRetweetAction()
	{
		$response = array('success' => true);

		try {
			$status  = $this->getStatus($this->in->getInt('status_id'));
			$account = $this->getAccount($this->in->getInt('account_id'));

			$twitter = Twitter::getTwitterService($account->getOauthAccessToken());

			// @TODO analyse response if it actually worked
			/* $response = */ $twitter->status->retweet($status['id']);
		} catch (\Exception $e) {
			$response = array(
				'success' => false,
				'error'   => $e->getMessage()
			);
		}

		return $this->createJsonResponse($response);
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @see \Application\AgentBundle\Controller\TwitterController::getStatus()
	 */
	public function ajaxSaveReplyAction()
	{
		$response = array('success' => true);

		try {
			$status  = $this->getStatus($this->in->getInt('status_id'));
			$account = $this->getAccount($this->in->getInt('account_id'));

			// @TODO add private reply
			// $type = $this->in->getValue('type');

			$twitter = Twitter::getTwitterService($account->getOauthAccessToken());

			// @TODO analyse response if it actually worked
			/* $response = */ $twitter->status->update($this->in->getValue('text'), $status['id']);
		} catch (\Exception $e) {
			$response = array(
				'success' => false,
				'error'   => $e->getMessage()
			);
		}

		return $this->createJsonResponse($response);
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @see \Application\AgentBundle\Controller\TwitterController::getStatus()
	 */
	public function ajaxSaveArchiveAction()
	{
		$response = array('success' => true);

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
		} catch (\Exception $e) {
			$response = array(
				'success' => false,
				'error'   => $e->getMessage()
			);
		}

		return $this->createJsonResponse($response);
	}
}
