<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\DeskPRO\Entity\EventListener;


use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Domain\DomainObject;

abstract class EntityChangeLogListener
{
	/** @var  DeskproContainer */
	protected $container;

	/** @var array */
	protected $queued_inserts = array();

	/** @var array */
	protected $queued_updates = array();

	/** @var \Application\DeskPRO\Monolog\Logger  */
	protected $logger;

	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
		$this->logger = $container->get('deskpro.logger.changelog');
	}

	/**
	 * todo backend context?
	 */
	protected function getContextPerson()
	{
		$person = $this->tryToGetPersonFromContext();

		// don't even return a PersonGuest
		if (!$person || $person instanceof PersonGuest) {
			return null;
		}

		return $person;
	}

	/**
	 *
	 */
	protected function tryToGetPersonFromContext()
	{
		$c = $this->container;
		/** @var RequestAuth $auth */
		try {
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
		} catch (InactiveScopeException $e) {
		}
	}

	/**
	 * @param DomainObject $entity
	 * @return array
	 */
	protected function getChangesForEntity(DomainObject $entity)
	{
		/** @var StateChangeRecorder $stateChangeRecorder */
		$stateChangeRecorder = $entity->getStateChangeRecorder();
		if (!$changes = $stateChangeRecorder->getChanges()) {
			return;
		}

		$ret = array();
		foreach ($changes as $change) {
			if ($change->isSame() || ! isset($this->fields[$change->getField()])) {
				continue;
			}

			$ret[$change->getField()] = $change;
		}
		return $ret;
	}

	/**
	 * @param DomainObject $entity
	 */
	protected function flush(DomainObject $entity)
	{
		$oid = spl_object_hash($entity);

		foreach (array('inserts', 'updates') as $type) {
			if (!isset($this->{'queued_' . $type}[$oid])) {
				continue;
			}

			$entry = $this->{'queued_' . $type}[$oid];
			$this->logger->info($entry);
			foreach ($entry->children as $child) {
				$this->logger->info($child);
			}

			unset($this->{'queued_' . $type}[$oid]);
		}
	}
} 