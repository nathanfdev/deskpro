<?php

namespace spec\DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\Helper\VoiceTaskHelper;
use DeskPRO\Bundle\VoiceBundle\Helper\WorkerHelper;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\TaskQueue;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use PhpSpec\ObjectBehavior;

/**
 * Class VoiceWorkflowTest.
 *
 * @mixin \DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow
 */
class VoiceWorkflowSpec extends ObjectBehavior
{
    public function let(
        WorkerHelper            $workerHelper,
        VoiceTaskHelper         $taskHelper,
        VoiceSettingsResolver   $settingsResolver,
        StorageAdapterInterface $storage
    ) {
        $this->beConstructedWith($workerHelper, $taskHelper, $settingsResolver, $storage);
    }

    public function it_accepts_empty_task(
        Task $task,
        WorkerHelper $workerHelper,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $storage->getOnlineWorkersByType('agent')->willReturn([]);
        $workerHelper->getForwardingCallWorkers()->willReturn([]);

        $taskHelper->getVoiceQueue($task)->willReturn(null);
        $taskHelper->getWorkerAgent($task)->willReturn(null);

        $task->getRejectedBy()->willReturn([]);

        $this->assignTask($task);
    }

    public function it_ignores_rejected_workers_when_assigning_an_agent_directly(
        Person $agent,
        Task $task,
        WorkerHelper $workerHelper,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1 = new Worker();
        $worker1->setId(10);
        $worker1->setTypeId(1);

        $worker2 = new Worker();
        $worker2->setId(20);
        $worker2->setTypeId(2);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1]);
        $workerHelper->getForwardingCallWorkers()->willReturn([$worker2]);

        $agent->getId()->willReturn(1);

        $taskHelper->getVoiceQueue($task)->willReturn(null);
        $taskHelper->getWorkerAgent($task)->willReturn($agent);

        $task->getRejectedBy()->willReturn([10]);
        $task->setWorkersIds([10])->shouldNotBeCalled();

        $this->assignTask($task);
    }

    public function it_assigns_an_agent_directly(
        Person $agent,
        Task $task,
        WorkerHelper $workerHelper,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1 = new Worker();
        $worker1->setId(10);
        $worker1->setTypeId(1);

        $worker2 = new Worker();
        $worker2->setId(20);
        $worker2->setTypeId(2);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1]);
        $workerHelper->getForwardingCallWorkers()->willReturn([$worker2]);

        $agent->getId()->willReturn(1);

        $taskHelper->getVoiceQueue($task)->willReturn(null);
        $taskHelper->getWorkerAgent($task)->willReturn($agent);

        $task->getRejectedBy()->willReturn([]);
        $task->setWorkersIds([10])->shouldBeCalled();

        $this->assignTask($task);
    }

    public function it_doesnt_assign_an_offline_agent_directly(
        Person $agent,
        Task $task,
        WorkerHelper $workerHelper,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $storage->getOnlineWorkersByType('agent')->willReturn([]);
        $workerHelper->getForwardingCallWorkers()->willReturn([]);

        $agent->getId()->willReturn(1);

        $taskHelper->getVoiceQueue($task)->willReturn(null);
        $taskHelper->getWorkerAgent($task)->willReturn($agent);

        $task->getRejectedBy()->willReturn([]);

        $this->assignTask($task);
    }

    public function it_handles_round_robin_queue(
        VoiceQueue $queue,
        Task $task,
        TaskQueue $taskQueue,
        WorkerHelper $workerHelper,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1 = new Worker();
        $worker1->setId(10);
        $worker1->setTypeId(1);

        $worker3 = new Worker();
        $worker3->setId(30);
        $worker3->setTypeId(3);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(VoiceQueue::ROUTING_MODEL_AUTOMATIC);
        $queue->getActiveAgentsPeopleIds()->willReturn([1, 2, 3]);

        $taskHelper->getVoiceQueue($task)->willReturn($queue);
        $taskHelper->getWorkerAgent($task)->willReturn(null);

        $taskQueue->getAttribute('round_robin_order')->willReturn([1, 2, 3]);
        $taskQueue->setAttribute('round_robin_order', [2, 3, 1])->shouldBeCalled();

        $storage->getTaskQueue('voice', 1)->willReturn($taskQueue);
        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1, $worker3]);
        $workerHelper->getForwardingCallWorkers()->willReturn([]);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->getRejectedBy()->willReturn([]);
        $task->setWorkersIds([10])->shouldBeCalled();

        $this->assignTask($task);
    }

    public function it_keeps_round_robin_up_to_date(
        VoiceQueue $queue,
        Task $task,
        TaskQueue $taskQueue,
        WorkerHelper $workerHelper,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1 = new Worker();
        $worker1->setId(10);
        $worker1->setTypeId(1);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(VoiceQueue::ROUTING_MODEL_AUTOMATIC);
        $queue->getActiveAgentsPeopleIds()->willReturn([1, 3, 5]);

        $taskHelper->getVoiceQueue($task)->willReturn($queue);
        $taskHelper->getWorkerAgent($task)->willReturn(null);

        $taskQueue->getAttribute('round_robin_order')->willReturn([1, 2, 3]);
        $taskQueue->setAttribute('round_robin_order', [3, 5, 1])->shouldBeCalled();

        $storage->getTaskQueue('voice', 1)->willReturn($taskQueue);
        $storage->getOnlineWorkersByType('agent')->willReturn([]);
        $workerHelper->getForwardingCallWorkers()->willReturn([$worker1]);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->getRejectedBy()->willReturn([]);
        $task->setWorkersIds([10])->shouldBeCalled();

        $this->assignTask($task);
    }

    public function it_keeps_round_robin_order_same_if_no_workers(
        VoiceQueue $queue,
        Task $task,
        TaskQueue $taskQueue,
        WorkerHelper $workerHelper,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(VoiceQueue::ROUTING_MODEL_AUTOMATIC);
        $queue->getActiveAgentsPeopleIds()->willReturn([1, 3, 5]);

        $taskHelper->getVoiceQueue($task)->willReturn($queue);
        $taskHelper->getWorkerAgent($task)->willReturn(null);

        $taskQueue->getAttribute('round_robin_order')->willReturn([1, 2, 3]);
        $taskQueue->setAttribute('round_robin_order', [1, 3, 5])->shouldBeCalled();

        $storage->getTaskQueue('voice', 1)->willReturn($taskQueue);
        $storage->getOnlineWorkersByType('agent')->willReturn([]);
        $workerHelper->getForwardingCallWorkers()->willReturn([]);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->getRejectedBy()->willReturn([]);

        $this->assignTask($task);
    }

    public function it_handles_simulring_queue(
        VoiceQueue $queue,
        Task $task,
        TaskQueue $taskQueue,
        WorkerHelper $workerHelper,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1 = new Worker();
        $worker1->setId(10);
        $worker1->setTypeId(1);

        $worker3 = new Worker();
        $worker3->setId(30);
        $worker3->setTypeId(3);

        $worker5 = new Worker();
        $worker5->setId(50);
        $worker5->setTypeId(5);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(VoiceQueue::ROUTING_MODEL_SIMULRING);
        $queue->getActiveAgentsPeopleIds()->willReturn([1, 2, 4, 5]);

        $taskHelper->getVoiceQueue($task)->willReturn($queue);
        $taskHelper->getWorkerAgent($task)->willReturn(null);

        $storage->getTaskQueue('voice', 1)->willReturn($taskQueue);
        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1, $worker5]);
        $workerHelper->getForwardingCallWorkers()->willReturn([$worker1]);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->getRejectedBy()->willReturn([]);
        $task->setWorkersIds([10, 50])->shouldBeCalled();

        $this->assignTask($task);
    }

    public function it_handles_least_utilized_queue(
        VoiceQueue $queue,
        Task $task,
        TaskQueue $taskQueue,
        WorkerHelper $workerHelper,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1 = new Worker();
        $worker1->setId(10);
        $worker1->setTypeId(1);

        $worker2 = new Worker();
        $worker2->setId(20);
        $worker2->setTypeId(2);

        $worker3 = new Worker();
        $worker3->setId(30);
        $worker3->setTypeId(3);

        $worker4 = new Worker();
        $worker4->setId(40);
        $worker4->setTypeId(4);

        $queue->getId()->willReturn(1);
        $queue->getMaxQueueSize()->willReturn(2);
        $queue->getRoutingModel()->willReturn(VoiceQueue::ROUTING_MODEL_LEAST_UTILIZED);
        $queue->getActiveAgentsPeopleIds()->willReturn([1, 2, 3]);

        $taskHelper->getVoiceQueue($task)->willReturn($queue);
        $taskHelper->getWorkerAgent($task)->willReturn(null);

        $taskQueue->getAttribute('answered_calls_counts')->willReturn([
            1 => 5,
            2 => 10,
            3 => 1,
        ]);

        $storage->getTaskQueue('voice', 1)->willReturn($taskQueue);
        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1, $worker4]);
        $workerHelper->getForwardingCallWorkers()->willReturn([$worker2, $worker3]);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->getRejectedBy()->willReturn([]);
        $task->setWorkersIds([30, 10])->shouldBeCalled();

        $this->assignTask($task);
    }

    public function it_keeps_least_utilized_up_to_date(
        VoiceQueue $queue,
        Task $task,
        TaskQueue $taskQueue,
        WorkerHelper $workerHelper,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker3 = new Worker();
        $worker3->setId(30);
        $worker3->setTypeId(3);

        $worker4 = new Worker();
        $worker4->setId(40);
        $worker4->setTypeId(4);

        $queue->getId()->willReturn(1);
        $queue->getMaxQueueSize()->willReturn(2);
        $queue->getRoutingModel()->willReturn(VoiceQueue::ROUTING_MODEL_LEAST_UTILIZED);
        $queue->getActiveAgentsPeopleIds()->willReturn([1, 3, 4]);

        $taskHelper->getVoiceQueue($task)->willReturn($queue);
        $taskHelper->getWorkerAgent($task)->willReturn(null);

        $taskQueue->getAttribute('answered_calls_counts')->willReturn([
            1 => 5,
            2 => 10,
            3 => 1,
        ]);

        $storage->getTaskQueue('voice', 1)->willReturn($taskQueue);
        $storage->getOnlineWorkersByType('agent')->willReturn([$worker3, $worker4]);
        $workerHelper->getForwardingCallWorkers()->willReturn([]);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->getRejectedBy()->willReturn([]);
        $task->setWorkersIds([40, 30])->shouldBeCalled();

        $this->assignTask($task);
    }
}
