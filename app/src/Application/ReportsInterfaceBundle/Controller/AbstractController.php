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
*/

namespace Application\ReportsInterfaceBundle\Controller;

use Application\DeskPRO\App;

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
		if (!$this->person['id']) {
			if ($this->isPostRequest()) {
				$return = $this->get('router')->generate('reports');
			} else {
				$return = $this->request->getRequestUri();
			}
		}

		if (!$this->_userHasPermissions()) {
			if ($this->request->isXmlHttpRequest()) {
				$data = array('error' => 'session_expired');
				return $this->createJsonResponse($data, 403);
			}

			return $this->redirectRoute('agent');
		}

		if ($this->requireRequestToken($action, $arguments) && !$this->checkRequestToken('request_token', '_rt')) {
			if ($this->request->isXmlHttpRequest()) {
				$data = array(
					'error' => 'invalid_request_token',
					'redirect_login' => $this->generateUrl('agent_login')
				);

				return $this->createJsonResponse($data, 403);
			} else {
				return $this->renderStandardPermissionError('The form you are trying to submit has expired. Please go back and try again.');
			}
		}

		if (!CheckWhitelistedIP::checkIP($this->container, $this->person)) {
			return $this->render('AgentBundle:Login:whitelist-ip.html.twig', array(
				'ip' => dp_get_user_ip_address()
			));
		}

		return null;
	}

	protected function _userHasPermissions()
	{
		if ($this->person->is_agent && $this->person->can_reports) {
			return true;
		}

		return false;
	}
}
