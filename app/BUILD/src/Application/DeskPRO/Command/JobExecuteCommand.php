<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\JobQueue\JobWorker;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class JobExecuteCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:job:execute')
            ->setDescription('Bypasses all queue settings and immediately passes a job to the JobRouter - should only be used to debug')
            ->addArgument('job', InputOption::VALUE_REQUIRED, 'Execute given job ID now, regardless of status');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = $this->getContainer()->get('doctrine.dbal.default_connection');
        $router     = $this->getContainer()->getSystemService('job_router');
        $queue      = $this->getContainer()->getSystemService('job_queue');
        $worker     = new JobWorker($connection, $router, $queue);

        $res = $worker->executeJobById($input->getArgument('job'));

        if ($res) {
            $output->writeln('<info>Found job and attempted to process it</info>');
        } else {
            $output->writeln('<error>Failed to find job</error>');
        }
    }
}
