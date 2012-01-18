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
				$return = $this->get('router')->generate('admin');
			} else {
				$return = $this->request->getRequestUri();
			}

			return $this->redirect($this->get('router')->generate('admin_login', array('return' => $return)));
		}

		if (!$this->_userHasPermissions()) {
			return $this->redirect($this->get('router')->generate('admin_login', array('return' => $return)));
		}

		$setup_guide = new \Application\AdminBundle\SetupGuide($this->container, $this);
		$this->container->get('templating.globals')->setVariable('setup_guide', $setup_guide);
		return $setup_guide->preActionHelper($action, $arguments);
	}

	protected function _userHasPermissions()
	{
		if ($this->person->is_agent && $this->person->can_admin) {
			return true;
		}

		return false;
	}

	/**
	 * Render a standard error message.
	 *
	 * @param string $error_message
	 * @param string $error_title
	 * @return Response
	 */
	public function renderStandardError($error_message = '', $error_title = '', $code = 200, array $vars = array())
	{
		if ($error_message AND $error_message[0] == '@') {
			$error_message = App::getTranslator()->getPhraseText(substr($error_message, 1));
		}

		if ($error_title AND $error_title[0] == '@') {
			$error_title = App::getTranslator()->getPhraseText(substr($error_title, 1));
		}

		return $this->forward('AdminBundle:Main:standardError', array(
			'error_message' => $error_message,
			'error_title'   => $error_title,
			'code'          => $code,
			'vars'          => $vars
		));
	}
}
