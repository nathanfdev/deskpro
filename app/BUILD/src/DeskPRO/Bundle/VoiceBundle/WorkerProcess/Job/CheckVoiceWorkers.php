<?php

namespace DeskPRO\Bundle\VoiceBundle\WorkerProcess\Job;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\WorkerProcess\Job\AbstractJob;
use DeskPRO\Bundle\AppBundle\Entity\VoicePhoneCall;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Model\Task;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\ChatWorkflow;
use DeskPRO\Bundle\VoiceBundle\TaskRouter\Workflow\VoiceWorkflow;

/**
 * Class CheckVoiceWorkers.
 */
class CheckVoiceWorkers extends AbstractJob
{
    const DEFAULT_INTERVAL = 600;

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function run()
    {
        $storage = $this->getContainer()->get('dp.voice.task_router.storage');
        $workers = $storage->getAllWorkers();
        foreach ($workers as $worker) {
            $chatTaskIds = array_merge(
                $worker->getPendingTaskIdsForChannel(ChatWorkflow::getChannelName()),
                $worker->getActiveTaskIdsForChannel(ChatWorkflow::getChannelName())
            );

            $voiceTaskIds = array_merge(
                $worker->getPendingTaskIdsForChannel(VoiceWorkflow::getChannelName()),
                $worker->getActiveTaskIdsForChannel(VoiceWorkflow::getChannelName())
            );

            $endedTasks = [];
            if ($chatTaskIds) {
                $endedTasks = array_merge($endedTasks, $this->getEndedChatTasks($storage->getTasks($chatTaskIds)));
            }
            if ($voiceTaskIds) {
                $endedTasks = array_merge($endedTasks, $this->getEndedVoiceTasks($storage->getTasks($voiceTaskIds)));
            }

            if ($endedTasks) {
                foreach ($endedTasks as $task) {
                    $worker->removeActiveTask($task);
                    $worker->removePendingTask($task);

                    $this->getLogger()->logWarn("Found stuck voice worker, worker_id {$worker->getId()}, task_id = {$task->getId()}");
                }

                $storage->saveWorker($worker);
            }
        }
    }

    /**
     * @param Task[] $tasks
     *
     * @throws \Exception
     *
     * @return Task[]
     */
    private function getEndedChatTasks(array $tasks)
    {
        $endedTasks = [];

        foreach ($tasks as $task) {
            $chatId = $task->getAttribute('chat');
            if (!$chatId) {
                $endedTasks[] = $task;
                continue;
            }

            $chat = $this->getContainer()->get('doctrine.orm.entity_manager')->getRepository(ChatConversation::class)->find($chatId);
            if (!$chat) {
                $endedTasks[] = $task;
                continue;
            }

            if ($chat->getStatus() !== ChatConversation::STATUS_OPEN) {
                $endedTasks[] = $task;
            }
        }

        return $endedTasks;
    }

    /**
     * @param Task[] $tasks
     *
     * @throws \Exception
     *
     * @return Task[]
     */
    private function getEndedVoiceTasks(array $tasks)
    {
        $endedTasks = [];

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        foreach ($tasks as $task) {
            $phoneCallId = $task->getAttribute('phone_call');
            if (!$phoneCallId) {
                $endedTasks[] = $task;
                continue;
            }

            /** @var VoicePhoneCall $phoneCall */
            $phoneCall = $em->getRepository(VoicePhoneCall::class)->find($phoneCallId);
            if (!$phoneCall) {
                $endedTasks[] = $task;
                continue;
            }

            $endedStatuses = [
                VoicePhoneCall::STATUS_ENDED,
                VoicePhoneCall::STATUS_VOICEMAIL,
                VoicePhoneCall::STATUS_CANCELED,
                VoicePhoneCall::STATUS_FAILED,
            ];

            if (in_array($phoneCall->getStatus(), $endedStatuses)
                || (!$phoneCall->getCallSid() && $phoneCall->getDateCreated() < new \DateTime('-5 minutes'))
            ) {
                $endedTasks[] = $task;
            }
        }

        return $endedTasks;
    }
}
