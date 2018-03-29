<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class JobSupervisorCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:job:supervisor')
            ->setDescription('Runs the job queue supervisor (once)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Running Job Supervisor...</info>');

        /** @var \Application\DeskPRO\JobQueue\JobSupervisor $supervisor */
        $supervisor = $this->getContainer()->getJobSupervisor();
        $supervisor->run();

        $output->writeln('<info>Finished Supervising</info>');
    }
}
