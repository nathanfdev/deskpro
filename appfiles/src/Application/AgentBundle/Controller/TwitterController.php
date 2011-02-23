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

/**
 * Handles creating/editing of Twitter Accounts
 */
class TwitterController extends AbstractController
{
	public function indexAction()
	{
		return $this->render('AgentBundle:Twitter:index.html.twig');
	}

	public function accountsPaneAction()
	{
		return $this->render('AgentBundle:Twitter:pane-accounts.html.twig');
	}
}