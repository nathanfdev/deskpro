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

use Application\DeskPRO\Entity\LogEvent;
use Application\DeskPRO\Entity\PersonContactData;
use Application\DeskPRO\Log\Event\EntityUpdated;
use Application\DeskPRO\ORM\StateChange\ChangeObject;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class PersonContactDataChangeLogListener extends EntityChangeLogListener
{
	protected $parent_log_entry;

	/**
	 * @param PersonContactData $data
	 * @param PreUpdateEventArgs $event
	 */
	public function onPreUpdate(PersonContactData $data, PreUpdateEventArgs $event)
	{
		$old = clone $data;
		foreach ($event->getEntityChangeSet() as $field => $change) {
			$old[$field] = $change[0];
		}

		$change = new ChangeObject('contact_data', $old, $data);
		$entry = new LogEvent(new EntityUpdated($data->person, $change), $this->getContextPerson() ?: $data->person);
		$this->queued_updates[spl_object_hash($data)] = $entry;
	}

	/**
	 * @param PersonContactData $data
	 * @param LifecycleEventArgs $event
	 */
	public function onPostUpdate(PersonContactData $data, LifecycleEventArgs $event)
	{
		$this->flush($data);
	}

	/**
	 * @param PersonContactData $data
	 * @param LifecycleEventArgs $event
	 */
	public function onPrePersist(PersonContactData $data, LifecycleEventArgs $event)
	{
		$change = new ChangeObject('contact_data', null, $data);
		$entry = new LogEvent(new EntityUpdated($data->person, $change), $this->getContextPerson() ?: $data->person);
		$this->queued_inserts[spl_object_hash($data)] = $entry;
	}

	/**
	 * @param PersonContactData $data
	 * @param LifecycleEventArgs $event
	 */
	public function onPostPersist(PersonContactData $data, LifecycleEventArgs $event)
	{
		$this->flush($data);
	}

	/**
	 * @param PersonContactData $data
	 * @param LifecycleEventArgs $event
	 */
	public function onPreRemove(PersonContactData $data, LifecycleEventArgs $event)
	{
		$change = new ChangeObject('contact_data', $data, null);
		$entry = new LogEvent(new EntityUpdated($data->person, $change), $this->getContextPerson() ?: $data->person);
		$this->queued_deletions[spl_object_hash($data)] = $entry;
	}

	/**
	 * @param PersonContactData $data
	 * @param LifecycleEventArgs $event
	 */
	public function onPostRemove(PersonContactData $data, LifecycleEventArgs $event)
	{
		$this->flush($data);
	}

	/**
	 * @param $oid
	 * @param $type
	 */
	protected function doFlush($oid, $type)
	{
		if (!isset($this->{'queued_' . $type}[$oid])) {
			return;
		}

		/** @var LogEvent $entry */
		$entry = $this->{'queued_' . $type}[$oid];

		if (!$this->parent_log_entry) {
			$contextPerson = $entry->person;
			$person = $entry->_event->getSubject();
			$this->parent_log_entry = new LogEvent(new EntityUpdated($person), $contextPerson);
		}

		$this->parent_log_entry->children->add($entry);
		$entry->parent = $this->parent_log_entry;

		unset($this->{'queued_' . $type}[$oid]);

		/**
		 * we do only one single flush, and only when all queued actions added as child to parent_log_entry
		 */
		if (!count($this->queued_inserts) && !count($this->queued_updates) && !count($this->queued_deletions)) {

			$this->logger->info($this->parent_log_entry);

			foreach ($this->parent_log_entry->children as $child) {
				$this->logger->info($child);
			}
			$this->parent_log_entry = null;
		}
	}
} 