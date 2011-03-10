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
	public function statusesAction($account_id)
	{
		// fetch selected account
		$account = App::getOrm()->getRepository('DeskPRO:TwitterAccount')
			->findOneById($account_id);

		// @TODO improve check if person/team is "owner" :)
		foreach ($this->person->getTwitterAccounts() as $account) {
			if ($account_id == $account['id']) {
				break;
			}
		}

		$archived = false;

		// fetch following users (= public timeline tweeter)
		$followingIds   = $account->getFollowingIds();
		$followingIds[] = $account['user']['id'];

		// @TODO add checkboxes / filters / paging
		$statuses = App::getORM()->getRepository('DeskPRO:TwitterStatus')
			->findByUserIds($followingIds);

		return $this->render('AgentBundle:Twitter:statuses.html.twig', array(
			'account'  => $account,
			'statuses' => $statuses
		));
	}

	public function accountsPaneAction()
	{
		return $this->render('AgentBundle:Twitter:pane-accounts.html.twig', array(
			'accounts' => $this->person->getTwitterAccounts()
		));
	}

	public function statusesPaneAction()
	{
		$accounts = $this->person->getTwitterAccountIds();

		$statuses = array(
			'starred' => 0,
			'my'      => 0,
			'team'    => 0
		);

		return $this->render('AgentBundle:Twitter:pane-statuses.html.twig', array(
			'statuses' => $statuses
		));
	}
}