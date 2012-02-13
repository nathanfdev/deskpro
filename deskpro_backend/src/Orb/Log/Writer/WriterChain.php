<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Log
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Log\Writer;
use \Orb\Log\LogItem;



/**
 * A writer that calls other writers
 */
class WriterChain extends AbstractWriter implements \Countable, \IteratorAggregate
{
	/**
	 * An array of writers
	 * @var array
	 */
	protected $_writers = array();


	
	/**
	 * Add a new filter to the chain
	 *
	 * @param FilterInterface $writer
	 */
	public function addWriter(AbstractWriter $writer)
	{
		$this->_writers[] = $writer;
	}



	/**
	 * Get the writers currently set.
	 *
	 * @return array
	 */
	public function getWriters()
	{
		return $this->_writers;
	}


	
	/**
     * Write a log message
     *
     * @param  LogItem $event
     * @return bool
     */
    public function _write(LogItem $log_item)
	{
		foreach ($this->_writers as $writer) {
			$writer->write($log_item);
		}

		return true;
	}



    /**
     * Perform shutdown activities
     *
     * @return void
     */
    public function shutdown()
	{
		foreach ($this->_writers as $writer) {
			$writer->shutdown();
		}
	}



	/**
	 * Count how many writers there are.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->_writers);
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_writers);
	}
}