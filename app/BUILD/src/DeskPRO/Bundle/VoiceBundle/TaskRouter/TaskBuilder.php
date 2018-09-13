<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;

/**
 * Class TaskBuilder.
 */
class TaskBuilder
{
    /**
     * @var StorageAdapterInterface
     */
    private $storage;

    /**
     * Constructor.
     *
     * @param StorageAdapterInterface $storage
     */
    public function __construct(StorageAdapterInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     * @param Person         $person
     * @param Person[]       $relatedPeople
     *
     * @return Task
     */
    public function createVoiceTaskForAgent(VoicePhoneCall $phoneCall, Person $agent, Person $person, array $relatedPeople = [])
    {
        $task = new Task();
        $task->setChannel('voice');
        $task->setAttributes([
            'agent'          => $agent->getId(),
            'phone_call'     => $phoneCall->getId(),
            'person'         => $person->getId(),
            'related_people' => array_map(function (Person $person) {
                return $person->getId();
            }, $relatedPeople),
        ]);

        $this->storage->saveTask($task);

        return $task;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param VoiceQueue     $queue
     * @param Person         $person
     * @param Person[]       $relatedPeople
     *
     * @return Task
     */
    public function createVoiceTaskForQueue(VoicePhoneCall $phoneCall, VoiceQueue $queue, Person $person, array $relatedPeople = [])
    {
        $task = new Task();
        $task->setChannel('voice');
        $task->setAttributes([
            'queue'          => $queue->getId(),
            'phone_call'     => $phoneCall->getId(),
            'person'         => $person->getId(),
            'related_people' => array_map(function (Person $person) {
                return $person->getId();
            }, $relatedPeople),
        ]);

        $this->storage->saveTask($task);

        return $task;
    }
}
