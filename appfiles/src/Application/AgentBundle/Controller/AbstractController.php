<?php

namespace Application\AgentBundle\Controller;

use \Application\DeskPRO\App;

abstract class AbstractController extends \Application\DeskPRO\Controller\AbstractController
{
	/**
	 * The currently logged in person.
	 * TODO: Rename this to user? Agent?
	 * @var \Application\DeskPRO\Bundle\DeskPRO\Entity\Person
	 */
	public $person;

	protected function init()
	{
		parent::init();

		$this->person = $this->session->getPerson();
		$this->tplvars['person'] = $this->person;
	}


	/**
	 * Force a login
	 */
	public function preAction($action, $arguments = null)
	{
		if (!$this->person['id']) {
			if ($this->isPostRequest()) {
				$return = $this->get('router')->generate('agent');
			} else {
				$return = $this->request->getRequestUri();
			}


			if ($this->request->isXmlHttpRequest()) {
				$redirect_url = $this->get('router')->generate('agent_login', array('return' => $this->get('router')->generate('agent')));
				return $this->createJsonResponse(array('error' => 'session_expired', 'redirect_login' => $redirect_url), 403);
			}

			$redirect_url = $this->get('router')->generate('agent_login', array('return' => $return));
			return $this->redirect($redirect_url);
		}

		if (!$this->_userHasPermissions()) {
			// TODO implement no perms
		}

		$this->person->loadHelper('AgentTeam');
		$this->person->loadHelper('AgentPermissions');
	}

	protected function _userHasPermissions()
	{
		if ($this->person['is_agent']) {
			return true;
		}

		return false;
	}



	/**
	 * Create a reponse that indicates a permissions error.
	 *
	 * @param string $message The message to show the user
	 * @return Response
	 */
	protected function createPermissionErrorResponse($message)
	{
		return $this->createJsonResponse(array('error' => 'not_allowed', 'message' => $message), 403);
	}


	/**
	 * @return Application\DeskPRO\Entity\Person
	 */
	public function getPerson()
	{
		return $this->person;
	}
}