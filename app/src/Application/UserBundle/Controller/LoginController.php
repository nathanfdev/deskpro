<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 */

namespace Application\UserBundle\Controller;

use Application\DeskPRO\Auth\LoginProcessor;
use Application\DeskPRO\Controller\Helper\LoginHelper;
use Application\DeskPRO\Entity\TmpData;

use Application\DeskPRO\App;

class LoginController extends \Application\DeskPRO\Controller\AbstractController
{
	protected $tpl_prefix = 'UserBundle:Login';
	protected $route_prefix = 'user';

	/**
	 * @var \Application\DeskPRO\Controller\Helper\LoginHelper
	 */
	protected $login_helper;

	public function init()
	{
		parent::init();

		$this->login_helper = new LoginHelper(
			$this,
			$this->tpl_prefix,
			$this->route_prefix
		);
	}

	/**
	 * Handles showing the login form, and on POST handles login credentials
	 * through the auth adapters.
	 */
	public function indexAction()
	{
		$return = $this->in->getStringFromGet('return');
		if ($return AND $return[0] != '/') {
			// Always be a path on the current domain,
			// or else it might be a trick to go to some other domain etc
			$return = '';
		}

		$register = new \Application\UserBundle\Form\Model\Register();
		$reg_formtype = new \Application\UserBundle\Form\RegisterType();
		$form = $this->get('form.factory')->create($reg_formtype, $register);

		$failed_login_name = false;
		if ($this->session->has('failed_login_name')) {
			$failed_login_name = $this->session->get('failed_login_name');
			$this->session->remove('failed_login_name');
			$this->session->save();
		}

		return $this->render($this->tpl_prefix . ':index.html.twig', array(
			'return' => $return,
			'form' => $form->createView(),
			'failed_login_name' => $failed_login_name,
		));
	}

