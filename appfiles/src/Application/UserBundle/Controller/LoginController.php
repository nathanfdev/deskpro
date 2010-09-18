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
		if ($this->isPostRequest()) {
			$person = $this->_processLogin();

			// In some cases, there is a direct response (like a redirect)
			if ($person instanceof \Symfony\Component\HttpFoundation\Response) {
				return $person;
			}

			if ($person) {
				$this->session->set('auth_person_id', $person['id']);
				return $this->redirect($this['router']->generate('tech_dashboard', array()));
			} else {
				$this->tplvars['invalid_login'] = true;
			}
		}

       return $this->render('UserBundle:Login:index');
    }

	protected function _processLogin()
	{
		$auth = $this->getAuth();

		$usersource_id = $this->in->getUint('usersource_id');
		$adapter = $this->getAuthAdapter($usersource_id);

		return $this->_processAuth($auth, $adapter);
	}

	protected function _processAuth($auth, $adapter)
	{
		$result = $adapter->authenticate();
		if ($result->isRedirectRequired()) {
			return $this->redirect($result->getRedirectUrl());
		} elseif (!$result->isValid()) {
			return false;
		} else {
			$user_init = new \DeskPRO\Auth\UserInitializer($this->em);
			$person = $user_init->getPersonFromIdentity($usersource_id, $result->getIdentity());

			return $person;
		}
	}


	
	############################################################################
	# /callback/:usersource_id
	############################################################################

	public function callbackAction($usersource_id)
	{
		try {
			$usersource = $this->em->find('CoreBundle:Usersource', $usersource_id);
		} catch (\Doctrine\ORM\NoResultException $e) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException("There is no usersource with ID $usersource_id");
		}

		$auth = $this->getAuth();
		$adapter = $this->getAuthAdapter($usersource_id);

		$person = $this->_processAuth($auth, $adapter);

		// In some cases, there is a direct response (like a redirect)
		if ($person instanceof \Symfony\Component\HttpFoundation\Response) {
			return $person;
		}
		
		if ($person) {
			$this->session->set('auth_userid', $user['id']);
			return $this->redirect($this['router']->generate('tech_dashboard', array()));
		} else {
			$this->tplvars['invalid_login'] = true;
		}
	}



	############################################################################
	# /logout
	############################################################################

	/**
	 * Handles user logout by destroying the session and cookies.
	 */
	public function logoutAction()
	{
		// TODO
		// When symfony session is more flushed out, should completely destroy the old session

		$this->session->set('auth_userid', null);

		return $this->redirect($this['router']->generate('user_login', array()));
	}



	############################################################################

	/**
	 * Get the auth object
	 *
	 * @return Orb\Auth\Auth
	 */
	protected function getAuth()
	{
		static $auth = null;

		if ($auth === null) {
			$auth = new \Orb\Auth\Auth();
		}

		return $auth;
	}


	
	/**
	 * Get the auth adapter for a particular usersource.
	 * 
	 * @param int $usersource_id The usersource id
	 * @return Orb\Auth\Adapter\AdapterInterface
	 */
	protected function getAuthAdapter($usersource_id)
	{
		$adapter = null;

		if (!$usersource_id) {
			$adapter = new \DeskPRO\Auth\Adapter\Local($this->em);
			$adapter->setCredentials($this->in->getString('username'), $this->in->getString('password'));
		}

		return $adapter;
	}
}
