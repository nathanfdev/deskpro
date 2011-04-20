<?php

namespace Application\UserBundle\Controller;

use Application\DeskPRO\App;

abstract class AbstractController extends \Application\DeskPRO\Controller\AbstractController
{
	/**
	 * The currently logged in person.
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	protected function init()
	{
		parent::init();

		$this->person = $this->session->getPerson();
		$this->tplvars['person'] = $this->person;
	}



	/**
	 * Render a standard error message.
	 * 
	 * @param string $error_message
	 * @param string $error_title
	 * @return Response
	 */
	public function renderStandardError($error_message, $error_title = '', $code = 200)
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
			'code'          => $code
		));
	}
}