<?php

namespace Application\TechCoreBundle\Controller;

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
		if (!$this->user OR !$this->user['id']) {
			$this->redirect($this['router']->generate('TechCoreBundle:Login:index'));
			return;
		}

		$this->tpl['user'] = $this->user;
	}
}