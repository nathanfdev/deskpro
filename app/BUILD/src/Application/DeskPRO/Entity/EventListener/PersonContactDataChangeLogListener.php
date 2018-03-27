<?php

namespace Application\DeskPRO\Entity\EventListener;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\LogEvent;
use Application\DeskPRO\Entity\PersonContactData;
use Application\DeskPRO\Log\Event\EntityUpdated;
use Application\DeskPRO\ORM\StateChange\ChangeObject;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class PersonContactDataChangeLogListener extends EntityChangeLogListener
{
    /**
     * @var PersonChangeLogListener
     */
    protected $person_log_listener;

    public function __construct(DeskproContainer $container)
    {
        parent::__construct($container);
        $this->person_log_listener = $container->get('dp.entity_listener.person_changelog');
    }

    /**
     * @param PersonContactData  $data
     * @param PreUpdateEventArgs $event
     */
    public function onPreUpdate(PersonContactData $data, PreUpdateEventArgs $event)
    {
        $old = clone $data;
        foreach ($event->getEntityChangeSet() as $field => $change) {
            $old[$field] = $change[0];
        }

        $change                                       = new ChangeObject('contact_data', $old, $data);
        $entry                                        = $this->createLogEntry(new EntityUpdated($data->person, $change), $data->person);
        $this->queued_updates[spl_object_hash($data)] = $entry;
    }

    /**
     * @param PersonContactData $data
     */
    public function onPostUpdate(PersonContactData $data)
    {
        $this->flush($data);
    }

    /**
     * @param PersonContactData $data
     */
    public function onPrePersist(PersonContactData $data)
    {
        $change                                       = new ChangeObject('contact_data', null, $data);
        $entry                                        = $this->createLogEntry(new EntityUpdated($data->person, $change), $data->person);
        $this->queued_inserts[spl_object_hash($data)] = $entry;
    }

    /**
     * @param PersonContactData $data
     */
    public function onPostPersist(PersonContactData $data)
    {
        $this->flush($data);
    }

    /**
     * @param PersonContactData $data
     */
    public function onPreRemove(PersonContactData $data)
    {
        $change                                         = new ChangeObject('contact_data', $data, null);
        $entry                                          = $this->createLogEntry(new EntityUpdated($data->person, $change), $data->person);
        $this->queued_deletions[spl_object_hash($data)] = $entry;
    }

    /**
     * @param PersonContactData $data
     */
    public function onPostRemove(PersonContactData $data)
    {
        $this->flush($data);
    }

    /**
     * @param $oid
     * @param $type
     */
    protected function doFlush($oid, $type)
    {
        if (!isset($this->{'queued_'.$type}[$oid])) {
            return;
        }

        /** @var LogEvent $entry */
        $entry       = $this->{'queued_'.$type}[$oid];
        $person      = $entry->getEventObject()->getSubject();
        $parentEntry = $this->person_log_listener->getUpdateLogEntry($person);

        $parentEntry->children->add($entry);
        $entry->parent = $parentEntry;

        unset($this->{'queued_'.$type}[$oid]);

        /*
         * we do only one single flush, and only when all queued actions added as child to $parentEntry
         */
        if (!count($this->queued_inserts) && !count($this->queued_updates) && !count($this->queued_deletions)) {
            $this->person_log_listener->onPostUpdate($person);
        }
    }
}
