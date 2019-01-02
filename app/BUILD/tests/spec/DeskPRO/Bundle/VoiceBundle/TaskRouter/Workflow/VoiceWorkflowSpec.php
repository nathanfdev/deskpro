<?php

namespace spec\DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\VoiceBundle\Helper\VoiceTaskHelper;
use DeskPRO\Bundle\VoiceBundle\Helper\WorkerHelper;
use DeskPRO\Bundle\VoiceBundle\Settings\VoiceSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\TaskQueue;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\ChatWorkflow;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;
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

    public function it_returns_empty_list_of_workers(Task $task, WorkerHelper $workerHelper, StorageAdapterInterface $storage)
    {
        $storage->getOnlineWorkersByType('agent')->willReturn([]);
        $workerHelper->getVoiceAgentIds()->willReturn([]);
        $workerHelper->getForwardingCallWorkers()->willReturn([]);

        $this->getAvailableWorkers($task)->shouldReturn([]);
    }

    public function it_returns_a_list_of_workers(
        Worker $worker1,
        Worker $worker2,
        Task $task,
        WorkerHelper $workerHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);

        $worker2->getId()->willReturn(20);
        $worker2->getTypeId()->willReturn(2);
        $worker2->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker2->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker2->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker2->hasActiveTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1]);
        $workerHelper->getForwardingCallWorkers()->willReturn([$worker2]);
        $workerHelper->getVoiceAgentIds()->willReturn([1, 2]);

        $this->getAvailableWorkers($task)->shouldReturn([10 => $worker1, 20 => $worker2]);
    }

    public function it_ignores_rejected_workers(
        Worker $worker1,
        Worker $worker2,
        Task $task,
        WorkerHelper $workerHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);

        $worker2->getId()->willReturn(20);
        $worker2->getTypeId()->willReturn(2);
        $worker2->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker2->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker2->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker2->hasActiveTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1]);
        $workerHelper->getForwardingCallWorkers()->willReturn([$worker2]);
        $workerHelper->getVoiceAgentIds()->willReturn([1, 2]);
        $task->getRejectedBy()->willReturn([10]);

        $this->getAvailableWorkers($task)->shouldReturn([20 => $worker2]);
    }

    public function it_merges_online_and_forwarding_agents(
        Worker $worker1,
        Worker $worker2,
        Task $task,
        WorkerHelper $workerHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);

        $worker2->getId()->willReturn(20);
        $worker2->getTypeId()->willReturn(2);
        $worker2->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker2->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker2->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker2->hasActiveTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1, $worker2]);
        $workerHelper->getForwardingCallWorkers()->willReturn([$worker2]);
        $workerHelper->getVoiceAgentIds()->willReturn([1, 2]);

        $this->getAvailableWorkers($task)->shouldReturn([10 => $worker1, 20 => $worker2]);
    }

    public function it_ignores_worker_if_it_has_pending_voice_tasks(
        Worker $worker1,
        Task $task,
        WorkerHelper $workerHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(true);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1]);
        $workerHelper->getForwardingCallWorkers()->willReturn([]);
        $workerHelper->getVoiceAgentIds()->willReturn([1, 2]);

        $this->getAvailableWorkers($task)->shouldReturn([]);
    }

    public function it_ignores_worker_if_it_has_pending_chat_tasks(
        Worker $worker1,
        Task $task,
        WorkerHelper $workerHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(true);
        $worker1->hasActiveTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1]);
        $workerHelper->getForwardingCallWorkers()->willReturn([]);
        $workerHelper->getVoiceAgentIds()->willReturn([1, 2]);

        $this->getAvailableWorkers($task)->shouldReturn([]);
    }

    public function it_ignores_worker_if_it_has_active_voice_tasks(
        Worker $worker1,
        Task $task,
        WorkerHelper $workerHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(true);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1]);
        $workerHelper->getForwardingCallWorkers()->willReturn([]);
        $workerHelper->getVoiceAgentIds()->willReturn([1, 2]);

        $this->getAvailableWorkers($task)->shouldReturn([]);
    }

    public function it_ignores_worker_if_it_has_active_chat_tasks(
        Worker $worker1,
        Task $task,
        WorkerHelper $workerHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(ChatWorkflow::getChannelName())->willReturn(true);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1]);
        $workerHelper->getForwardingCallWorkers()->willReturn([]);
        $workerHelper->getVoiceAgentIds()->willReturn([1, 2]);

        $this->getAvailableWorkers($task)->shouldReturn([]);
    }

    public function it_ignores_worker_if_it_doesnt_have_voice(
        Worker $worker1,
        Task $task,
        WorkerHelper $workerHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1]);
        $workerHelper->getForwardingCallWorkers()->willReturn([]);
        $workerHelper->getVoiceAgentIds()->willReturn([2, 3]);

        $this->getAvailableWorkers($task)->shouldReturn([]);
    }

    public function it_accepts_empty_task(Task $task)
    {
        $this->assignTask($task, []);
    }

    public function it_doesnt_assign_an_offline_agent(Person $agent, Task $task, VoiceTaskHelper $taskHelper)
    {
        $taskHelper->getVoiceQueue($task)->willReturn(null);
        $taskHelper->getWorkerAgent($task)->willReturn($agent);

        $this->assignTask($task, []);
    }

    public function it_handles_round_robin_queue(
        Worker $worker1,
        Worker $worker3,
        VoiceQueue $queue,
        Task $task,
        TaskQueue $taskQueue,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);

        $worker3->getId()->willReturn(30);
        $worker3->getTypeId()->willReturn(3);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(VoiceQueue::ROUTING_MODEL_AUTOMATIC);
        $queue->getActiveAgentsPeopleIds()->willReturn([1, 2, 3]);

        $taskHelper->getVoiceQueue($task)->willReturn($queue);
        $taskHelper->getWorkerAgent($task)->willReturn(null);

        $taskQueue->getAttribute('round_robin_order')->willReturn([1, 2, 3]);
        $taskQueue->setAttribute('round_robin_order', [2, 3, 1])->shouldBeCalled();

        $storage->getTaskQueue('voice', 1)->willReturn($taskQueue);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->setWorkersIds([10])->shouldBeCalled();

        $this->assignTask($task, [$worker1, $worker3]);
    }

    public function it_keeps_round_robin_up_to_date(
        Worker $worker1,
        VoiceQueue $queue,
        Task $task,
        TaskQueue $taskQueue,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(VoiceQueue::ROUTING_MODEL_AUTOMATIC);
        $queue->getActiveAgentsPeopleIds()->willReturn([1, 3, 5]);

        $taskHelper->getVoiceQueue($task)->willReturn($queue);
        $taskHelper->getWorkerAgent($task)->willReturn(null);

        $taskQueue->getAttribute('round_robin_order')->willReturn([1, 2, 3]);
        $taskQueue->setAttribute('round_robin_order', [3, 5, 1])->shouldBeCalled();

        $storage->getTaskQueue('voice', 1)->willReturn($taskQueue);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->setWorkersIds([10])->shouldBeCalled();

        $this->assignTask($task, [$worker1]);
    }

    public function it_keeps_round_robin_order_same_if_no_workers(
        VoiceQueue $queue,
        Task $task,
        TaskQueue $taskQueue,
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
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $this->assignTask($task, []);
    }

    public function it_handles_simulring_queue(
        Worker $worker1,
        Worker $worker3,
        Worker $worker5,
        VoiceQueue $queue,
        Task $task,
        TaskQueue $taskQueue,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);

        $worker3->getId()->willReturn(30);
        $worker3->getTypeId()->willReturn(3);

        $worker3->getId()->willReturn(50);
        $worker3->getTypeId()->willReturn(5);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(VoiceQueue::ROUTING_MODEL_SIMULRING);
        $queue->getActiveAgentsPeopleIds()->willReturn([1, 2, 4, 5]);

        $taskHelper->getVoiceQueue($task)->willReturn($queue);
        $taskHelper->getWorkerAgent($task)->willReturn(null);

        $storage->getTaskQueue('voice', 1)->willReturn($taskQueue);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->setWorkersIds([10, 50])->shouldBeCalled();

        $this->assignTask($task, [$worker1, $worker3, $worker5]);
    }

    public function it_handles_least_utilized_queue(
        Worker $worker1,
        Worker $worker2,
        Worker $worker3,
        Worker $worker4,
        VoiceQueue $queue,
        Task $task,
        TaskQueue $taskQueue,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);

        $worker2->getId()->willReturn(20);
        $worker2->getTypeId()->willReturn(2);

        $worker3->getId()->willReturn(30);
        $worker3->getTypeId()->willReturn(3);

        $worker4->getId()->willReturn(40);
        $worker4->getTypeId()->willReturn(4);

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
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->setWorkersIds([30, 10])->shouldBeCalled();

        $this->assignTask($task, [$worker1, $worker2, $worker3, $worker4]);
    }

    public function it_keeps_least_utilized_up_to_date(
        Worker $worker3,
        Worker $worker4,
        VoiceQueue $queue,
        Task $task,
        TaskQueue $taskQueue,
        VoiceTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker3->getId()->willReturn(30);
        $worker3->getTypeId()->willReturn(3);

        $worker4->getId()->willReturn(40);
        $worker4->getTypeId()->willReturn(4);

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
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->setWorkersIds([40, 30])->shouldBeCalled();

        $this->assignTask($task, [$worker3, $worker4]);
    }
}