	protected function _logoutPerson()
	{
		// When an agent actually logs out, we should be clearing the state
		$person = $this->session->getPerson();
		if ($person['is_agent']) {
			$this->db->executeUpdate("
				DELETE FROM people_prefs
				WHERE person_id = ? AND name = ?
			", array($person['id'], 'agent.ui.state'));
		}

		$this->session->replace(array());
		$this->session->save();

		foreach (array('dpsid', 'dpsid-agent', 'dpsid-admin', 'dpreme') as $cookie_name) {
			if (!empty($_COOKIE[$cookie_name])) {
				$sess2 = $this->em->getRepository('DeskPRO:Session')->getSessionFromCode($_COOKIE[$cookie_name]);
				if ($sess2) {
					$this->em->remove($sess2);
					$this->em->flush();
				}
			}

			$cookie = \Application\DeskPRO\HttpFoundation\Cookie::makeDeleteCookie($cookie_name);
			$cookie->send();
		}
	}

	public function logoutAction($auth)
	{
		if (!\Orb\Util\Util::checkStaticSecurityToken($auth, md5(App::getAppSecret() . 'user_logout'))) {
			return $this->redirectRoute('user');
		}

		$this->_logoutPerson();

		if ($this->in->getString('quicklogout') == 'ajax') {
			if ($this->in->getString('callback')) {
				return $this->createJsonpResponse(array('logged_out' => true));
			} else {
				return $this->createJsonResponse(array('logged_out' => true));
			}
		} elseif ($this->in->getString('quicklogout') == 'pop') {
			$html = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<script type="text/javascript">
window.close();
window.onload = function() { window.close(); };
</script>
</head>
<body>
</body>
</html>
HTML;

			$this->createResponse($html);
		}

		if ($this->in->getString('to') == 'admin') {
			return $this->redirect($this->request->getBaseUrl() . '/admin/login?o');
		} elseif ($this->in->getString('to') == 'agent') {
			return $this->redirect($this->request->getBaseUrl() . '/agent/login?o');
		} else {
			return $this->redirectRoute('user');
		}
	}

	public function authenticateLocalAction($usersource_id)
	{
		$return = $this->in->getString('return');

		$result = $this->authLocalInput();

		// Form wasnt inputted (eg direct url)
		if (!$this->in->getString('email') || !$this->in->getString('password')) {
			return $this->redirectRoute($this->route_prefix . '_login', array('return' => $return));
		}

		if (!$result->isValid()) {

			// Send alert
			$attempt_person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($this->in->getString('email'));
			if ($attempt_person && $attempt_person->getPref('agent_notif.login_attempt_fail.email')) {
				$message = $this->container->getMailer()->createMessage();
				$message->setTemplate('DeskPRO:emails_agent:login-alert.html.twig', array('success' => false, 'session' => $this->session->getEntity()));
				$message->setTo($attempt_person->getPrimaryEmailAddress(), $attempt_person->getDisplayName());
				$this->container->getMailer()->send($message);
			}

			// Save login log
			if ($attempt_person) {
				$this->db->insert('login_log', array(
					'person_id'    => $attempt_person->getId(),
					'area'         => DP_INTERFACE == 'admin' ? 'admin' : 'agent',
					'is_success'   => 0,
					'ip_address'   => App::getRequest()->getClientIp(),
					'hostname'     => @gethostbyaddr(App::getRequest()->getClientIp()) ?: '',
					'user_agent'   => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
					'date_created' => date('Y-m-d H:i:s')
				));
			}

			$this->session->set('failed_login_name', $this->in->getString('email'));
			$this->session->save();
			return $this->redirectRoute($this->route_prefix . '_login', array('return' => $return));
		}

		$identity = $result->getIdentity();

		$person = $identity['person'];
		$person->setLastLoginAt();
		$this->em->persist($person);
		$this->em->flush();

		$this->session->set('auth_person_id', $identity->getIdentity());
		$this->session->set('dp_interface', DP_INTERFACE);
		$this->session->save();

		if ($person['is_agent']) {

			// Set their status to available by default
			$this->session->set('active_status', 'available');
			$this->session->set('is_chat_available', 1);

			$data = array(
				'agent_id'   => $person['id'],
				'agent_name' => $person['display_name'],
				'agent_short_name' => $person->getDisplayContactShort(4),
				'picture_url' => $person->getPictureUrl(10)
			);

			// Announce if its an agent
			$cm = new \Application\DeskPRO\Entity\ClientMessage();
			$cm->fromArray(array(
				'channel' => 'agent.new-agent-online',
				'data' => $data,
				'created_by_client' => $this->session->getEntityId(),
			));

			// Send alert
			if ($person->getPref('agent_notif.login_attempt.email')) {
				$message = $this->container->getMailer()->createMessage();
				$message->setTemplate('DeskPRO:emails_agent:login-alert.html.twig', array('success' => true, 'session' => $this->session->getEntity()));
				$message->setTo($person->getPrimaryEmailAddress(), $person->getDisplayName());
				$this->container->getMailer()->send($message);
			}

			// Login log
			$this->db->insert('login_log', array(
				'person_id'    => $person->getId(),
				'area'         => DP_INTERFACE == 'admin' ? 'admin' : 'agent',
				'is_success'   => 1,
				'ip_address'   => App::getRequest()->getClientIp(),
				'hostname'     => @gethostbyaddr(App::getRequest()->getClientIp()) ?: '',
				'user_agent'   => empty($_SERVER['HTTP_USER_AGENT']) ? '' : $_SERVER['HTTP_USER_AGENT'],
				'date_created' => date('Y-m-d H:i:s')
			));

			$this->em->persist($cm);
			$this->em->flush();
		}

		// Remember me cookie
		if ($this->in->getBool('remember_me')) {
			$cookie = \Application\DeskPRO\HttpFoundation\Cookie::makeCookie('dpreme', $person->getId() . '-' . $person->getRememberMeCookieCode(), 'never');
			$cookie->send();
		}

		if ($return) {
			return $this->redirect($return);
		} else {
			return $this->redirectRoute($this->route_prefix);
		}
	}


	/**
	 * A generic landing page after the user has logged in which has JS to alert
	 * its parent that the user is now logged in.
	 *
	 * This is used when the auth happens in a popup, and then the page that spawned
	 * the popup needs to know the user is finished.
	 */
	public function jstellLoginAction($security_token, $usersource_id)
	{
		if (!$this->session->getEntity()->checkSecurityToken('jstell', $security_token)) {
			return $this->createResponse('');
		}

		return $this->render('UserBundle:Login:jstell.html.twig', array(

		));
	}


	public function authLocalInput()
	{
		#------------------------------
		# Auth local
		#------------------------------

		if ($this->container->getSetting('core.deskpro_source_enabled')) {
			$adapter = new \Application\DeskPRO\Auth\Adapter\Local(App::getOrm());
			$adapter->setCredentials($this->in->getString('email'), $this->in->getString('password'));
			$result = $adapter->authenticate();

			if ($result->isValid()) {
				return $result;
			}
		}

		#------------------------------
		# Auth usersources that accept local input
		#------------------------------

		$usersources = $this->em->getRepository('DeskPRO:Usersource')->getLocalInputUsersources();
		foreach ($usersources as $us) {

			/** @var $us \Application\DeskPRO\Entity\Usersource */
			$adapter = $this->_initUserSourceAdapter($us);
			$adapter->setFormData(array(
				'username' => $this->in->getString('email'),
				'password' => $this->in->getString('password')
			));
			$result = $adapter->authenticate();

			if ($result->isValid()) {
				$login_processor = new LoginProcessor($us, $result->getIdentity());
				$person = $login_processor->getPerson();

				$identity = new \Orb\Auth\Identity($person->id, array('person' => $person));
				$result = new \Orb\Auth\Result(\Orb\Auth\Result::SUCCESS, $identity);

				return $result;
			}
		}

		return new \Orb\Auth\Result(\Orb\Auth\Result::FAILURE_INVALID_CREDS);
	}


	############################################################################
	# Usersource auth
	############################################################################

	public function authenticateAction($usersource_id)
	{
		$return = $this->in->getString('return');

		$usersource = $this->em->find('DeskPRO:Usersource', $usersource_id);
		$adapter = $this->_initUserSourceAdapter($usersource, $this->in->getString('context'));

		#------------------------------
		# Callback types require us to redirect
		#------------------------------

		if ($adapter instanceof \Orb\Auth\Adapter\CallbackInterface) {
			$result = $adapter->authenticate();

			// The user is already logged in
			if ($result->isValid()) {

				$login_processor = new LoginProcessor($usersource, $result->getIdentity());
				$person = $login_processor->getPerson();
				$person->setLastLoginAt();

				$this->em->persist($person);
				$this->em->flush();

				$this->session->set('auth_person_id', $person->id);
				$this->session->set('dp_interface', DP_INTERFACE);
				$this->session->set('auth_usersource_id', $usersource->id);
				$this->session->set('auth_usersource_type', $usersource->source_type);
				$this->session->set('usersource_display_name', $usersource->getAdapter()->getDisplayName($result->getIdentity()->getRawData()));
				$this->session->set('usersource_display_link', $usersource->getAdapter()->getDisplayLink($result->getIdentity()->getRawData()));

				$this->session->save();

				if ($this->in->getString('js_tell')) {
					$return = $this->generateUrl('user_jstell_login', array(
						'jstell' => $this->in->getString('js_tell'),
						'security_token' => $this->session->getEntity()->generateSecurityToken('jstell'),
						'usersource_id' => $usersource_id
					));
					return $this->redirect($return);
				}

				if ($this->session->get('auth_return')) {
					$return = $this->session->get('auth_return');
					$this->session->remove('auth_return');
					$this->session->save();
					return $this->redirect($return);
				} else {
					return $this->redirectRoute($this->route_prefix);
				}

			// We expect a redirect to be rquired
			} elseif ($result->isRedirectRequired()) {

				$return = $this->in->getString('return');
				$this->session->set('auth_return', $return);

				if ($this->in->getString('js_tell')) {
					$return = $this->generateUrl('user_jstell_login', array(
						'jstell' => $this->in->getString('js_tell'),
						'security_token' => $this->session->getEntity()->generateSecurityToken('jstell'),
						'usersource_id' => $usersource_id
					), true);
					$this->session->set('auth_return', $return);
				}

				$this->session->save();

				return $this->redirect($result->getRedirectUrl());

			// Otherwise its an error
			} else {
				$this->session->setFlash('login_failed', true);
				return $this->redirectRoute($this->route_prefix . '_login', array('return' => $return));
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
				$this->session->set('auth_usersource_id', $usersource->id);
				$this->session->set('auth_usersource_type', $usersource->source_type);
				$this->session->set('usersource_display_name', $usersource->getAdapter()->getDisplayName($result->getIdentity()->getRawData()));
				$this->session->set('usersource_display_link', $usersource->getAdapter()->getDisplayLink($result->getIdentity()->getRawData()));
				$this->session->save();

				$return = $this->in->getString('return');
				if ($return) {
					return $this->redirect($return);
				} else {
					return $this->redirectRoute($this->route_prefix);
				}

			// Error, go back to login
			} else {
				$this->session->setFlash('login_failed', true);
				return $this->redirectRoute($this->route_prefix . '_login', array('return' => $return));
			}
		}
	}

	public function authenticateCallbackAction($usersource_id)
	{
		$return = $this->in->getString('return');
		$usersource = $this->em->find('DeskPRO:Usersource', $usersource_id);

		$adapter = $this->_initUserSourceAdapter($usersource);

		// It must be a callback type to be here, so if not redirect back to login
		if (!($adapter instanceof \Orb\Auth\Adapter\CallbackInterface)) {
			$this->session->setFlash('login_failed', true);
			return $this->redirectRoute($this->route_prefix . '_login', array('return' => $return));
		}

		$adapter->setCallbackContext($_REQUEST);

		$result = $adapter->authenticate();

		// Valid
		if ($result->isValid()) {

			$login_processor = new LoginProcessor($usersource, $result->getIdentity());
			$person = $login_processor->getPerson();

			$this->session->set('auth_person_id', $person['id']);
			$this->session->set('auth_usersource_id', $usersource->id);
			$this->session->set('auth_usersource_type', $usersource->source_type);
			$this->session->set('usersource_display_name', $usersource->getAdapter()->getDisplayName($result->getIdentity()->getRawData()));
			$this->session->set('usersource_display_link', $usersource->getAdapter()->getDisplayLink($result->getIdentity()->getRawData()));

			$this->session->save();

			if ($this->session->get('auth_return')) {
				$return = $this->session->get('auth_return');
				$this->session->remove('auth_return');
				$this->session->save();
				return $this->redirect($return);
			} else {
				return $this->redirectRoute($this->route_prefix);
			}

		// Error, go back to login
		} else {
			$this->session->setFlash('login_failed', true);
			return $this->redirectRoute($this->route_prefix . '_login', array('return' => $return));
		}
	}

	public function resetPasswordAction($invalid_email = false, $invalid_code = false)
	{
		$register = new \Application\UserBundle\Form\Model\Register();
		$reg_formtype = new \Application\UserBundle\Form\RegisterType();
		$form = $this->get('form.factory')->create($reg_formtype, $register);

		return $this->render($this->tpl_prefix . ':reset-password.html.twig', array(
			'invalid_email' => $invalid_email,
			'invalid_code' => $invalid_code,
			'form' => $form->createView(),
		));
	}

	protected function _initUserSourceAdapter($usersource, $context = null)
	{
		$adapter = $usersource->getAdapter()->getAuthAdapter();

		if (App::getConfig('debug.enable_usersource_log') && $adapter instanceof \Orb\Log\Loggable) {
			$adapter->setLogger($this->_getAdapterLogger());
		}

		if ($adapter instanceof \Orb\Auth\Adapter\FormLoginInterface) {
			$adapter->setFormData($_POST);
		}

		if ($context && $adapter instanceof \Orb\Auth\Adapter\DisplayContextInterface) {
			$adapter->setDisplayContext($context);
		}

		if ($adapter instanceof \Orb\Auth\Adapter\CallbackInterface) {
			$adapter->setCallbackUrl(
				$this->container->getSetting('core.helpdesk_url') .
				$this->generateUrl('user_login_callback', array('usersource_id' => $usersource['id']), false)
			);
		}

		if ($adapter instanceof \Orb\Auth\Adapter\SessionStateInterface) {
			$auth_state = new \Orb\Auth\StateHandler\ArrayAccessWrapper($this->session);
			$auth_state->setClearStateMethod('clear');

			$adapter->setStateHandler($auth_state);
		}

		return $adapter;
	}

	protected function _getAdapterLogger()
	{
		static $logger = null;

		if ($logger === null) {
			$logger = new \Orb\Log\Logger();
			$logger->addWriter(new \Orb\Log\Writer\Stream($this->container->getLogDir() . '/usersource_log.log'));
		}

		return $logger;
	}

	############################################################################
	# Resetting passwords
	############################################################################

	public function sendResetPasswordAction()
	{
		$email = $this->in->getString('email');
		$person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($email);

		if (!$person) {

			// If no user was found in our database, then the account might not have
			// been set up yet. For adapters that support it, we can still see if we
			// can be helpful and redirect to another source they exist in
			$usersources = $this->em->getRepository('DeskPRO:Usersource')->getUserInfoFetchableUsersources();
			foreach ($usersources as $us) {
				$found = $us->getUserInfoFromIdentity($email, 'email');
				if ($found && $us->lost_password_url) {
					return $this->redirect($us->lost_password_url);
				}
			}

			if ($this->request->isXmlHttpRequest()) {
				return $this->createJsonResponse(array('error' => 'invalid_email'));
			}
			return $this->resetPasswordAction(true);
		}

		// If they dont have a password, this either means they're not a user yet,
		// but could also mean they registered through a usersource which means they might
		// need to use a different reset URL
		if (!$person->password) {
			$associations = $this->em->getRepository('DeskPRO:PersonUsersourceAssoc')->getAssociationsForPerson($person);
			foreach ($associations as $assoc) {
				if ($assoc->usersource->lost_password_url) {
					return $this->redirect($assoc->usersource->lost_password_url);
				}
			}
		}

		// If they're still here, then we just send them through the normal DeskPRO reset procedure

		$code_data = TmpData::create('reset-password', array('person_id' => $person['id']), '+2 days');
		$this->em->persist($code_data);
		$this->em->flush();

		$vars = array(
			'code' => $code_data->getCode(),
			'person' => $person,
			'email' => $email
		);

		$message = $this->container->getMailer()->createMessage();
		$message->setTemplate('DeskPRO:emails_user:reset-password.html.twig', $vars);
		$message->setTo($email, $person->getDisplayName());

		$this->container->getMailer()->send($message);

		if ($this->request->isXmlHttpRequest()) {
			return $this->createJsonResponse(array('success' =>1 ));
		}

		$this->_logoutPerson();

		return $this->render($this->tpl_prefix . ':reset-password-sent.html.twig', array());
	}

	public function resetPasswordNewPassAction($code)
	{
		$code_data = $this->em->getRepository('DeskPRO:TmpData')->getByCode($code, 'reset-password');
		$person = null;
		if ($code_data) {
			$person = $this->em->find('DeskPRO:Person', $code_data->getData('person_id', 0));
		}

		if (!$code_data OR !$person) {
			throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
		}

		$errors = array();
		if ($this->in->getBool('process')) {
			$pass = $this->in->getString('password');
			$pass2 = $this->in->getString('password2');

			if ($pass != $pass2) {
				$errors['password.mismatch'] = 1;
			} elseif (\Orb\Util\Strings::utf8_strlen($pass) < 5) {
				$errors['password.short'] = 1;
			}

			if (!$errors) {
				$person->setPassword($pass);
				$this->em->transactional(function ($em) use ($person, $code_data) {
					$em->persist($person);
					$em->remove($code_data);
					$em->flush();
				});

				$this->session->setFlash('password_reset', 1);
				return $this->redirectRoute($this->route_prefix . '_login');
			}
		}

		return $this->render($this->tpl_prefix . ':reset-password-newpass.html.twig', array(
			'code' => $code_data->getCode(),
			'errors' => $errors
		));
	}

	public function resetPasswordNewPassQueryCode()
	{
		return $this->resetPasswordNewPassAction($this->in->getString('reset_code'));
	}


	############################################################################
	# Inline login
	############################################################################

	public function inlineLoginAction()
	{
		$adapter = new \Application\DeskPRO\Auth\Adapter\Local(App::getOrm());
		$adapter->setCredentials($this->in->getString('email'), $this->in->getString('password'));
		$result = $adapter->authenticate();

		if (!$result->isValid()) {
			$html = $this->renderView('UserBundle:Common:form-email-login-row.html.twig', array('login_error' => true, 'mode' => $this->in->getString('mode')));
			return $this->createJsonResponse(array(
				'html' => $html,
			));
		}

		$identity = $result->getIdentity();

		$this->session->set('auth_person_id', $identity->getIdentity());

		$person = $identity['person'];
		$person->setLastLoginAt();
		$person->loadHelper('FeedbackVotes', array(
			'visitor' => $this->session->getVisitor()
		));
		$person->loadHelper('HelpdeskUser', array(
			'session' => $this->session,
			'visitor' => $this->session->getVisitor()
		));

		$this->person = $person;

		App::setCurrentPerson($person);

		$this->em->persist($person);
		$this->em->flush();

		$html = $this->renderView('UserBundle:Common:form-email-login-row.html.twig', array('person' => $person, 'mode' => $this->in->getString('mode')));

		return $this->createJsonResponse(array(
			'html' => $html,
			'sections_replace' => array(

			),
			'person_id' => $person['id'],
			'name' => $person['name']
		));
	}
}
