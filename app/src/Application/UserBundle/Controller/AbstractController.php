<?php

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;
use Orb\Util\Strings;

abstract class AbstractController extends \Application\DeskPRO\Controller\AbstractController
{
	/**
	 * The currently logged in person.
	 * @var \Application\DeskPRO\Entity\Person
	 */
	public $person;

	protected $search_query = '';

	protected function init()
	{
		parent::init();

		$tpl_globals = $this->container->get('templating.globals');
		if (!$tpl_globals->getVariable('usersources')) {
			 $tpl_globals->setVariable('usersources', App::getEntityRepository('DeskPRO:Usersource')->getAllUsersources());
		}

		if ($this->in->getString('q')) {
			$this->search_query = $this->in->getString('q');
		} else {
			$referrer = $this->request->headers->get('Referer');
			if ($referrer && ($q = Strings::extractRegexMatch('#search\?q=(.*?)(&|$)#', $referrer, 1))) {
				$this->search_query = urldecode($q);
			}
		}
		$tpl_globals->setVariable('search_query', $this->search_query);
	}

	public function preAction($action, $arguments = null)
	{
		$this->person = $this->session->getPerson();

		if (!$this->person->id) {
			$cas = new \Application\AgentBundle\Controller\Helper\CarryAdminSession($this);
			$cas->process();
		}

		$this->person->loadHelper('FeedbackVotes', array(
			'visitor' => $this->session->getVisitor()
		));
		$this->person->loadHelper('HelpdeskUser', array(
			'session' => $this->session,
			'visitor' => $this->session->getVisitor()
		));

		if ($this instanceof RequireUserInterface) {
			if (!$this->person['id']) {
				if ($this->isPostRequest()) {
					$return = $this->get('router')->generate('user');
				} else {
					$return = $this->request->getRequestUri();
				}

				$redirect_url = $this->get('router')->generate('user_login', array('return' => $return));
				return $this->redirect($redirect_url);
			}
		}
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

		return $this->forward('UserBundle:Main:standardError', array(
			'error_message' => $error_message,
			'error_title'   => $error_title,
			'code'          => $code,
			'vars'          => $vars
		));
	}

	/**
	 * @return Response
	 */
	public function renderStandardTokenError()
	{
		return $this->renderStandardError('@user.error_expired_token');
	}
}
