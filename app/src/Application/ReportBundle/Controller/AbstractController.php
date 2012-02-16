<?php

namespace Application\ReportBundle\Controller;

use Application\DeskPRO\App;

abstract class AbstractController extends \Application\DeskPRO\Controller\AbstractController
{
	/**
	 * The currently logged in person.
	 * @var \Application\DeskPRO\Bundle\DeskPRO\Entity\Person
	 */
	public $person;

	protected function init()
	{
		parent::init();

		$this->person = $this->session->getPerson();
		$this->person->loadHelper('HelpMessages');

		if (!$this->person->id) {
			$cas = new \Application\AgentBundle\Controller\Helper\CarryAdminSession($this);
			$cas->process();
		}

		$dashboards = App::getEntityRepository('DeskPRO:ReportDashboard')->getDashboards();
		$this->get('templating.globals')->setVariable('dashboards', $dashboards);
	}


	/**
	 * Force a login
	 */
	public function preAction($action, $arguments = null)
	{
		if (!$this->person['id']) {
			if ($this->isPostRequest()) {
				$return = $this->get('router')->generate('report');
			} else {
				$return = $this->request->getRequestUri();
			}

			return $this->redirect($this->get('router')->generate('report_login', array('return' => $return)));
		}

		if (!$this->_userHasPermissions()) {
			// TODO implement no perms
		}
	}

	protected function _userHasPermissions()
	{
		if ($this->person->is_agent && $this->person->can_reports) {
			return true;
		}

		return false;
	}

	protected function rememberLastPage($url = null)
	{
		if (!$url) {
			$url = App::getRequest()->getRequestUri();
		}
		App::getSession()->set('report_last_page', $url);
	}
}
