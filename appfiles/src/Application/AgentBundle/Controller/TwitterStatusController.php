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
			$status = $this->getStatus($this->in->getInt('status_id'));

			// @TODO add retweet code
			// 1) send to twitter
			// 2) store in db (user stream should do that?)

			// $em = App::getOrm();
			// $em->persist($);
			// $em->flush();
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
			$status = $this->getStatus($this->in->getInt('status_id'));

			// @TODO add reply code
			// 1) send to twitter
			// 2) store in db (user stream should do that?)

			// $em = App::getOrm();
			// $em->persist($);
			// $em->flush();
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
