<?php

namespace Application\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MainController extends Controller
{
    public function indexAction()
    {
		$tplvars = array();

		if ($_REQUEST['process']) {
			if ($user = $this->_processLogin()) {
				$this->getRequest()->getSession()->setAttribute('auth_userid', $user['id']);
				return $this->redirect($this->container->getRouterService()->generate('UserBundle:Main:index'));
			} else {
				$tplvars['invalid_login'] = true;
			}
		}
        return $this->render('UserBundle:Login:index', $tplvars);
    }

	protected function _processLogin()
	{
		$email_address = $_REUQEST['email_address'];
		$password = $_REQUEST['password'];

		$em = $this->container->getService('doctrine.orm.entity_manager');
		$profile_email = $em->createQuery('SELECT DeskPRO:ProfileEmail WHERE email_address = ?', $email_address);

		if (!$profile_email OR !$profile_email['profile_id'] OR !$profile_email['profile']['user_id']) {
			return false;
		}

		$user = $profile_email['profile']['user_id'];
		if (!$user->checkPassword($password)) {
			return false;
		}

		return true;
	}

}
