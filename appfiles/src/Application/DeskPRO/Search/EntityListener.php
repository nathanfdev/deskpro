<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Search
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Search;

use Orb\Util\CapabilityInformerInterface;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Search\Adapter\AbstractAdapter;
use \Symfony\Component\EventDispatcher\EventDispatcher;
use \Application\DeskPRO\DBAL\DoctrineEvent;

/**
 * The entity listener hooks into the postX events in Doctrine and fires off
 * the appropriate indexing handlers when entities we care about have been updated
 */
class EntityListener
{
	/**
	 * @var \Application\DeskPRO\Search\Adapter
	 */
	protected $adapter;

	/**
	 * @var \Symfony\Bundle\FrameworkBundle\ContainerAwareEventDispatcher
	 */
	protected $event_dispatcher;

	/**
	 * Classes we care about
	 */
	protected $indexable_classes = array();

	/**
	 * @param \Application\DeskPRO\Search\Adapter\AbstractAdapter $adapter
	 * @param \Symfony\Component\EventDispatcher\EventDispatcher $event_dispatcher
	 */
	public function __construct(AbstractAdapter $adapter, EventDispatcher $event_dispatcher)
	{
		$this->adapter = $adapter;
		$this->event_dispatcher = $event_dispatcher;

		$this->indexable_classes = array_keys($adapter->getContentTypeMap());
	}

	protected function _handleUpdate(DoctrineEvent $event)
	{
		$entity = $event->getEntity();
		$class = get_class($entity);

		if (!in_array($class, $this->indexable_classes)) {
			return;
		}

		$this->adapter->updateObjectInIndex($entity);
	}

	public function Doctrine_onPostUpdate(DoctrineEvent $event)
	{
		$this->_handleUpdate($event);
	}

	public function Doctrine_onPostPersist(DoctrineEvent $event)
	{
		$this->_handleUpdate($event);
	}

	public function Doctrine_onPostRemove(DoctrineEvent $event)
	{
		$entity = $event->getEntity();
		$class = get_class($entity);

		if (!in_array($class, $this->indexable_classes)) {
			return;
		}

		$this->adapter->deleteObjectFromIndex($entity);
	}
}