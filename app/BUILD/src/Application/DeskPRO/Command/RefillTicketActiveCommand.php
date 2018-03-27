<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefillTicketActiveCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    /** @var bool */
    protected $set_verbose = false;
    /** @var bool */
    protected $ignore_interval = false;

    protected function configure()
    {
        $this->setName('dp:refill-ticket-active');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $time_start = microtime(true);
        App::getEntityRepository('DeskPRO:Ticket')->fillSearchTable();
        $time_end = microtime(true);

        $output->writeln(sprintf('Done in %.4f seconds', $time_end - $time_start));
    }
}
