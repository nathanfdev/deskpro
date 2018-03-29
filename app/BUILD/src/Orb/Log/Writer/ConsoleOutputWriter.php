<?php

/**
 * Orb.
 */

namespace Orb\Log\Writer;

use Orb\Log\LogItem;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Writes to the symfony Output object.
 */
class ConsoleOutputWriter extends AbstractWriter
{
    /**
     * @var Symfony\Component\Console\Output\OutputInterface
     */
    protected $output = null;

    /**
     * @var Symfony\Component\Console\Output\OutputInterface
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;

        $this->addFilter(new \Orb\Log\Filter\ConsoleOutputFormatter());
    }

    /**
     * Write a message to the log.
     */
    public function _write(LogItem $log_item)
    {
        $this->output->writeln($log_item[LogItem::MESSAGE_LINE]);
    }
}
