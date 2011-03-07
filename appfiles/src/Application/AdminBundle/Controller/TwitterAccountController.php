<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Pierre Minnieur <pm@pierre-minnieur.de>
 */

namespace Application\AdminBundle\Controller;

use \Application\DeskPRO\App;

/**
 * Handles creating/editing of Twitter Accounts
 */
class TwitterAccountController extends AbstractController
{
	public function listAction()
	{
		$accounts = App::getORM()->getRepository('DeskPRO:TwitterAccount')->findAll();

		return $this->render('AdminBundle:TwitterAccount:list.html.twig', array(
			'accounts' => $accounts
		));
	}

	public function newAction()
	{
		return $this->render('AdminBundle:TwitterAccount:new.html.twig');
	}

	public function editAction($account_id)
	{
		$account = App::getORM()->getRepository('DeskPRO:TwitterAccount')
			->findById($account_id);

		return $this->render('AdminBundle:TwitterAccount:edit.html.twig', array(
			'account' => $account
		));
	}
}