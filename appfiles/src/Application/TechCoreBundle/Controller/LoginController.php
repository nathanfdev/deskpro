<?php

namespace Application\TechCoreBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller;

class LoginController extends Controller
{
	public function indexAction()
    {
		$tplvars = array();

		if (isset($_REQUEST['process'])) {
			$user = $this->_processLogin();
			if ($user) {
				$this->container->get('request')->getSession()->setAttribute('auth_userid', $user['id']);
				return $this->redirect($this->container->getRouterService()->generate('TechCoreBundle:Main:index'));
			} else {
				$tplvars['invalid_login'] = true;
			}
		}

       return $this->render('TechCoreBundle:Login:index:twig', $tplvars);
    }

	protected function _processLogin()
	{
		$email_address = $_REQUEST['email_address'];
		$password = $_REQUEST['password'];

		$em = $this->container->get('doctrine.orm.entity_manager');
		$q = $em->createQuery('
			SELECT p
			FROM DeskPRO\\Bundle\\Core\\Entity\\ProfileEmail p
			WHERE p.email_address = ?1
		');
		$q->setParameter(1, $email_address);

		$profile_email = $q->getSingleResult();

		if (!$profile_email OR !$profile_email['profile'] OR !$profile_email['profile']['user_id']) {
			return false;
		}

		$user = $profile_email['profile']['user'];
		if (!$user->checkPassword($password)) {
			return false;
		}

		return true;
	}
}
