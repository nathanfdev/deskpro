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
 * Handles creating/editing of Twitter Accounts
 */
class TwitterController extends AbstractController
{
	/**
	 * Display statuses for provided account.
	 *
	 * @param integer $account_id The account id.
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	public function statusesAction($account_id)
	{
		// check if account id is in persons account id list
		if (!in_array($account_id, $this->person->getTwitterAccountIds())) {
			throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
		}

		// check if account exists
		$account = App::getOrm()->getRepository('DeskPRO:TwitterAccount')->find($account_id);
		if (!$account) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException(sprintf('There is no account with ID "%d"', $account_id));
		}

		// sort by date, ascending or descending
		$sortByDate = $this->in->getValue('sortbydate');
		if (!$sortByDate) {
			$sortByDate = 'asc';
		}

		// whether include archived and/or account statuses
		$includeArchived = $this->in->getBool('include.archived');
		$includeAccount  = $this->in->getBool('include.account');

		// fetch public timeline
		$statuses = $account->getTimeline($includeArchived, $includeAccount, $sortByDate);

		// check if is partial
		if ($this->in->getBool('partial')) {
			// render json response
			return $this->createJsonResponse(array(
				'statuses' => $this->renderView('AgentBundle:Twitter:part-statuses.html.twig', array(
					'statuses' => $statuses
				))
			));
		}

		// render html response
		return $this->render('AgentBundle:Twitter:statuses.html.twig', array(
			'account'  => $account,
			'statuses' => $statuses
		));
	}

	/**
	 * Display accounts for Super Menu.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function accountsPaneAction()
	{
		return $this->render('AgentBundle:Twitter:pane-accounts.html.twig', array(
			'accounts' => $this->person->getTwitterAccounts()
		));
	}

	/**
	 * Display statuses overview for Super Menu.
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function statusesPaneAction()
	{
		// fetch persons' accounts
		$accounts = $this->person->getTwitterAccounts();

		// statuses counters
		$statuses = array(
			'starred' => 0,
			'account' => 0,
			'team'    => 0
		);

		// iterate accounts, count statuses
		foreach ($accounts as $account) {
			// @TODO count statuses
		}

		return $this->render('AgentBundle:Twitter:pane-statuses.html.twig', array(
			'statuses' => $statuses
		));
	}
}