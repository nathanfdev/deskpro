<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Log
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Log\Writer;

use Orb\Log\LogItem;
use Symfony\Component\Console\Output\Output;

/**
 * Writes to the symfony Output object
 */
class ConsoleOutputWriter extends AbstractWriter
{
	/**
	 * @var Symfony\Component\Console\Output\Output
	 */
	protected $output = null;

	/**
	 * @var Symfony\Component\Console\Output\Output
	 */
	public function __construct(Output $output)
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