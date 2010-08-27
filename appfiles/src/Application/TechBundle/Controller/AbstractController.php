<?php

namespace Application\TechBundle\Controller;

abstract class AbstractController extends \DeskPRO\Controller\AbstractController
{
	/**
	 * The currently logged in user
	 * @var \DeskPRO\Bundle\Core\Entity\User
	 */
	protected $user;

	protected function init()
	{
		$this->user = $this['deskpro.core.requestuser'];
		$this->tplvars['user'] = $this->user;
	}

	public function isUserRequired()
	{
		return true;
	}

	public function userRequriedAction()
	{
		return $this->forward('TechBundle:Login:index');
	}
}