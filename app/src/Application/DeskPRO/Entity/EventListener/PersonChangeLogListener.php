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
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Log\Event\EntityCreated;
use Application\DeskPRO\Log\Event\EntityUpdated;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class PersonChangeLogListener extends EntityChangeLogListener
{
	protected $fields = array(
		'first_name' => true,
		'last_name' => true,
		'password' => true,
		'is_disabled' => true,
		'title_prefix' => true,

		'picture_blob' => true,
		'organization' => true,
		'primary_email' => true,

		'emails' => true,
		'labels' => true,
		'notes' => true,
		'usergroups' => true,
	);

	/**
	 * @param Person $person
	 * @param PreUpdateEventArgs $event
	 */
	public function onPreUpdate(Person $person, PreUpdateEventArgs $event)
	{
		if (!$changes = $this->getChangesForEntity($person)) {
			return;
		}

		$entry = $this->getUpdateLogEntry($person);

		foreach ($changes as $change) {
			$child = new LogEvent(new EntityUpdated($person, $change), $this->getContextPerson() ?: $person);
			$entry->children->add($child);
			$child->parent = $entry;
		}
	}

	/**
	 * @param Person $person
	 */
	public function onPostUpdate(Person $person)
	{
		$this->flush($person);
	}

	/**
	 * @param Person $person
	 */
	public function onPrePersist(Person $person)
	{
		// do not handle persisted entity
		if ($person['id']) {
			return;
		}

		if (!$changes = $this->getChangesForEntity($person)) {
			return;
		}

		$entry = new LogEvent(new EntityCreated($person), $this->getContextPerson() ?: $person);
		foreach ($changes as $change) {
			$child = new LogEvent(new EntityUpdated($person, $change), $this->getContextPerson() ?: $person);
			$entry->children->add($child);
			$child->parent = $entry;
		}

		$this->queued_inserts[spl_object_hash($person)] = $entry;
	}

	/**
	 * @param Person $person
	 */
	public function onPostPersist(Person $person)
	{
		$this->flush($person);
	}

	/**
	 * @param Person $person
	 * @return null
	 */
	public function getUpdateLogEntry(Person $person)
	{
		$oid = spl_object_hash($person);
		if (!isset($this->queued_updates[$oid])) {
			$this->queued_updates[$oid] = new LogEvent(new EntityUpdated($person), $this->getContextPerson() ?: $person);
		}
		return $this->queued_updates[$oid];
	}
} 