<?php

namespace Application\AdminBundle\Controller;

use Application\DeskPRO\App;

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
		$this->person->loadHelper('HelpMessages');
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
			return $this->redirect($this->get('router')->generate('agent_login', array('return' => $return)));
		}

		if (!$this->_userHasPermissions()) {
			// TODO implement no perms
		}
	}

	protected function _userHasPermissions()
	{
		if ($this->person['is_agent']) {
			return true;
		}

		return false;
	}

	protected function rememberLastPage($url = null)
	{
		if (!$url) {
			$url = App::getRequest()->getRequestUri();
		}
		App::getSession()->set('admin_last_page', $url);
	}
}
