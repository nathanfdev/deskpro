<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Tech
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\TechBundle\Listener;

use \Symfony\Component\EventDispatcher\Event;
use \Symfony\Framework\Debug\EventDispatcher;
use \Symfony\Component\DependencyInjection\ContainerInterface;

class LoginListener
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

	public function register(EventDispatcher $dispatcher)
	{
		$dispatcher->connect('core.controller', array($this, 'handle'));
	}


	public function handle(Event $event, $controller)
	{
		// Dont touch if we're already the login controller
		if ($controller[0] instanceof \Application\TechBundle\Controller\LoginController) {
			return $controller;
		}

		// Or if we aren't a proper tech controller
		if (!($controller[0] instanceof \Application\TechBundle\Controller\AbstractController)) {
			return $controller;
		}

		// Or if we're logged in already
		$request = $event->getParameter('request');
		$person = $request->getSession()->getPerson();
		if ($person AND $person['id'] AND $person['is_user']) {
			return $controller;
		}

		// Otherwise, rewrite to login
		$controller[1] = 'userRequriedAction';

		return $controller;
	}
}