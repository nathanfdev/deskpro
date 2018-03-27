<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use DeskPRO\Bundle\SystemBundle\Command\SystemAlerts\IncidentsTriggeringCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Class ProcessSystemAlertEvents.
 */
class ProcessSystemAlertEvents extends AbstractJob
{
    const DEFAULT_INTERVAL = 60; // 1 minute

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $command = new IncidentsTriggeringCommand();
        $command->setContainer($this->getContainer());
        $command->run(new ArrayInput([]), $output = new BufferedOutput());
        $this->logStatus($output->fetch());
    }
}
