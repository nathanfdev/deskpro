<?php

namespace DeskPRO\Services\EmailProcess\TaskRunner\Processor;

use DeskPRO\Component\TaskRunner\Processor\AbstractCommandProcessor;
use DeskPRO\Component\TaskRunner\Task\Task;

class EmailTaskProcessor extends AbstractCommandProcessor
{
    /**
     * @param Task $task
     *
     * @return string
     */
    protected function getCmdString(Task $task)
    {
        $email_id = (int) $task->get('source_id');

        if (!$email_id) {
            $this->logger->warn(sprintf('[EmailTaskProcessor] No source_id in task %s', $task->getId()));
            throw new \InvalidArgumentException();
        }

        $cmd_path = realpath(DP_DIR.'/bin/console');
        $cmd      = dp_get_php_command($cmd_path, 'dp:process-email --enable-retries --expect-pending --source '.$email_id);

        $this->logger->info("[EmailTaskProcessor] <EmailSource::{$email_id}> process command: $cmd", [
            'task' => $task,
        ]);

        return $cmd;
    }
}
