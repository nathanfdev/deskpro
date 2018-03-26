<?php

namespace DeskPRO\Services\EmailCollection\TaskRunner\Processor;

use DeskPRO\Component\TaskRunner\Processor\AbstractCommandProcessor;
use DeskPRO\Component\TaskRunner\Task\Task;

class AccountProcessor extends AbstractCommandProcessor
{
    /**
     * @param Task $task
     *
     * @return string
     */
    protected function getCmdString(Task $task)
    {
        $account_id = $task->get('account_id');

        $cmd_path = realpath(DP_DIR.'/bin/console');
        $cmd      = dp_get_php_command($cmd_path, 'dp:collect-email '.$account_id);

        $this->logger->info("[AccountProcessor] <EmailAccount::{$account_id}> process command: $cmd", [
            'task' => $task,
        ]);

        return $cmd;
    }
}
