<?php

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\TaskQueue;
use Application\DeskPRO\TaskQueueJob\AbstractJob as TaskQueueJob;
use DpSys\LowError\SystemErrorHandler;

/**
 * Runs queued tasks if there are any.
 */
class RunQueuedTasks extends AbstractJob
{
    const DEFAULT_INTERVAL = 60; // 60 seconds

    public function run()
    {
        $max_run    = 25;
        $start_time = microtime(true);
        $task       = false;

        $em     = App::getOrm();
        $logger = $this->getLogger();

        while (($remaining_time = $max_run - (microtime(true) - $start_time)) > 1) {
            if (!$task) {
                /** @var TaskQueue $task */
                $task = App::getEntityRepository(TaskQueue::class)->getRunnableTask();
            }

            if (!$task) {
                break;
            }

            $taskId          = $task->getId();
            $taskRunnerClass = $task->getRunnerClass();

            $logger->logInfo("Running task #$taskId: $taskRunnerClass");

            try {
                $result = $task->runTask($remaining_time, $logger);
            } catch (\Exception $e) {
                $result = false;
                $logger->logWarn("Task #$taskId ($taskRunnerClass) errored: ".$e->getMessage());
                SystemErrorHandler::logException($e);
            }

            $em->flush();

            if ($result === TaskQueueJob::TASK_COMPLETED) {
                // finished task, move on
                $logger->logInfo("Task #$taskId ($taskRunnerClass) completed successfully.");
                $task = false;
            } elseif ($result === TaskQueueJob::TASK_CONTINUING) {
                // still running task, so keep $task in case we have more time
                $logger->logInfo("Task #$taskId ($taskRunnerClass) to be continued.");
            } else {
                // task errored, logged above already
                $task = false;
            }
        }
    }
}
