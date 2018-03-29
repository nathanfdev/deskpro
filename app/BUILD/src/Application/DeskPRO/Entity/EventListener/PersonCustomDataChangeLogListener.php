<?php

namespace Application\DeskPRO\Entity\EventListener;

use Application\DeskPRO\CustomFields\PersonFieldManager;
use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\CustomDataPerson;
use Application\DeskPRO\Entity\LogEvent;
use Application\DeskPRO\Log\Event\EntityUpdated;
use Application\DeskPRO\ORM\StateChange\ChangeArray;
use Doctrine\ORM\Event\PreUpdateEventArgs;

class PersonCustomDataChangeLogListener extends EntityChangeLogListener
{
    /**
     * @var PersonChangeLogListener
     */
    protected $person_log_listener;

    /**
     * @var PersonFieldManager
     */
    protected $custom_field_manager;

    public function __construct(DeskproContainer $container)
    {
        parent::__construct($container);
        $this->person_log_listener  = $container->get('dp.entity_listener.person_changelog');
        $this->custom_field_manager = $container->getPersonFieldManager();
    }

    /**
     * @param CustomDataPerson   $data
     * @param PreUpdateEventArgs $event
     */
    public function onPreUpdate(CustomDataPerson $data, PreUpdateEventArgs $event)
    {
        if (!$data->person) {
            return;
        }

        $old = clone $data;
        foreach ($event->getEntityChangeSet() as $field => $change) {
            $old[$field] = $change[0];
        }

        $val                                          = $this->custom_field_manager->renderTextForData($data);
        $change                                       = new ChangeArray('custom_data', null, $val);
        $entry                                        = $this->createLogEntry(new EntityUpdated($data->person, $change), $data->person);
        $this->queued_updates[spl_object_hash($data)] = $entry;
    }

    /**
     * @param CustomDataPerson $data
     */
    public function onPostUpdate(CustomDataPerson $data)
    {
        $this->flush($data);
    }

    /**
     * @param CustomDataPerson $data
     */
    public function onPrePersist(CustomDataPerson $data)
    {
        if (!$data->person) {
            return;
        }

        $val                                          = $this->custom_field_manager->renderTextForData($data);
        $change                                       = new ChangeArray('custom_data', null, $val);
        $entry                                        = $this->createLogEntry(new EntityUpdated($data->person, $change), $data->person);
        $this->queued_inserts[spl_object_hash($data)] = $entry;
    }

    /**
     * @param CustomDataPerson $data
     */
    public function onPostPersist(CustomDataPerson $data)
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
