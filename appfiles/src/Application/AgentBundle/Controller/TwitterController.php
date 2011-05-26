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
class TwitterController extends AbstractController
{
	public function getSectionDataAction()
	{
		$data = array();

		#------------------------------
		# Statuses
		#------------------------------

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
			$statuses['starred'] += $account->countStarredStatuses();
			$statuses['account'] += $account->countAssignedStatusesToAgent();
			$statuses['team'] += $account->countAssignedStatusesToTeam();
		}

		$data['section_html'] = $this->renderView('AgentBundle:Twitter:window-section.html.twig', array(
			'statuses' => $statuses,
			'accounts' => $this->person->getTwitterAccounts()
		));

		return $this->createJsonResponse($data);
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
			$statuses['starred'] += $account->countStarredStatuses();
			$statuses['account'] += $account->countAssignedStatusesToAgent();
			$statuses['team'] += $account->countAssignedStatusesToTeam();
		}

		return $this->render('AgentBundle:Twitter:pane-statuses.html.twig', array(
			'statuses' => $statuses
		));
	}
}