<?php

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

        if ($includeFile) {
            if (!file_exists($includeFile)) {
                $output->writeln("<error>File does not exist: $includeFile</error>");

                return 1;
            }

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
