<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
*/

namespace Application\AdminInterfaceBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Service\CheckWhitelistedIP;

abstract class AbstractController extends \Application\DeskPRO\Controller\AbstractController
{
	/**
	 * The currently logged in person.
	 * @var \Application\DeskPRO\Entity\Person
	 */
	public $person;

	protected function init()
	{
		parent::init();
		$this->person = $this->session->getPerson();
	}

	/**
	 * Check if the global request token check is required for the request
	 */
	public function requireRequestToken($action, $arguments = null)
	{
		// Pre install we dont have a secret yet
		// So dont require the request token on POSTs
		// while we fill out setup form
		if (!App::getSetting('core.setup_initial')) {
			return false;
		}

		if ($this->request->getMethod() == 'POST') {
			return true;
		}

		return false;
	}

	/**
	 * Force a login
	 */
	public function preAction($action, $arguments = null)
	{
		$return = '';
		if (!$this->person['id']) {
			if ($this->isPostRequest()) {
				$return = $this->get('router')->generate('admin');
			} else {
				$return = $this->request->getRequestUri();
			}
		}

		if (!$this->_userHasPermissions()) {
			if ($this->request->isXmlHttpRequest()) {
				$data = array('error' => 'session_expired');
				return $this->createJsonResponse($data, 403);
			}

			return $this->render('AgentBundle:Login:redirect-login.html.twig', array(
				'return' => $return
			));
		}

		if ($this->requireRequestToken($action, $arguments) && !$this->checkRequestToken('request_token', '_rt')) {
			if ($this->request->isXmlHttpRequest()) {
				$data = array(
					'error' => 'invalid_request_token',
					'redirect_login' => $this->generateUrl('agent_login')
				);

				return $this->createJsonResponse($data, 403);
			} else {
				return $this->standardErrorResponse('The form you are trying to submit has expired. Please go back and try again.');
			}
		}

		if (!CheckWhitelistedIP::checkIP($this->container, $this->person)) {
			return $this->render('AgentBundle:Login:whitelist-ip.html.twig', array(
				'ip' => dp_get_user_ip_address()
			));
		}

		return null;
	}

	/**
	 * @param string $error_message
	 * @param string $error_title
	 * @param int    $code
	 * @param array  $vars
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function standardErrorResponse($error_message = '', $error_title = '', $code = 200, array $vars = array())
	{
		$tpl_standard = 'UserBundle:Main:error-standard.html.twig';
		$tpl_specific = "UserBundle:Main:error-{$code}.html.twig";

		$tpl = $tpl_standard;
		if (App::getTemplating()->exists($tpl_specific)) {
			$tpl = $tpl_specific;
		}

		$vars = array_merge(
			$vars, array(
				'error_message' => $error_message,
				'error_title'   => $error_title
			)
		);

		$res = $this->render($tpl, $vars);

		$res->setStatusCode($code);

		return $res;
	}

	protected function _userHasPermissions()
	{
		if ($this->person->is_agent && $this->person->can_admin) {
			return true;
		}

		return false;
	}
}
