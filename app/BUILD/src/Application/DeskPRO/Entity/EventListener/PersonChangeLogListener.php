<?php

namespace Application\DeskPRO\Entity\EventListener;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Log\Event\EntityCreated;
use Application\DeskPRO\Log\Event\EntityUpdated;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class PersonChangeLogListener extends EntityChangeLogListener
{
    protected $fields = [
        'first_name'   => true,
        'last_name'    => true,
        'password'     => true,
        'is_disabled'  => true,
        'title_prefix' => true,

        'picture_blob'  => true,
        'organization'  => true,
        'primary_email' => true,

        'emails'     => true,
        'labels'     => true,
        'notes'      => true,
        'usergroups' => true,
    ];

    /**
     * @param Person             $person
     * @param PreUpdateEventArgs $event
     */
    public function onPreUpdate(Person $person, PreUpdateEventArgs $event)
    {
        if (isset($GLOBALS['DP_IS_IMPORTING'])) {
            return;
        }

        if (!$changes = $this->getChangesForEntity($person)) {
            return;
        }

        $entry = $this->getUpdateLogEntry($person);

        foreach ($changes as $change) {
            $child = $this->createLogEntry(new EntityUpdated($person, $change), $person);
            $entry->children->add($child);
            $child->parent = $entry;
        }
    }

    /**
     * @param Person $person
     */
    public function onPostUpdate(Person $person)
    {
        if (isset($GLOBALS['DP_IS_IMPORTING'])) {
            return;
        }
        $this->flush($person);
    }

    /**
     * @param Person $person
     */
    public function onPrePersist(Person $person)
    {
        if (isset($GLOBALS['DP_IS_IMPORTING'])) {
            return;
        }

        // do not handle persisted entity
        if ($person['id']) {
            return;
        }

        if (!$changes = $this->getChangesForEntity($person)) {
            return;
        }

        $entry = $this->createLogEntry(new EntityCreated($person), $person);
        foreach ($changes as $change) {
            $child = $this->createLogEntry(new EntityUpdated($person, $change), $person);
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
        if (isset($GLOBALS['DP_IS_IMPORTING'])) {
            return;
        }
        $this->flush($person);
    }

    /**
     * @param Person $person
     */
    public function getUpdateLogEntry(Person $person)
    {
        $oid = spl_object_hash($person);
        if (!isset($this->queued_updates[$oid])) {
            $this->queued_updates[$oid] = $this->createLogEntry(new EntityUpdated($person), $person);
        }

        return $this->queued_updates[$oid];
    }
}
