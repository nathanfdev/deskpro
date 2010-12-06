<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category ORM
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DeskPRO\ORM\EventListener;

use \Doctrine\ORM\Events;
use \Doctrine\ORM\Event\LifecycleEventArgs;
use \Doctrine\Common\EventSubscriber;

/**
 * This listener automatically sets the container once an ORM entity has been laoded.
 */
class SetContainerListener implements EventSubscriber
{
	protected $container;

	public function __construct(\Symfony\Component\DependencyInjection\Container $container)
	{
		$this->container = $container;
	}

	public function getSubscribedEvents()
	{
	   return array(Events::postLoad);
	}

	public function postLoad(LifecycleEventArgs $args)
	{
		$entity = $args->getEntity();

		if ($entity instanceof Entity) {
			if (!$entity->hasContainer()) {
				$entity->setContainer($container);
			}
		}
	}
}
