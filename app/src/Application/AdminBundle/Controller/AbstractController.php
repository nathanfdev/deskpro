<?php

namespace Application\AdminBundle\Controller;

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
			return $this->renderStandardPermissionError('You do not have permission to use the admin interface.');
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
		$tpl_standard = 'AdminBundle:Main:error-standard.html.twig';
		$tpl_specific = "AdminBundle:Main:error-{$code}.html.twig";

		$tpl = $tpl_standard;
		if (App::getTemplating()->exists($tpl_specific)) {
			$tpl = $tpl_specific;
		}

		$vars = array_merge($vars, array(
			'error_message' => $error_message,
			'error_title'   => $error_title
		));

		$res = $this->render($tpl, $vars);

		$res->setStatusCode($code);

		return $res;
	}

	/**
	 * Render a standard permission error message.
	 *
	 * @param string $error_message
	 * @param string $error_title
	 * @return Response
	 */
	public function renderStandardPermissionError($error_message = '', $error_title = '', $code = 200, array $vars = array())
	{
		$tpl = 'AdminBundle:Main:error-permission.html.twig';

		$vars = array_merge($vars, array(
			'error_message' => $error_message,
			'error_title'   => $error_title
		));

		$res = $this->render($tpl, $vars);

		$res->setStatusCode($code);

		return $res;
	}

	/**
	 * Standard error displayed when we're given an invalid security token.
	 *
	 * @return Response
	 */
	public function renderStandardTokenError()
	{
		return $this->renderStandardError('The page you are trying to access has expired. Go back, refresh, and try again.');
	}
}
