<?php

namespace DeskPRO\Bundle\DevBundle\Command\MassLoader;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ManyTicketMessagesCommand.
 */
class TicketMessagesCommand extends AbstractLoadDataCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:load-data:ticket-messages');
        $this->setDescription('Test ticket load performance with a lot of replies');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->clearSection('tickets');
        $this->iterate('Create %s ticket batches', 1, 'loadTicketBatch', [
            'ticketsBatchCount'  => 100,
            'messagesBatchCount' => 1000,
        ]);
        $output->writeln('');
        $output->writeln('Done.');
    }
}
