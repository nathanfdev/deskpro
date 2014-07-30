<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category ORM
 */

namespace Application\DeskPRO\ORM\EventListener;

use Application\ApiBundle\Request\RequestAuth;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\DependencyInjection\SystemServices\PersonFieldsManagerService;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\CustomDataPerson;
use Application\DeskPRO\Entity\LogEvent;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonContactData;
use Application\DeskPRO\HttpFoundation\Session;
use Application\DeskPRO\Log\Event\EntityCreated;
use Application\DeskPRO\Log\Event\EntityUpdated;
use Application\DeskPRO\ORM\StateChange\ChangeArray;
use Application\DeskPRO\ORM\StateChange\ChangeCollection;
use Application\DeskPRO\ORM\StateChange\ChangeInterface;
use Application\DeskPRO\ORM\StateChange\ChangeObject;
use Application\DeskPRO\ORM\StateChange\StateChangeRecorder;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Application\DeskPRO\Monolog\Logger as DPLogger;

/**
 * This listener logs entity changes
 */
class EntityChangeTrackingListener implements EventSubscriber
{
	/** @var  DeskproContainer */
	protected $container;

	/** @var \SplQueue */
	protected $queue;

	/** @var array handled state versions */
	protected $handled = array();

	/** @var \Application\DeskPRO\Monolog\Logger  */
	protected $logger;

	// todo better tracking policy
	protected $track = array(
		'Person' => array(

			'first_name' => true,
			'last_name' => true,
			'password' => true,
			'is_disabled' => true,

			'picture_blob' => true,
			'organization' => true,
			'primary_email' => true,

			'emails' => true,
			'labels' => true,
			'notes' => true,
			'usergroups' => true,

			'contact_data' => true,
			'custom_data' => true,
		),
		'PersonContactData' => array(),
		'CustomDataPerson' => array(),
	);

	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
		$this->queue = new \SplQueue();
	}

	public function getSubscribedEvents()
	{
	   return array(
		   Events::preFlush,
		   Events::postFlush,
		   Events::preUpdate,
		   Events::postUpdate,
	   );
	}

	public function preFlush(PreFlushEventArgs $args)
	{
		$uow = $args->getEntityManager()->getUnitOfWork();
		foreach ($uow->getScheduledEntityInsertions() as $entity) {
			$this->prepareEntityChangeset($entity);
		}
		foreach ($uow->getScheduledEntityUpdates() as $entity) {
			$this->prepareEntityChangeset($entity);
		}
	}

	/**
	 * @param LifecycleEventArgs $args
	 */
	public function preUpdate(LifecycleEventArgs $args)
	{
		$this->prepareEntityChangeset($args->getEntity());
	}

	/**
	 * @param LifecycleEventArgs $args
	 */
	public function postUpdate(LifecycleEventArgs $args)
	{
		$this->postFlush();
	}

	/**
	 * fill queue with loggable entries
	 * @param DomainObject $entity
	 */
	protected function prepareEntityChangeset(DomainObject $entity)
	{
		$parts = explode('\\', get_class($entity));
		$entityName = end($parts);

		// todo better tracking policy
		if (!isset($this->track[$entityName])) {
			return;
		}

		/** @var StateChangeRecorder $stateChangeRecorder */
		$stateChangeRecorder = $entity->getStateChangeRecorder();
		if (!$changes = $stateChangeRecorder->getChanges()) {
			return;
		}

		// prevent dupes
		if (isset($this->handled[spl_object_hash($entity)][$stateChangeRecorder->getStateVersion()])) {
			return;
		}
		$this->handled[spl_object_hash($entity)][$stateChangeRecorder->getStateVersion()] = true;


		if ($entity instanceof Person) {
			$parentEntry = null;
			if (!$entity['id']) {
				$event = new EntityCreated($entity);
				$parentEntry = new LogEvent($event, $this->getContextPerson()); // group changes for new Person
				$this->queue->enqueue($parentEntry);
			}

			foreach ($changes as $change) {

				$isTracked = isset($this->track[$entityName][$change->getField()]);
				if ($change->isSame() || ! $isTracked) {
					continue;
				}

				if ('custom_data' === $change->getField() && $change instanceof ChangeCollection) {

					$manager = $this->container->getPersonFieldManager();

					foreach ($change->getAddedElements() as $data) {
						$val = $manager->renderTextForData($data);
						$change = new ChangeArray('custom_data', null, $val);
						$this->enqueueUpdateEvent($entity, $change, $parentEntry);
					}

					continue;
				}

				$this->enqueueUpdateEvent($entity, $change, $parentEntry);
			}
		}

		if ($entity instanceof PersonContactData) {
			$change = new ChangeObject('contact_data', null, $entity);
			return $this->enqueueUpdateEvent($entity->person, $change);
		}

		if ($entity instanceof CustomDataPerson) {
			if (!$entity['id']) return; // ignore as will be handled by Person's custom_data Collection
			$manager = $this->container->getPersonFieldManager();
			$val = $manager->renderTextForData($entity);
			$change = new ChangeArray('custom_data', null, $val);
			$this->enqueueUpdateEvent($entity->person, $change);
		}
	}

	protected function enqueueUpdateEvent(DomainObject $entity, ChangeInterface $change, LogEvent $parentLogEntry = null)
	{
		$event = new EntityUpdated($entity, $change);
		$entry = new LogEvent($event, $this->getContextPerson());

		if ($parentLogEntry) {
			$parentLogEntry->children->add($entry);
			$entry->parent = $parentLogEntry;
		} else {
			$this->queue->enqueue($entry);
		}
	}

	/**
	 * proceed queued log entries after tracked objects were inserted/updated
	 */
	public function postFlush()
	{
		/** @var DPLogger $logger */
		$logger = $this->container->get('deskpro.logger.changelog');

		while (!$this->queue->isEmpty()) {

			/** @var LogEvent $entry */
			$entry = $this->queue->dequeue();
			$logger->info($entry);

			foreach ($entry->children as $child) {
				$logger->info($child);
			}
		}
	}

	/**
	 * todo backend context?
	 */
	protected function getContextPerson()
	{
		$c = $this->container;

		/** @var RequestAuth $auth */
		if ($c->has('deskpro.api.request_auth') && ($auth = $c->get('deskpro.api.request_auth'))) {
			if ($apiUser = $auth->getApiUser()) {
				if ($apiUser->person) {
					return $apiUser->person;
				}
			}
		}

		if ($c->has('session') && ($sess = $c->get('session'))) {
			/** @var $sess Session */
			if ($person = $sess->getPerson()) {
				return $person;
			}
		}

		return null;
	}
}
