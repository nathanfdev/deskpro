<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\DevBundle\Command\MassLoader;

use Application\DeskPRO\Entity\Ticket;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class BaseLoadDataCommand.
 */
class AddTicketsCommand extends AbstractLoadDataCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dpdev:load-data:add-tickets')
            ->setDescription('Add tickets to the current database')
            ->addOption('batches', 'c', InputOption::VALUE_REQUIRED, 'The number of batches to insert. Default: 5', 5)
            ->addOption('batch-size', 't', InputOption::VALUE_REQUIRED, 'The size of each batch. Default: 1000', 1000)
            ->addOption('include', 'z', InputOption::VALUE_REQUIRED, 'Path to a file to include that can optionally define a post_ticket_batch function to execute code after each batch (e.g. to add some extra data to the db etc)')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $batches     = (int) $input->getOption('batches');
        $batchSize   = (int) $input->getOption('batch-size');
        $includeFile = $input->getOption('include');

        if (!file_exists($includeFile)) {
            $output->writeln("<error>File does not exist: $includeFile</error>");

            return 1;
        }

        if ($includeFile) {
            require $includeFile;
            if (!function_exists('post_ticket_batch')) {
                $output->writeln('<error>Custom include file does not define post_ticket_batch</error>');

                return 1;
            }
            $cb = 'post_ticket_batch';
        } else {
            $cb = null;
        }

        $this->iterate('Create %s ticket batches', $batches, 'loadTicketBatch', [
            'ticketsBatchCount' => $batchSize,
            'postBatchCallback' => $cb,
        ]);

        $output->writeln('');
        $output->writeln('Done.');
    }
}
