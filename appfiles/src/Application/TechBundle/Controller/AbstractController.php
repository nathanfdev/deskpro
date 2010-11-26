<?php

namespace Application\TechBundle\Controller;

abstract class AbstractController extends \DeskPRO\Controller\AbstractController
{
	/**
	 * The currently logged in person.
	 * TODO: Rename this to user? Agent?
	 * @var \DeskPRO\Bundle\CoreBundle\Entity\Person
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
			return $this->redirect($this->get('router')->generate('user_login', array()));
		}

		if (!$this->_userHasPermissions()) {
			// TODO implement no perms
		}
	}
	
	protected function _userHasPermissions()
	{
		if ($this->person['is_tech']) {
			return true;
		}
		
		return false;
	}
}