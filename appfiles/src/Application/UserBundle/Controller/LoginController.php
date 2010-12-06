<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\UserBundle\Controller;

use \Application\DeskPRO\Auth\LoginProcessor;

class LoginController extends \Application\DeskPRO\Controller\AbstractController
{
	############################################################################
	# /login
	############################################################################

	/**
	 * Handles showing the login form, and on POST handles login credentials
	 * through the auth adapters.
	 */
	public function indexAction()
	{
		$usersources = $this->em->createQuery('
			SELECT us
			FROM CoreBundle:Usersource us
			INDEX BY us.id
			WHERE us.is_enabled = ?1
		')->setParameter(1, true)->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

		return $this->render('UserBundle:Login:index.twig', array(
			'usersources' => $usersources,
			'usersource_forms' => $this->_getUsersourceLoginForms($usersources)
		));
	}

	protected function _getUsersourceLoginForms($usersources)
	{
		$forms = array();

		foreach ($usersources as $usersource) {
			$parts = explode('\\', $usersource['handler_class']);
			$tpl_name = 'UserBundle:Login:login-form-.twig' . strtolower(array_pop($parts));

			$forms[] = array(
				'usersource' => $usersource,
				'html' => $this->tpl->render($tpl_name, array('usersource' => $usersource))
			);
		}

		return $forms;
	}


	
	############################################################################
	# /logout
	############################################################################

	public function logoutAction()
	{
		$this->session->setAttributes(array());
		
		return $this->redirectRoute('user_login');
	}



	############################################################################
	# /login/authenticate
	############################################################################

	public function authenticateAction($usersource_id)
	{
		if ($usersource_id) {
			return $this->_processUsersourceLogin($usersource_id);
		} else {
			return $this->_processLocalLogin();
		}
	}

	protected function _processLocalLogin()
	{
		$adapter = new \Application\DeskPRO\Auth\Adapter\Local($this->em);
		$adapter->setCredentials($this->in->getString('email'), $this->in->getString('password'));
		$result = $adapter->authenticate();
		if (!$result->isValid()) {
			return $this->redirect($this->get('router')->generate('user_login', array()));
		}

		$identity = $result->getIdentity();

		$this->session->set('auth_person_id', $identity->getIdentity());
		return $this->redirect($this->get('router')->generate('tech_dashboard', array()));
	}

	protected function _processUsersourceLogin($usersource_id)
	{
		$usersource = $this->em->find('CoreBundle:Usersource', $usersource_id);

		$adapter = $this->_initUserSourceAdapter($usersource);

		#------------------------------
		# Callback types require us to redirect
		#------------------------------

		if ($adapter instanceof \Orb\Auth\Adapter\CallbackInterface) {
			$result = $adapter->authenticate();

			// We expect a redirect to be rquired
			if ($result->isRedirectRequired()) {
				return $this->redirect($result->getRedirectUrl());

			// Otherwise its an error
			} else {
				return $this->redirect($this->get('router')->generate('user_login', array()));
			}

		#------------------------------
		# Other types should return a result right away
		#------------------------------

		} else {
			$result = $adapter->authenticate();

			// Valid
			if ($result->isValid()) {

				$login_processor = new LoginProcessor($usersource, $result->getIdentity());
				$person = $login_processor->getPerson();

				$this->session->set('auth_person_id', $person['id']);
				return $this->redirect($this->get('router')->generate('tech_dashboard', array()));

			// Error, go back to login
			} else {
				return $this->redirect($this->get('router')->generate('user_login', array()));
			}
		}
	}



	############################################################################
	# /login/authenticate-callback/:usersource_id
	############################################################################

	public function authenticateCallbackAction($usersource_id)
	{
		$usersource = $this->em->find('CoreBundle:Usersource', $usersource_id);

		$adapter = $this->_initUserSourceAdapter($usersource);

		// It must be a callback type to be here, so if not redirect back to login
		if (!($adapter instanceof \Orb\Auth\Adapter\CallbackInterface)) {
			return $this->redirect($this->get('router')->generate('user_login', array()));
		}

		$adapter->setCallbackContext($_REQUEST);

		$result = $adapter->authenticate();

		// Valid
		if ($result->isValid()) {

			$login_processor = new LoginProcessor($usersource, $result->getIdentity());
			$person = $login_processor->getPerson();

			$this->session->set('auth_person_id', $person['id']);
			return $this->redirect($this->get('router')->generate('tech_dashboard', array()));

		// Error, go back to login
		} else {
			print_r($_REQUEST);
			die('err');
			return $this->redirect($this->get('router')->generate('user_login', array()));
		}
	}


	############################################################################

	protected function _initUserSourceAdapter($usersource)
	{
		$adapter = $usersource->getHandler()->getAuthAdapter();

		if ($adapter instanceof \Orb\Auth\Adapter\FormLoginInterface) {
			$adapter->setFormData($_POST);
		}

		if ($adapter instanceof \Orb\Auth\Adapter\CallbackInterface) {
			$adapter->setCallbackUrl($this->generateUrl('user_login_callback', array('usersource_id' => $usersource['id']), true));
		}

		if ($adapter instanceof \Orb\Auth\Adapter\SessionStateInterface) {
			$auth_state = new \Orb\Auth\StateHandler\ArrayAccessWrapper($this->session);
			$auth_state->setClearStateMethod('clear');

			$adapter->setStateHandler($auth_state);
		}

		return $adapter;
	}
}
