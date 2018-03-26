<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Command\SystemAlerts;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\LogReducer;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\TriggeringProcess;
use DpSys\LowError\SystemErrorHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class IncidentsTriggeringCommand.
 */
class IncidentsTriggeringCommand extends ContainerAwareCommand
{
    /**
     * Log processing will be terminated once incidents number exceeds MAX_INCIDENTS.
     */
    const MAX_INCIDENTS = 100;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:sys:trigger-incidents')
            ->setDescription('Process system alerts events log')
            ->addArgument('batch_size', InputArgument::OPTIONAL, 'Number of events processed within an iteration', 100)
            ->addArgument('iterations_limit', InputArgument::OPTIONAL, 'Iteration limit per single command run', 5)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        if ($this->shouldTerminate()) {
            $this->logTerminated();

            return 1;
        }

        $start = microtime(true);

        $this->getLogReducer()->preProcessingReducer();
        $this->runTriggeringProcess(
            $input->getArgument('batch_size'),
            $input->getArgument('iterations_limit')
        );
        $this->getLogReducer()->postProcessingReducer();

        $time = microtime(true) - $start;
        $output->writeln("Finished in $time seconds");

        return 0;
    }

    /**
     * @return bool
     */
    private function shouldTerminate()
    {
        return $this->getTriggeringProcess()->countIncidents() > self::MAX_INCIDENTS;
    }

    /**
     * Logs / Alerts about terminated triggering process.
     */
    private function logTerminated()
    {
        SystemErrorHandler::logException(new \Exception(
            $message = 'System alerts log processing was terminated because incidents number has reached '
                      .'the limit. Resolve and clear the existing incidents to continue log processing.'
        ));
        $this->output->writeln($message);
    }

    /**
     * @param int $batchSize
     * @param int $iterationsLimit
     */
    private function runTriggeringProcess($batchSize, $iterationsLimit)
    {
        $process  = $this->getTriggeringProcess();
        $i        = 1;
        $finished = false;
        while ($i <= $iterationsLimit && !$finished) {
            $start    = microtime(true);
            $result   = $process->run($batchSize);
            $finished = empty($result['events']);
            if (!$finished) {
                $time = microtime(true) - $start;
                $this->batchReport(
                    $i, $time, $result['events'], $result['new_incidents'], $result['updated_incidents']);
            }
            ++$i;
        }
    }

    /**
     * @param int        $iterationNum
     * @param float      $time
     * @param Event[]    $events
     * @param Incident[] $newIncidents
     * @param Incident[] $updatedIncidents
     */
    private function batchReport($iterationNum, $time, array $events, array $newIncidents, array $updatedIncidents)
    {
        $this->output->writeln(sprintf('Iteration #%d: %d events processed', $iterationNum, count($events)));
        $this->output->writeln('');

        if ($count = count($newIncidents)) {
            $this->output->writeln(sprintf('Created %d new incidents:', $count));
            foreach ($newIncidents as $incident) {
                $this->output->writeln($incident->getTitle());
            }
        } else {
            $this->output->writeln('No new incidents');
        }
        $this->output->writeln('');

        if ($count = count($updatedIncidents)) {
            $this->output->writeln(sprintf('Updated %d incidents:', $count));
            foreach ($updatedIncidents as $incident) {
                $this->output->writeln($incident->getTitle());
            }
        } else {
            $this->output->writeln('No incidents were updated');
        }
        $this->output->writeln(sprintf('Batch took %s seconds', $time));
        $this->output->writeln('');
        $this->output->writeln('----------');
        $this->output->writeln('');
    }

    /**
     * @return LogReducer
     */
    private function getLogReducer()
    {
        return $this->getContainer()->get('dp_sys.alerts.log_reducer');
    }

    /**
     * @return TriggeringProcess
     */
    private function getTriggeringProcess()
    {
        return $this->getContainer()->get('dp_sys.alerts.triggering_process');
    }
}
