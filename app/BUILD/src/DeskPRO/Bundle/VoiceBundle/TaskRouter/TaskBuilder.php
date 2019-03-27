<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\ChatWorkflow;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;

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
        $task->setChannel(VoiceWorkflow::getChannelName());
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
        $task->setChannel(VoiceWorkflow::getChannelName());
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

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     * @param Person         $fromAgent
     *
     * @return Task
     */
    public function createVoiceTransferTask(VoicePhoneCall $phoneCall, Person $agent, Person $fromAgent = null)
    {
        $attributes = [
            'agent'       => $agent->getId(),
            'phone_call'  => $phoneCall->getId(),
            'person'      => $phoneCall->getPerson()->getId(),
            'transfer'    => true,
            'invite_type' => 'cold',
        ];
        if ($fromAgent) {
            $attributes['from_agent_id'] = $fromAgent->getId();
        }

        $task = new Task();
        $task->setChannel(VoiceWorkflow::getChannelName());
        $task->setAttributes($attributes);

        $this->storage->saveTask($task);

        return $task;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     *
     * @return Task
     */
    public function createVoiceTaskForOutgoingCall(VoicePhoneCall $phoneCall, Person $agent)
    {
        // create already accepted so task router will ignore it
        // don't need to process it
        $task = new Task();
        $task->setChannel(VoiceWorkflow::getChannelName());
        $task->setStatus(Task::STATUS_ACCEPTED);
        $task->setAttributes([
            'agent'      => $agent->getId(),
            'phone_call' => $phoneCall->getId(),
        ]);

        $this->storage->saveTask($task);

        return $task;
    }

    /**
     * @param ChatConversation $chat
     *
     * @return Task
     */
    public function createChatTaskForQueue(ChatConversation $chat)
    {
        $task = new Task();
        $task->setChannel(ChatWorkflow::getChannelName());
        $task->setAttributes([
            'chat' => $chat->getId(),
        ]);

        $this->storage->saveTask($task);

        return $task;
    }
}
