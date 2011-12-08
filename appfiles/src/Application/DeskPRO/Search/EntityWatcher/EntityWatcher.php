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

namespace Application\DeskPRO\Search\EntityWatcher;

use Application\DeskPRO\App;
use Application\DeskPRO\Search\Adapter\AbstractAdapter;
use Application\DeskPRO\DBAL\DoctrineEvent;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Orb\Util\Util;
use Orb\Filter\FilterInterface;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

class EntityWatcher implements \Doctrine\Common\EventSubscriber
{
	public static $watched_entities = array(
		'Application\\DeskPRO\\Entity\\Article',
		'Application\\DeskPRO\\Entity\\Download',
		'Application\\DeskPRO\\Entity\\Idea',
		'Application\\DeskPRO\\Entity\\News',
		'Application\\DeskPRO\\Entity\\Ticket',
		'Application\\DeskPRO\\Entity\\TicketMessage',
	);

	/**
	 * @var \Orb\Filter\FilterInterface[]
	 */
	protected $entity_filters = array();

	/**
	 * @var \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	protected $container;

	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
	}

	protected function _lazyInit()
	{
		static $has_init = false;
		if ($has_init === true) return;
		$has_init = true;

		$ticket_filter = new MysqlFilter\TicketFilter($this->container->getEm());
		$this->addEntityTypeFilter('Application\\DeskPRO\\Entity\\Ticket', $ticket_filter);
	}

	/**
	 * Add an entity filter.
	 *
	 * Filters take entities that we've detected changes on, and is meant to
	 * take a look at the changes to see if we actually need to update the index.
	 * For example, if a ticket status is just changed, we dont need to update the fulltext index
	 *
	 * @param $entity
	 * @param \Orb\Filter\FilterInterface $filter
	 */
	public function addEntityTypeFilter($entity_type, FilterInterface $filter)
	{
		$this->entity_filters[$entity_type] = $filter;
	}


	/**
	 * Filter an entity to see if it sholud be updated
	 *
	 * @param $entity
	 * @return bool
	 */
	public function filterEntity($entity)
	{
		$entity_type = get_class($entity);
		if (isset($this->entity_filters[$entity_type])) {
			return $this->entity_filters[$entity_type]->filter($entity);
		}

		// Default to true
		return true;
	}


	public function onFlush(OnFlushEventArgs $eventArgs)
	{
		$this->_lazyInit();

		$update = array();
		$delete = array();

		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		foreach ($uow->getScheduledEntityInsertions() as $ent) {
			if (self::isWatchedEntity($ent) && $this->filterEntity($ent)) {
				$update[] = $ent;
			}
		}
		foreach ($uow->getScheduledEntityUpdates() as $ent) {
			if (self::isWatchedEntity($ent) && $this->filterEntity($ent)) {
				$update[] = $ent;
			}
		}
		foreach ($uow->getScheduledEntityDeletions() as $ent) {
			if (self::isWatchedEntity($ent)) {
				$delete[] = $ent;
			}
		}

		if ($update || $delete) {
			$queue = $this->container->getQueue('search_object_update');
			foreach ($update as $ent) {
				$queue->send(array('entity' => get_class($ent), 'id' => $ent->id, 'op' => 'update'));
			}
			foreach ($delete as $ent) {
				$queue->send(array('entity' => get_class($ent), 'id' => $ent->id, 'op' => 'delete'));
			}
		}
	}


	public function getSubscribedEvents()
	{
		return array(
			\Doctrine\ORM\Events::onFlush
		);
	}


	/**
	 * Check if an entity is watched
	 *
	 * @param $entity
	 * @return bool
	 */
	public static function isWatchedEntity($entity)
	{
		if (is_string($entity)) {
			$name = $entity;
		} else {
			$name = get_class($entity);
		}
		return isset(self::$watched_entities[$name]);
	}
}
