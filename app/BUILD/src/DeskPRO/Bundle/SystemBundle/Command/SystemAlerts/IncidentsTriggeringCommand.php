<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\SystemBundle\Command\SystemAlerts;

use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Event\Event;
use DeskPRO\Bundle\SystemBundle\Entity\SystemAlerts\Incident\Incident;
use DeskPRO\Bundle\SystemBundle\SystemAlerts\Triggering\TriggeringProcess;
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
     * @var OutputInterface
     */
    private $output;

    /**
     * @param int        $iterationNum
     * @param float      $time
     * @param Event[]    $events
     * @param Incident[] $newIncidents
     * @param Incident[] $updatedIncidents
     */
    public function batchReport($iterationNum, $time, array $events, array $newIncidents, array $updatedIncidents)
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

        /** @var TriggeringProcess $triggeringProcess */
        $triggeringProcess = $this->getContainer()->get('dp_sys.alerts.triggering_process');

        $batchSize       = $input->getArgument('batch_size');
        $iterationsLimit = $input->getArgument('iterations_limit');
        $totalTime       = 0;
        $i               = 1;
        $finished        = false;
        while ($i <= $iterationsLimit && !$finished) {
            $start    = microtime(true);
            $result   = $triggeringProcess->run($batchSize);
            $finished = empty($result['events']);
            if (!$finished) {
                $time = microtime(true) - $start;
                $totalTime += $time;
                $this->batchReport(
                    $i, $time, $result['events'], $result['new_incidents'], $result['updated_incidents']);
            }
            ++$i;
        }

        $output->writeln(
            sprintf('Finished in %s seconds', $iterationsLimit, $batchSize, $totalTime));

        return 0;
    }
}
