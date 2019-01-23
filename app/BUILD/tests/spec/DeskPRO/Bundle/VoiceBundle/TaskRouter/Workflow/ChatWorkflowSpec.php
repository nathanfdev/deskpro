<?php

namespace spec\DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\AgentTeam as AgentTeamRepo;
use Application\DeskPRO\EntityRepository\Person as PersonRepo;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueueAgent;
use DeskPRO\Bundle\VoiceBundle\Helper\ChatTaskHelper;
use DeskPRO\Bundle\VoiceBundle\Settings\ChatSettingsResolver;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\TaskQueue;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Worker;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\StorageAdapter\StorageAdapterInterface;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\ChatWorkflow;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use PhpSpec\ObjectBehavior;

/**
 * Class ChatWorkflowSpec.
 *
 * @mixin \DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\ChatWorkflow
 */
class ChatWorkflowSpec extends ObjectBehavior
{
    public function let(
        EntityManager           $em,
        PersonRepo              $personRepo,
        AgentTeamRepo           $agentTeamRepo,
        ChatTaskHelper          $taskHelper,
        ChatSettingsResolver    $settingsResolver,
        StorageAdapterInterface $storage
    ) {
        $this->beConstructedWith($em, $taskHelper, $settingsResolver, $storage);
        $em->getRepository(Person::class)->willReturn($personRepo);
        $em->getRepository(AgentTeam::class)->willReturn($agentTeamRepo);
    }

    public function it_returns_empty_list_of_workers(Task $task, PersonRepo $personRepo, StorageAdapterInterface $storage)
    {
        $storage->getOnlineWorkersByType('agent')->willReturn([]);
        $personRepo->getActiveAgentIdsForUserChat()->willReturn([]);

        $this->getAvailableWorkers($task)->shouldReturn([]);
    }

