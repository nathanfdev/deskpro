<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\Auth\LoginProcessor;
use \Application\DeskPRO\Controller\Helper\LoginHelper;

use Application\DeskPRO\App;

class LoginController extends \Application\DeskPRO\Controller\AbstractController
{
	/**
	 * @var \Application\DeskPRO\Controller\Helper\LoginHelper
	 */
	protected $login_helper;

	public function init()
	{
		parent::init();

		$this->login_helper = new LoginHelper(
			$this,
			'AgentBundle:Login',
			'agent'
		);
	}

	/**
	 * Handles showing the login form, and on POST handles login credentials
	 * through the auth adapters.
	 */
	public function indexAction()
	{
		return $this->login_helper->execIndexAction();
	}

	public function logoutAction()
	{
		return $this->login_helper->execLogoutAction();
	}
	
	public function authenticateAction($usersource_id)
	{
		return $this->login_helper->execAuthenticateAction($usersource_id);
	}

	public function authenticateCallbackAction($usersource_id)
	{
		return $this->login_helper->execAuthenticateCallbackAction($usersource_id);
	}

	public function preloadSourcesAction()
	{
		return $this->render('AgentBundle:Login:js-preload.html.twig');
	}
}
