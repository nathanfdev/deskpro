<?php

namespace Application\AgentBundle\Controller;

abstract class AbstractController extends \Application\DeskPRO\Controller\AbstractController
{
	/**
	 * The currently logged in person.
	 * TODO: Rename this to user? Agent?
	 * @var \Application\DeskPRO\Bundle\DeskPRO\Entity\Person
	 */
	protected $person;

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
			return $this->redirect($this->get('router')->generate('user_login', array('return' => $return)));
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
	 * @return Application\DeskPRO\Entity\Person
	 */
	public function getPerson()
	{
		return $this->person;
	}
}