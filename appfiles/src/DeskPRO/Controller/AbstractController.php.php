<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Controller
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Controller;

/**
 * The abstract controller sets up some default objects.
 */
class AbstractController extends Symfony\Bundle\FrameworkBundle\Controller
{
	/**
	 * @var DeskPRO\Entities\User
	 */
	protected $user;

	function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->user = $this->container->get('deskpro.core.requestuser');

		$this->init();
	}

	/**
	 * An empty callback function
	 */
	protected function init()
	{

	}
}