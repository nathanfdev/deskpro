<?php

namespace Application\TechBundle\Controller;

class LoginController extends \DeskPRO\Controller\AbstractController
{
	public function indexAction()
    {
		if (isset($_REQUEST['process'])) {
			$user = $this->_processLogin();
			if ($user) {
				$this['request']->getSession()->start();
				$this['request']->getSession()->set('auth_userid', $user['id']);
				return $this->redirect($this['router']->generate('tech_dashboard', array()));
			} else {
				$this->tplvars['invalid_login'] = true;
			}
		}

       return $this->render('TechBundle:Login:index');
    }

	protected function _processLogin()
	{
		$email_address = $_REQUEST['email_address'];
		$password = $_REQUEST['password'];

		$q = $this->em->createQuery('
			SELECT e
			FROM Core:ProfileEmail e
			WHERE e.email_address = ?1 AND e.profile IS NOT NULL
		');
		$q->setParameter(1, $email_address);

		try {
			$profile_email = $q->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {
			return false;
		}

		$user = $profile_email['profile']['user'];
		if (!$user->checkPassword($password)) {
			return false;
		}

		return $user;
	}


	public function logoutAction()
	{
		// TODO
		// When symfony session is more flushed out, should completely destroy the old session

		$this['request']->getSession()->start();
		$this['request']->getSession()->set('auth_userid', $user['id']);

		return $this->redirect($this['router']->generate('tech_login', array()));
	}
}
