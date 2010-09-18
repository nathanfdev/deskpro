<?php

namespace Application\TechBundle\Controller;

abstract class AbstractController extends \DeskPRO\Controller\AbstractController
{
	/**
	 * The currently logged in person
	 * @var \DeskPRO\Bundle\CoreBundle\Entity\Person
	 */
	protected $person;

	protected function init()
	{
		$this->person = $this->session->getPerson();
		$this->tplvars['person'] = $this->person;
	}

	public function isUserRequired()
	{
		return true;
	}

	public function userRequriedAction()
	{
		return $this->forward('UserBundle:Login:index');
	}
}