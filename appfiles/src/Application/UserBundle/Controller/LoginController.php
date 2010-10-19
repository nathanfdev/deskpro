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

use \DeskPRO\Auth\LoginProcessor;

class LoginController extends \DeskPRO\Controller\AbstractController
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
			SELECT FROM CoreBundle:Usersource us
			INDEX BY us.id
			WHERE us.is_enabled = ?1
		')->setParameter(1, true)->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

		return $this->render('UserBundle:Login:index', array(
			'usersources' => $usersources,
			'usersource_forms' => $this->_getUsersourceLoginForms($usersources)
		));
	}

	protected function _getUsersourceLoginForms($usersources)
	{
		$forms = array();

		foreach ($usersources as $usersource) {
			$parts = explode('\\', $usersource['handler_class']);
			$tpl_name = 'UserBundle::Login:_usersource_form_' . strtolower(array_pop($parts));

			$this->forms[$usersource['id']] = $this->tpl->render($tpl_name, array('usersource' => $usersource));
		}

		return $forms;
	}

	############################################################################
	# /login/authenticate
	############################################################################

	public function authenticateAction()
	{
		$usersource_id = $this->in->getUint('usersource_id');

		if ($usersource_id) {
			return $this->_processUsersourceLogin($usersource_id);
		} else {
			return $this->_processLocalLogin();
		}
	}

	protected function _processLocalLogin()
	{
		$adapter = new \DeskPRO\Auth\Adapter\Local($this->em);
		$adapter->setCredentials($this->in->getString('username'), $this->in->getString('password'));
		if (!$adapter->isValid()) {
			return $this->redirect($this['router']->generate('login', array()));
		}

		$identity = $adapter->getIdentity();

		$this->session->set('auth_person_id', $identity['id']);
		return $this->redirect($this['router']->generate('tech_dashboard', array()));
	}

	protected function _processUsersourceLogin($usersource_id)
	{
		$usersource = $this->em->find('CoreBundle:Usersource', $usersource_id);

		$adapter = $this->_initUserSourceAdapter($usersource);

		#------------------------------
		# Callback types require us to redirect
		#------------------------------

		if ($adapter instanceof Orb\Auth\Adapter\CallbackInterface) {
			$adapter->setCallbackUrl($this->generateUrl('user_login_callback', array('usersource_id' => $usersource_id), true));

			$result = $adapter->authenticate();

			// We expect a redirect to be rquired
			if ($result->isRedirectRequired()) {
				return $this->redirect($result->getRedirectUrl());

			// Otherwise its an error
			} else {
				return $this->redirect($this['router']->generate('login', array()));
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
				return $this->redirect($this['router']->generate('tech_dashboard', array()));

			// Error, go back to login
			} else {
				return $this->redirect($this['router']->generate('login', array()));
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
		if (!($adapter instanceof Orb\Auth\Adapter\CallbackInterface)) {
			return $this->redirect($this['router']->generate('login', array()));
		}

		$adapter->setCallbackContext($_REQUEST);

		$result = $adapter->authenticate();

		// Valid
		if ($result->isValid()) {

			$login_processor = new LoginProcessor($usersource, $result->getIdentity());
			$person = $login_processor->getPerson();

			$this->session->set('auth_person_id', $person['id']);
			return $this->redirect($this['router']->generate('tech_dashboard', array()));

		// Error, go back to login
		} else {
			return $this->redirect($this['router']->generate('login', array()));
		}
	}


	############################################################################

	protected function _initUserSourceAdapter($usersource)
	{
		$adapter = $usersource->getHandler()->getAuthAdapter();

		if ($adapter instanceof Orb\Auth\Adapter\SessionStateInterface) {
			$auth_session = $this->session->createNamespace('user_auth_state');

			$auth_state = new Orb\Auth\StateHandler\ArrayAccessWrapper($auth_session);
			$auth_state->setClearStateMethod('clearAllData');

			$adapter->setStateHandler($auth_session);
		}
	}
}
