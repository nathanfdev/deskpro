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
use Application\DeskPRO\Entity\LogEntity;
use Application\DeskPRO\HttpFoundation\Session;
use Application\DeskPRO\ORM\StateChange\ChangeInterface;
use Application\DeskPRO\ORM\StateChange\StateChangeRecorder;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Application\DeskPRO\Monolog\Logger;

/**
 * This listener logs entity changes
 */
class EntityChangeTrackingListener implements EventSubscriber
{
	/** @var  DeskproContainer */
	protected $container;

	protected $queuedChanges = array();

	protected $logger;

	protected $track = array(
		'Application\DeskPRO\Entity\Person' => true,
	);

	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
		// todo add db writer
		$this->logger = new Logger('changelog');
	}

	public function getSubscribedEvents()
	{
	   return array(
		   Events::prePersist,
		   Events::preUpdate,
		   Events::postPersist,
		   Events::postUpdate,
	   );
	}

	/**
	 * proxy
	 * @param LifecycleEventArgs $args
	 */
	public function prePersist(LifecycleEventArgs $args)
	{
//		$this->preUpdate($args);
	}

	/**
	 * store changes into queue to log them after successful flush
	 * @param LifecycleEventArgs $args
	 */
	public function preUpdate(LifecycleEventArgs $args)
	{
		if (!isset($this->track[get_class($args->getEntity())])) {
			return;
		}
//		$uow = $args->getEntityManager()->getUnitOfWork();
//		$changes = $uow->getEntityChangeSet($args->getEntity());

		/** @var StateChangeRecorder $stateChangeRecorder */
		$stateChangeRecorder = $args->getEntity()->getStateChangeRecorder();
		$changes = $stateChangeRecorder->getChanges();


		$oid = spl_object_hash($args->getEntity());
		$this->queuedChanges[$oid] = ! isset($this->queuedChanges[$oid])
			? $changes
			: array_merge($this->queuedChanges[$oid], $changes);
	}

	/**
	 * proxy
	 * @param LifecycleEventArgs $args
	 */
	public function postPersist(LifecycleEventArgs $args)
	{
		$this->postUpdate($args);
	}

	/**
	 * log changes stored before persist/update
	 * @param LifecycleEventArgs $args
	 */
	public function postUpdate(LifecycleEventArgs $args)
	{
		$oid = spl_object_hash($args->getEntity());
		if (!isset($this->queuedChanges[$oid])) {
			return;
		}

		foreach ($this->queuedChanges[$oid] as $change) {
			/** @var $change ChangeInterface */
			if ($change->isSame()) continue;

			$entry = new LogEntity($args->getEntity(), $change, $this->getContextPerson());
			// todo merge db logger from round robin branch
			$this->logger->info($entry);
		}

		unset($this->queuedChanges[$oid]);
	}

	/**
	 * todo backend context?
	 */
	protected function getContextPerson()
	{
		$c = $this->container;

		if ($c->has('session') && ($sess = $c->get('session'))) {
			/** @var $sess Session */
			if ($person = $sess->getPerson()) {
				return $person;
			}
		}

		/** @var RequestAuth $auth */
		if ($c->has('deskpro.api.request_auth') && ($auth = $c->get('deskpro.api.request_auth'))) {
			if ($apiUser = $auth->getApiUser()) {
				if ($apiUser->person) {
					return $apiUser->person;
				}
			}
		}

		return null;
	}
}
