<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category DBAL
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\DBAL;

use Symfony\Bundle\FrameworkBundle\ContainerAwareEventDispatcher;

use \Doctrine\ORM\Event\LifecycleEventArgs;
use \Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use \Doctrine\ORM\Event\PreUpdateEventArgs;
use \Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * This connects some of the Doctrine events to the symfony event dispatcher
 */
class SymfonyEventConnector implements \Doctrine\Common\EventSubscriber
{
	/**
	 * @var \Symfony\Bundle\FrameworkBundle\ContainerAwareEventDispatcher
	 */
	protected $event_dispatcher;

	/**
	 * @param \Symfony\Bundle\FrameworkBundle\ContainerAwareEventDispatcher $event_dispatcher
	 */
	public function __construct(ContainerAwareEventDispatcher $event_dispatcher)
	{
		$this->event_dispatcher = $event_dispatcher;
	}

	public function preRemove($event)
	{
		$event = new DoctrineEvent('preRemove', $event);
		$this->event_dispatcher->dispatch('Doctrine_onPreRemove', $event);
	}

	public function postRemove($event)
	{
		$event = new DoctrineEvent('postRemove', $event);
		$this->event_dispatcher->dispatch('Doctrine_onPostRemove', $event);
	}

	public function prePersist($event)
	{
		$event = new DoctrineEvent('prePersist', $event);
		$this->event_dispatcher->dispatch('Doctrine_onPrePersist', $event);
	}

	public function postPersist($event)
	{
		$event = new DoctrineEvent('postPersist', $event);
		$this->event_dispatcher->dispatch('Doctrine_onPostPersist', $event);
	}

	public function preUpdate($event)
	{
		$event = new DoctrineEvent('preUpdate', $event);
		$this->event_dispatcher->dispatch('Doctrine_onPreUpdate', $event);
	}

	public function postUpdate($event)
	{
		$event = new DoctrineEvent('postUpdate', $event);
		$this->event_dispatcher->dispatch('Doctrine_onPostUpdate', $event);
	}

	public function postLoad($event)
	{
		$event = new DoctrineEvent('postLoad', $event);
		$this->event_dispatcher->dispatch('Doctrine_onPostLoad', $event);
	}

	public function loadClassMetadata($event)
	{
		$event = new DoctrineEvent('loadClassMetadata', $event);
		$this->event_dispatcher->dispatch('Doctrine_onLoadClassMetadata', $event);
	}

	public function onFlush($event)
	{
		$event = new DoctrineEvent('onFlush', $event);
		$this->event_dispatcher->dispatch('Doctrine_onFlush', $event);
	}

	public function getSubscribedEvents()
	{
		return array(
			'preRemove',
			'postRemove',
			'prePersist',
			'postPersist',
			'preUpdate',
			'postUpdate',
			'postLoad',
			'loadClassMetadata',
			'onFlush'
		);
	}
}