    public function it_returns_a_list_of_workers(
        Worker $worker1,
        Worker $worker2,
        Task $task,
        PersonRepo $personRepo,
        ChatSettingsResolver $settingsResolver,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker2->getId()->willReturn(20);
        $worker2->getTypeId()->willReturn(2);

        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker1->getActiveTaskIdsForChannel(ChatWorkflow::getChannelName())->willReturn([]);

        $worker2->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker2->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker2->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker2->getActiveTaskIdsForChannel(ChatWorkflow::getChannelName())->willReturn([]);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1, $worker2]);
        $settingsResolver->getMaxChatsCount()->willReturn(5);
        $personRepo->getActiveAgentIdsForUserChat()->willReturn([1, 2]);

        $this->getAvailableWorkers($task)->shouldReturn([10 => $worker1, 20 => $worker2]);
    }

    public function it_filters_workers_by_use_chat_permission(
        Worker $worker1,
        Worker $worker2,
        Task $task,
        PersonRepo $personRepo,
        ChatSettingsResolver $settingsResolver,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker2->getId()->willReturn(20);
        $worker2->getTypeId()->willReturn(2);

        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker1->getActiveTaskIdsForChannel(ChatWorkflow::getChannelName())->willReturn([]);

        $worker2->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker2->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker2->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker2->getActiveTaskIdsForChannel(ChatWorkflow::getChannelName())->willReturn([]);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1, $worker2]);
        $settingsResolver->getMaxChatsCount()->willReturn(5);
        $personRepo->getActiveAgentIdsForUserChat()->willReturn([2]);

        $this->getAvailableWorkers($task)->shouldReturn([20 => $worker2]);
    }

    public function it_ignores_rejected_workers(
        Worker $worker1,
        Worker $worker2,
        Task $task,
        PersonRepo $personRepo,
        ChatSettingsResolver $settingsResolver,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker2->getId()->willReturn(20);
        $worker2->getTypeId()->willReturn(2);

        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker1->getActiveTaskIdsForChannel(ChatWorkflow::getChannelName())->willReturn([]);

        $worker2->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker2->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker2->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker2->getActiveTaskIdsForChannel(ChatWorkflow::getChannelName())->willReturn([]);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1, $worker2]);
        $settingsResolver->getMaxChatsCount()->willReturn(5);
        $task->getRejectedBy()->willReturn([10]);
        $personRepo->getActiveAgentIdsForUserChat()->willReturn([1, 2]);

        $this->getAvailableWorkers($task)->shouldReturn([20 => $worker2]);
    }

    public function it_ignores_worker_if_it_has_pending_voice_tasks(
        Worker $worker1,
        Task $task,
        PersonRepo $personRepo,
        ChatSettingsResolver $settingsResolver,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(true);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker1->getActiveTaskIdsForChannel(ChatWorkflow::getChannelName())->willReturn([]);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1]);
        $settingsResolver->getMaxChatsCount()->willReturn(5);
        $personRepo->getActiveAgentIdsForUserChat()->willReturn([1, 2]);

        $this->getAvailableWorkers($task)->shouldReturn([]);
    }

    public function it_ignores_worker_if_it_has_active_voice_tasks(
        Worker $worker1,
        Task $task,
        PersonRepo $personRepo,
        ChatSettingsResolver $settingsResolver,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(true);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker1->getActiveTaskIdsForChannel(ChatWorkflow::getChannelName())->willReturn([]);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1]);
        $settingsResolver->getMaxChatsCount()->willReturn(5);
        $personRepo->getActiveAgentIdsForUserChat()->willReturn([1, 2]);

        $this->getAvailableWorkers($task)->shouldReturn([]);
    }

    public function it_ignores_worker_if_it_has_pending_chat_tasks(
        Worker $worker1,
        Task $task,
        PersonRepo $personRepo,
        ChatSettingsResolver $settingsResolver,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(true);
        $worker1->getActiveTaskIdsForChannel(ChatWorkflow::getChannelName())->willReturn([]);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1]);
        $settingsResolver->getMaxChatsCount()->willReturn(5);
        $personRepo->getActiveAgentIdsForUserChat()->willReturn([1, 2]);

        $this->getAvailableWorkers($task)->shouldReturn([]);
    }

    public function it_ignores_worker_if_it_has_too_many_active_chat_tasks(
        Worker $worker1,
        Task $task,
        PersonRepo $personRepo,
        ChatSettingsResolver $settingsResolver,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker1->hasPendingTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasActiveTasksForChannel(VoiceWorkflow::getChannelName())->willReturn(false);
        $worker1->hasPendingTasksForChannel(ChatWorkflow::getChannelName())->willReturn(false);
        $worker1->getActiveTaskIdsForChannel(ChatWorkflow::getChannelName())->willReturn([1, 2, 3]);

        $storage->getOnlineWorkersByType('agent')->willReturn([$worker1]);
        $settingsResolver->getMaxChatsCount()->willReturn(2);
        $personRepo->getActiveAgentIdsForUserChat()->willReturn([1, 2]);

        $this->getAvailableWorkers($task)->shouldReturn([]);
    }

    public function it_accepts_empty_task(Task $task)
    {
        $this->assignTask($task, []);
    }

    public function it_handles_all_agents(
        Worker $worker1,
        Worker $worker3,
        Person $agent1,
        Person $agent2,
        Task $task,
        UserChatQueue $queue,
        TaskQueue $taskQueue,
        PersonRepo $personRepo,
        ChatTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker3->getId()->willReturn(30);
        $worker3->getTypeId()->willReturn(3);

        $agent1->getId()->willReturn(1);
        $agent1->offsetExists('id')->willReturn(true);
        $agent1->offsetGet('id')->willReturn(1);
        $agent2->getId()->willReturn(2);
        $agent2->offsetExists('id')->willReturn(true);
        $agent2->offsetGet('id')->willReturn(2);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(UserChatQueue::ROUTING_MODEL_ROUND_ROBIN);
        $queue->isAllAgents()->willReturn(true);

        $personRepo->getActiveAgentIdsForUserChat()->willReturn([1, 2]);
        $personRepo->findBy(['id' => [1, 2]])->willReturn([$agent1, $agent2]);

        $queue->getAnswerTimeout()->willReturn(null);

        $taskHelper->getChatQueue($task)->willReturn($queue);

        $taskQueue->getAttribute('round_robin_order')->willReturn([
            ['type' => 'agent', 'id' => 1],
            ['type' => 'agent_team', 'id' => 2],
            ['type' => 'agent', 'id' => 4],
        ]);
        $taskQueue->setAttribute('round_robin_order', [
            ['type' => 'agent', 'id' => 2],
            ['type' => 'agent', 'id' => 1],
        ])->shouldBeCalled();

        $storage->getTaskQueue('chat', 1)->willReturn($taskQueue);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->setWorkersIds([10])->shouldBeCalled();
        $task->setDateExpireOffset(null)->shouldBeCalled();

        $this->assignTask($task, [$worker1, $worker3]);
    }

    public function it_handles_round_robin_queue(
        Worker $worker1,
        Worker $worker3,
        Task $task,
        UserChatQueue $queue,
        TaskQueue $taskQueue,
        ArrayCollection $targets,
        UserChatQueueAgent $target1,
        UserChatQueueAgent $target2,
        UserChatQueueAgent $target3,
        PersonRepo $personRepo,
        ChatTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker3->getId()->willReturn(30);
        $worker3->getTypeId()->willReturn(3);

        $target1->toArray()->willReturn(['type' => 'agent', 'id' => 1]);
        $target1->getSort()->willReturn(10);
        $target2->toArray()->willReturn(['type' => 'agent_team', 'id' => 2]);
        $target2->getSort()->willReturn(20);
        $target3->toArray()->willReturn(['type' => 'agent', 'id' => 4]);
        $target3->getSort()->willReturn(30);

        $targets->toArray()->willReturn([$target1, $target2, $target3]);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(UserChatQueue::ROUTING_MODEL_ROUND_ROBIN);
        $queue->isAllAgents()->willReturn(false);
        $queue->getTargets()->willReturn($targets);
        $queue->getAnswerTimeout()->willReturn(null);

        $personRepo->getActiveAgentIdsForUserChat()->willReturn([1, 2]);

        $taskHelper->getChatQueue($task)->willReturn($queue);

        $taskQueue->getAttribute('round_robin_order')->willReturn([
            ['type' => 'agent', 'id' => 1],
            ['type' => 'agent_team', 'id' => 2],
            ['type' => 'agent', 'id' => 4],
        ]);
        $taskQueue->setAttribute('round_robin_order', [
            ['type' => 'agent_team', 'id' => 2],
            ['type' => 'agent', 'id' => 4],
            ['type' => 'agent', 'id' => 1],
        ])->shouldBeCalled();

        $storage->getTaskQueue('chat', 1)->willReturn($taskQueue);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->setWorkersIds([10])->shouldBeCalled();
        $task->setDateExpireOffset(null)->shouldBeCalled();

        $this->assignTask($task, [$worker1, $worker3]);
    }

    public function it_keeps_round_robin_up_to_date(
        Worker $worker1,
        Worker $worker3,
        Task $task,
        UserChatQueue $queue,
        TaskQueue $taskQueue,
        UserChatQueueAgent $target1,
        UserChatQueueAgent $target2,
        UserChatQueueAgent $target3,
        ArrayCollection $targets,
        PersonRepo $personRepo,
        ChatTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker3->getId()->willReturn(30);
        $worker3->getTypeId()->willReturn(3);

        $target1->toArray()->willReturn(['type' => 'agent', 'id' => 1]);
        $target1->getSort()->willReturn(10);
        $target2->toArray()->willReturn(['type' => 'agent_team', 'id' => 5]);
        $target2->getSort()->willReturn(20);
        $target3->toArray()->willReturn(['type' => 'agent', 'id' => 6]);
        $target3->getSort()->willReturn(30);

        $targets->toArray()->willReturn([$target1, $target2, $target3]);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(UserChatQueue::ROUTING_MODEL_ROUND_ROBIN);
        $queue->isAllAgents()->willReturn(false);
        $queue->getTargets()->willReturn($targets);
        $queue->getAnswerTimeout()->willReturn(null);

        $personRepo->getActiveAgentIdsForUserChat()->willReturn([1, 2]);

        $taskHelper->getChatQueue($task)->willReturn($queue);

        $taskQueue->getAttribute('round_robin_order')->willReturn([
            ['type' => 'agent', 'id' => 1],
            ['type' => 'agent_team', 'id' => 2],
            ['type' => 'agent', 'id' => 4],
        ]);
        $taskQueue->setAttribute('round_robin_order', [
            ['type' => 'agent_team', 'id' => 5],
            ['type' => 'agent', 'id' => 6],
            ['type' => 'agent', 'id' => 1],
        ])->shouldBeCalled();

        $storage->getTaskQueue('chat', 1)->willReturn($taskQueue);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->setWorkersIds([10])->shouldBeCalled();
        $task->setDateExpireOffset(null)->shouldBeCalled();

        $this->assignTask($task, [$worker1, $worker3]);
    }

    public function it_keeps_round_robin_order_same_if_no_workers(
        Worker $worker1,
        Worker $worker3,
        Task $task,
        UserChatQueue $queue,
        TaskQueue $taskQueue,
        UserChatQueueAgent $target1,
        UserChatQueueAgent $target2,
        UserChatQueueAgent $target3,
        ArrayCollection $targets,
        PersonRepo $personRepo,
        AgentTeamRepo $agentTeamRepo,
        AgentTeam $agentTeam,
        ChatTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker3->getId()->willReturn(30);
        $worker3->getTypeId()->willReturn(3);

        $target1->toArray()->willReturn(['type' => 'agent', 'id' => 1]);
        $target1->getSort()->willReturn(10);
        $target2->toArray()->willReturn(['type' => 'agent_team', 'id' => 2]);
        $target2->getSort()->willReturn(20);
        $target3->toArray()->willReturn(['type' => 'agent', 'id' => 4]);
        $target3->getSort()->willReturn(30);

        $targets->toArray()->willReturn([$target1, $target2, $target3]);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(UserChatQueue::ROUTING_MODEL_ROUND_ROBIN);
        $queue->isAllAgents()->willReturn(false);
        $queue->getTargets()->willReturn($targets);
        $queue->getAnswerTimeout()->willReturn(null);

        $personRepo->getActiveAgentIdsForUserChat()->willReturn([1, 2]);
        $agentTeamRepo->find(2)->willReturn($agentTeam);
        $agentTeam->getMembers()->willReturn([]);

        $taskHelper->getChatQueue($task)->willReturn($queue);

        $taskQueue->getAttribute('round_robin_order')->willReturn([
            ['type' => 'agent', 'id' => 1],
            ['type' => 'agent_team', 'id' => 2],
            ['type' => 'agent', 'id' => 4],
        ]);
        $taskQueue->setAttribute('round_robin_order', [
            ['type' => 'agent', 'id' => 1],
            ['type' => 'agent_team', 'id' => 2],
            ['type' => 'agent', 'id' => 4],
        ])->shouldBeCalled();

        $storage->getTaskQueue('chat', 1)->willReturn($taskQueue);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $this->assignTask($task, []);
    }

    public function it_handles_simulring_queue(
        Worker $worker1,
        Worker $worker3,
        Task $task,
        UserChatQueue $queue,
        TaskQueue $taskQueue,
        UserChatQueueAgent $target1,
        UserChatQueueAgent $target2,
        UserChatQueueAgent $target3,
        ArrayCollection $targets,
        PersonRepo $personRepo,
        Person $agent1,
        Person $agent2,
        Person $agent3,
        ChatTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker3->getId()->willReturn(30);
        $worker3->getTypeId()->willReturn(3);

        $target1->getSort()->willReturn(10);
        $target1->getAgent()->willReturn($agent1);
        $target2->getSort()->willReturn(20);
        $target2->getAgent()->willReturn($agent2);
        $target3->getSort()->willReturn(30);
        $target3->getAgent()->willReturn($agent3);

        $targets->toArray()->willReturn([$target1, $target2, $target3]);

        $agent1->getId()->willReturn(1);
        $agent2->getId()->willReturn(2);
        $agent3->getId()->willReturn(3);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(UserChatQueue::ROUTING_MODEL_SIMULRING);
        $queue->isAllAgents()->willReturn(false);
        $queue->getTargets()->willReturn($targets);
        $queue->getAnswerTimeout()->willReturn(null);

        $personRepo->getActiveAgentIdsForUserChat()->willReturn([1, 2]);

        $taskHelper->getChatQueue($task)->willReturn($queue);

        $storage->getTaskQueue('chat', 1)->willReturn($taskQueue);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->setWorkersIds([10, 30])->shouldBeCalled();
        $task->setDateExpireOffset(null)->shouldBeCalled();

        $this->assignTask($task, [$worker1, $worker3]);
    }

    public function it_handles_simulring_queue_if_no_workers(
        Task $task,
        UserChatQueue $queue,
        TaskQueue $taskQueue,
        UserChatQueueAgent $target1,
        UserChatQueueAgent $target2,
        UserChatQueueAgent $target3,
        ArrayCollection $targets,
        PersonRepo $personRepo,
        Person $agent1,
        Person $agent2,
        Person $agent3,
        ChatTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $target1->getSort()->willReturn(10);
        $target1->getAgent()->willReturn($agent1);
        $target2->getSort()->willReturn(20);
        $target2->getAgent()->willReturn($agent2);
        $target3->getSort()->willReturn(30);
        $target3->getAgent()->willReturn($agent3);

        $targets->toArray()->willReturn([$target1, $target2, $target3]);

        $agent1->getId()->willReturn(1);
        $agent2->getId()->willReturn(2);
        $agent3->getId()->willReturn(3);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(UserChatQueue::ROUTING_MODEL_SIMULRING);
        $queue->isAllAgents()->willReturn(false);
        $queue->getTargets()->willReturn($targets);
        $queue->getAnswerTimeout()->willReturn(null);

        $personRepo->getActiveAgentIdsForUserChat()->willReturn([1, 2]);

        $taskHelper->getChatQueue($task)->willReturn($queue);

        $storage->getTaskQueue('chat', 1)->willReturn($taskQueue);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->setWorkersIds([])->shouldBeCalled();
        $task->setDateExpireOffset(null)->shouldBeCalled();

        $this->assignTask($task, []);
    }

    public function it_handles_empty_least_utilized_queue(
        Worker $worker1,
        Worker $worker2,
        Worker $worker3,
        Task $task,
        UserChatQueue $queue,
        TaskQueue $taskQueue,
        UserChatQueueAgent $target1,
        UserChatQueueAgent $target2,
        UserChatQueueAgent $target3,
        ArrayCollection $targets,
        ChatTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker2->getId()->willReturn(20);
        $worker2->getTypeId()->willReturn(2);
        $worker3->getId()->willReturn(30);
        $worker3->getTypeId()->willReturn(3);

        $target1->toArray()->willReturn(['type' => 'agent', 'id' => 1]);
        $target1->getSort()->willReturn(10);
        $target2->toArray()->willReturn(['type' => 'agent', 'id' => 2]);
        $target2->getSort()->willReturn(20);
        $target3->toArray()->willReturn(['type' => 'agent', 'id' => 3]);
        $target3->getSort()->willReturn(30);

        $targets->toArray()->willReturn([$target1, $target2, $target3]);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(UserChatQueue::ROUTING_MODEL_LEAST_UTILIZED);
        $queue->isAllAgents()->willReturn(false);
        $queue->getTargets()->willReturn($targets);
        $queue->getMaxQueueSize()->willReturn(2);
        $queue->getAnswerTimeout()->willReturn(null);

        $taskHelper->getChatQueue($task)->willReturn($queue);

        $storage->getTaskQueue('chat', 1)->willReturn($taskQueue);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->setWorkersIds([10, 20])->shouldBeCalled();
        $task->setDateExpireOffset(null)->shouldBeCalled();

        $taskQueue->getAttribute('answered_chats_counts')->willReturn(null);

        $this->assignTask($task, [$worker1, $worker2, $worker3]);
    }

    public function it_handles_least_utilized_queue_order(
        Worker $worker1,
        Worker $worker2,
        Worker $worker3,
        Task $task,
        UserChatQueue $queue,
        TaskQueue $taskQueue,
        UserChatQueueAgent $target1,
        UserChatQueueAgent $target2,
        UserChatQueueAgent $target3,
        ArrayCollection $targets,
        ChatTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker2->getId()->willReturn(20);
        $worker2->getTypeId()->willReturn(2);
        $worker3->getId()->willReturn(30);
        $worker3->getTypeId()->willReturn(3);

        $target1->toArray()->willReturn(['type' => 'agent', 'id' => 1]);
        $target1->getSort()->willReturn(10);
        $target2->toArray()->willReturn(['type' => 'agent', 'id' => 2]);
        $target2->getSort()->willReturn(20);
        $target3->toArray()->willReturn(['type' => 'agent', 'id' => 3]);
        $target3->getSort()->willReturn(30);

        $targets->toArray()->willReturn([$target1, $target2, $target3]);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(UserChatQueue::ROUTING_MODEL_LEAST_UTILIZED);
        $queue->isAllAgents()->willReturn(false);
        $queue->getTargets()->willReturn($targets);
        $queue->getMaxQueueSize()->willReturn(2);
        $queue->getAnswerTimeout()->willReturn(null);

        $taskHelper->getChatQueue($task)->willReturn($queue);

        $storage->getTaskQueue('chat', 1)->willReturn($taskQueue);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->setWorkersIds([20, 10])->shouldBeCalled();
        $task->setDateExpireOffset(null)->shouldBeCalled();

        $taskQueue->getAttribute('answered_chats_counts')->willReturn([
            [
                'target' => ['type' => 'agent', 'id' => 1],
                'count'  => 10,
            ],
            [
                'target' => ['type' => 'agent', 'id' => 2],
                'count'  => 5,
            ],
            [
                'target' => ['type' => 'agent', 'id' => 3],
                'count'  => 15,
            ],
        ]);

        $this->assignTask($task, [$worker1, $worker2, $worker3]);
    }

    public function it_keeps_least_utilized_up_to_date(
        Worker $worker1,
        Worker $worker2,
        Worker $worker3,
        Task $task,
        UserChatQueue $queue,
        TaskQueue $taskQueue,
        UserChatQueueAgent $target1,
        UserChatQueueAgent $target2,
        UserChatQueueAgent $target3,
        ArrayCollection $targets,
        ChatTaskHelper $taskHelper,
        StorageAdapterInterface $storage
    ) {
        $worker1->getId()->willReturn(10);
        $worker1->getTypeId()->willReturn(1);
        $worker2->getId()->willReturn(20);
        $worker2->getTypeId()->willReturn(2);
        $worker3->getId()->willReturn(30);
        $worker3->getTypeId()->willReturn(3);

        $target1->toArray()->willReturn(['type' => 'agent', 'id' => 1]);
        $target1->getSort()->willReturn(10);
        $target2->toArray()->willReturn(['type' => 'agent', 'id' => 2]);
        $target2->getSort()->willReturn(20);
        $target3->toArray()->willReturn(['type' => 'agent', 'id' => 3]);
        $target3->getSort()->willReturn(30);

        $targets->toArray()->willReturn([$target1, $target2, $target3]);

        $queue->getId()->willReturn(1);
        $queue->getRoutingModel()->willReturn(UserChatQueue::ROUTING_MODEL_LEAST_UTILIZED);
        $queue->isAllAgents()->willReturn(false);
        $queue->getTargets()->willReturn($targets);
        $queue->getMaxQueueSize()->willReturn(2);
        $queue->getAnswerTimeout()->willReturn(null);

        $taskHelper->getChatQueue($task)->willReturn($queue);

        $storage->getTaskQueue('chat', 1)->willReturn($taskQueue);
        $storage->saveTaskQueue($taskQueue)->shouldBeCalled();

        $task->setWorkersIds([20, 30])->shouldBeCalled();
        $task->setDateExpireOffset(null)->shouldBeCalled();

        $taskQueue->getAttribute('answered_chats_counts')->willReturn([
            [
                'target' => ['type' => 'agent', 'id' => 1],
                'count'  => 10,
            ],
            [
                'target' => ['type' => 'agent', 'id' => 5],
                'count'  => 5,
            ],
            [
                'target' => ['type' => 'agent', 'id' => 3],
                'count'  => 1,
            ],
        ]);

        $this->assignTask($task, [$worker1, $worker2, $worker3]);
    }
}
