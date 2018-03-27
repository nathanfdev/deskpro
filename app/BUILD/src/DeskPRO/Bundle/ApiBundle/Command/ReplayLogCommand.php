<?php

namespace DeskPRO\Bundle\ApiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReplayLogCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dpdev:replay-log')
            ->setDescription('Replays log already stored in DB')
            ->addArgument('request_id', InputArgument::REQUIRED, 'Id of request to replay (string)')
            ->addOption('use-id', 'i', InputOption::VALUE_NONE, 'Use integer id instead if request_id')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $replayer = $this->getContainer()->get('api_log.replayer');

        $id = $input->getArgument('request_id');

        if ($input->getOption('use-id')) {
            $log = $this->getContainer()->get('doctrine.orm.default_entity_manager')->find('\DeskPRO\Bundle\AppBundle\Entity\ApiLog', $id);
            if (!$log) {
                throw new \InvalidArgumentException(sprintf('Log with id [ %d ] not found'), $id);
            }
            $id = $log->getRequestId();
        }

        $output->writeln($replayer->replayWithCrawler($id));
    }
}
