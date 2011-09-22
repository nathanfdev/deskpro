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

namespace Application\UserBundle\Controller;

use \Application\DeskPRO\Auth\LoginProcessor;
use \Application\DeskPRO\Controller\Helper\LoginHelper;
use Application\DeskPRO\Entity\TmpData;

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
			'UserBundle:Login',
			'user'
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

	public function resetPasswordAction($invalid_email = false, $invalid_code = false)
	{
		return $this->render('UserBundle:Login:reset-password.html.twig', array(
			'invalid_email' => $invalid_email,
			'invalid_code' => $invalid_code
		));
	}

	public function sendResetPasswordAction()
	{
		$email = $this->in->getString('email');
		$person = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($email);

		if (!$person) {
			if ($this->request->isXmlHttpRequest()) {
				return $this->createJsonResponse(array('error' => 'invalid_email'));
			}
			return $this->resetPasswordAction(true);
		}

		$code_data = TmpData::create('reset-password', array('person_id' => $person['id']), '+2 days');
		App::getOrm()->persist($code_data);
		App::getOrm()->flush();

		$vars = array(
			'code' => $code_data->getCode()
		);

		$email_subject = 'Reset Password';
		$email_body = App::get('templating')->render('DeskPRO:emails_user:reset-password.html.twig', $vars);

		$message = App::getMailer()->createMessage();
		$message->setTo($email, $person->getDisplayName());
		$message->setSubject($email_subject);
		$message->setBody($email_body, 'text/html');

		App::getMailer()->send($message);

		if ($this->request->isXmlHttpRequest()) {
			return $this->createJsonResponse(array('success' =>1 ));
		}
		return $this->render('UserBundle:Login:reset-password-sent.html.twig', array());
	}

	public function resetPasswordNewPassAction($code)
	{
		$code_data = App::getEntityRepository('DeskPRO:TmpData')->getByCode($code, 'reset-password');
		$person = null;
		if ($code_data) {
			$person = App::findEntity('DeskPRO:Person', $code_data->getData('person_id', 0));
		}

		if (!$code_data OR !$person) {
			return $this->resetPasswordAction(false, true);
		}

		if ($this->in->getBool('process')) {
			$pass = $this->in->getString('password');
			$pass2 = $this->in->getString('password2');

			if ($pass == $pass2) {
				$person->setPassword($pass);
				App::getOrm()->transactional(function ($em) use ($person, $code_data) {
					$em->persist($person);
					$em->remove($code_data);
					$em->flush();
				});

				return $this->redirectRoute('user_login');
			}
		}

		return $this->render('UserBundle:Login:reset-password-newpass.html.twig', array(
			'code' => $code_data->getCode()
		));
	}

	public function resetPasswordNewPassQueryCode()
	{
		return $this->resetPasswordNewPassAction($this->in->getString('reset_code'));
	}

	public function inlineLoginAction()
	{
		$adapter = new \Application\DeskPRO\Auth\Adapter\Local(App::getOrm());
		$adapter->setCredentials($this->in->getString('email'), $this->in->getString('password'));
		$result = $adapter->authenticate();

		if (!$result->isValid()) {
			$html = $this->renderView('UserBundle:Common:form-email-login-row.html.twig', array('login_error' => true));
			return $this->createJsonResponse(array(
				'html' => $html,
			));
		}

		$identity = $result->getIdentity();

		$this->session->set('auth_person_id', $identity->getIdentity());

		$person = $identity['person'];
		$person->setLastLoginAt();
		$person->loadHelper('IdeaVotes', array(
			'visitor' => $this->session->getVisitor()
		));
		$person->loadHelper('HelpdeskUser', array(
			'session' => $this->session,
			'visitor' => $this->session->getVisitor()
		));

		$this->person = $person;

		App::setCurrentPerson($person);

		App::getOrm()->persist($person);
		App::getOrm()->flush();

		$html = $this->renderView('UserBundle:Common:form-email-login-row.html.twig', array('person' => $person));
		$html_userbar = $this->renderView('UserBundle:Common:layout-userbar.html.twig', array('person' => $person));

		return $this->createJsonResponse(array(
			'html' => $html,
			'sections_replace' => array(
				'dp_userbar' => $html_userbar,
			),
			'person_id' => $person['id'],
			'name' => $person['name']
		));
	}
}
