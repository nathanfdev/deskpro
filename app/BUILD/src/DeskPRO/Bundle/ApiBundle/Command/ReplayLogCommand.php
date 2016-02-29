<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
        $this->setName('dpdev:replay-log')
            ->setDescription('Replays log already stored in DB')
            ->addArgument('request_id', InputArgument::REQUIRED, 'Id of request to replay (string)')
            ->addOption('use-id', 'i', InputOption::VALUE_NONE, 'Use integer id instead if request_id');
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
