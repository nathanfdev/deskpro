<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage A[iBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\AgentBundle\Listener;

use \Symfony\Component\EventDispatcher\Event;
use \Symfony\Framework\Debug\EventDispatcher;
use \Symfony\Component\DependencyInjection\ContainerInterface;

class ApiKeyListener
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
		// Dont care if we arent a proper API controller
		if (!($controller[0] instanceof \Application\ApiBundle\Controller\AbstractController)) {
			return $controller;
		}

		// If we do have the API key, then we're goo
		$apikey = $this->container->get('deskpro.api.requestapikey');
		if ($apikey['id']) {
			return $controller;
		}

		// Otherwise, rewrite to login
		$controller[1] = 'apiKeyRequriedAction';

		return $controller;
	}
}