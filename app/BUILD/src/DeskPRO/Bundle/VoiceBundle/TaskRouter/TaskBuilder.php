<?php

namespace DeskPRO\Bundle\VoiceBundle\TaskRouter;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\VoiceBundle\Event\TaskRouterEvent;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\ChatWorkflow;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;
use DpSys\LowError\SystemErrorHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param StorageAdapterInterface  $storage
     * @param EventDispatcherInterface $dispatcher
     * @param LoggerInterface          $logger
     */
    public function __construct(StorageAdapterInterface $storage, EventDispatcherInterface $dispatcher, LoggerInterface $logger)
    {
        $this->storage    = $storage;
        $this->dispatcher = $dispatcher;
        $this->logger     = $logger;
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

        try {
            $this->dispatcher->dispatch(TaskRouterEvent::TASK_CREATED, new TaskRouterEvent($task));
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        }

        $this->logger->info(sprintf(
            '[TaskBuilder] New voice task for direct agent call, task_id = %s, call_id = %s, agent_id = %s',
            $task->getId(), $phoneCall->getId(), $agent->getId()
        ));

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

        try {
            $this->dispatcher->dispatch(TaskRouterEvent::TASK_CREATED, new TaskRouterEvent($task));
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        }

        $this->logger->info(sprintf(
            '[TaskBuilder] New voice task for queue call, task_id = %s, call_id = %s, queue_id = %s',
            $task->getId(), $phoneCall->getId(), $queue->getId()
        ));

        return $task;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param Person         $agent
     * @param Person         $fromAgent
     *
     * @return Task
     */
    public function createVoiceTransferToAgentTask(VoicePhoneCall $phoneCall, Person $agent, Person $fromAgent = null)
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

        try {
            $this->dispatcher->dispatch(TaskRouterEvent::TASK_CREATED, new TaskRouterEvent($task));
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        }

        $this->logger->info(sprintf(
            '[TaskBuilder] New voice task for call transfer to agent, task_id = %s, call_id = %s, agent_id = %s',
            $task->getId(), $phoneCall->getId(), $agent->getId()
        ));

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

        try {
            $this->dispatcher->dispatch(TaskRouterEvent::TASK_CREATED, new TaskRouterEvent($task));
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        }

        $this->logger->info(sprintf(
            '[TaskBuilder] New voice task for outgoing call, task_id = %s, call_id = %s, agent_id = %s',
            $task->getId(), $phoneCall->getId(), $agent->getId()
        ));

        return $task;
    }

    /**
     * @param VoicePhoneCall $phoneCall
     * @param VoiceQueue     $queue
     *
     * @return Task
     */
    public function createVoiceTransferToQueueTask(VoicePhoneCall $phoneCall, VoiceQueue $queue)
    {
        $task = new Task();
        $task->setChannel(VoiceWorkflow::getChannelName());
        $task->setAttributes([
            'queue'      => $queue->getId(),
            'phone_call' => $phoneCall->getId(),
            'person'     => $phoneCall->getPerson()->getId(),
        ]);

        // ignore the call for existing participants
        // so they won't get this call again from the queue
        // if they are a part of this queue
        foreach ($phoneCall->getAgentParticipants() as $participant) {
            $worker = $this->storage->getWorkerByType('agent', $participant->getPerson()->getId());
            if ($worker) {
                $task->addRejectedBy($worker);
            }
        }

        $this->storage->saveTask($task);

        try {
            $this->dispatcher->dispatch(TaskRouterEvent::TASK_CREATED, new TaskRouterEvent($task));
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        }

        $this->logger->info(sprintf(
            '[TaskBuilder] New voice task for call transfer to queue, task_id = %s, call_id = %s, queue_id = %s',
            $task->getId(), $phoneCall->getId(), $queue->getId()
        ));

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

        try {
            $this->dispatcher->dispatch(TaskRouterEvent::TASK_CREATED, new TaskRouterEvent($task));
        } catch (\Exception $e) {
            SystemErrorHandler::logException($e);
        }

        $this->logger->info(sprintf(
            '[TaskBuilder] New chat task for queue, task_id = %s, chat_id = %s, department_id = %s',
            $task->getId(), $chat->getId(), $chat->getDepartmentId()
        ));

        return $task;
    }
}
