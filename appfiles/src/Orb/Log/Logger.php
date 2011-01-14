<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Log
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Log;



/**
 * A logger class.
 *
 * Implements standard logging of message, priority and time. But also extable
 * through custom LogItem class, writers and filters to support additional
 * fields for enhanced information tracking.
 */
class Logger
{
	/**@#+ Standard log levels */
	const EMERG   = 0;
    const ALERT   = 1;
    const CRIT    = 2;
    const ERR     = 3;
    const WARN    = 4;
    const NOTICE  = 5;
    const INFO    = 6;
    const DEBUG   = 7;
	/**@#-*/

	/**
	 * Priority number => name
     * @var array
     */
    protected $_priorities = array(
		self::EMERG    => 'EMERG',
		self::ALERT    => 'ALERT',
		self::CRIT     => 'CRIT',
		self::ERR      => 'ERR',
		self::WARN     => 'WARN',
		self::NOTICE   => 'NOTICE',
		self::INFO     => 'INFO',
		self::DEBUG    => 'DEBUG'
	);

	/**
	 * Main filter chain that will apply to all writers
	 * @var Orb\Log\Writer\ChainWriter
	 */
	protected $_writer_chain = null;

	/**
	 * A session name
	 * @var string
	 */
	protected $_session_name = null;


	
	public function __construct()
	{
		$this->_writer_chain = new Writer\WriterChain();
	}
	
	
	/**
	 * Add a priroty
	 *
	 * @param string $name
	 * @param int    $priority
	 * @return Logger
	 */
	public function addPriority($name, $priority)
	{
		$name = strtoupper($name);

		if (isset($this->_priorities[$priority])) {
			throw new Exception\InvalidArgumentException('Priority already exists');
		}

		$this->_priorities[$priority] = $name;
		return $this;
	}


	
	/**
	 * Add a filter to be applied to every item.
	 *
	 * A filter must return the event object (modified or not), or null if the event
	 * should not be logged. So in this way filters act dually to transform or actually
	 * filter out items.
	 *
	 * @param \Zend\Filter\Filter $filter
	 */
	public function addFilter(\Orb\Filter\FilterInterface $filter)
	{
		$this->_writer_chain->addFilter($filter);
	}


	
	/**
	 * Add a new writer to this logger.
	 * 
	 * @param \Orb\Log\Writer\AbstractWriter $writer
	 */
	public function addWriter(\Orb\Log\Writer\AbstractWriter $writer)
	{
		$this->_writer_chain->addWriter($writer);
	}

	

	/**
	 * Some writers are able to use a sesson name or ID to group a number of related
	 * log events together. For example, to log the process through a single execution.
	 * 
	 * @param string $session_name
	 */
	public function setSessionName($session_name)
	{
		$this->_session_name = $session_name;
	}

	

	/**
	 * Log a new message
	 *
	 * @param string $message
	 * @param int $priority
	 * @param array $info
	 */
	public function log($message, $priority, array $info = array())
	{
		$info[LogItem::MESSAGE] = $message;
		$info[LogItem::PRIORITY] = $priority;
		$info[LogItem::PRIORITY_NAME] = $this->_priorities[$priority];

		$log_item = $this->createLogInfoObject($info);
		$this->logItem($log_item);
	}

	

	/**
	 * @param array $info
	 * @return LogItem
	 */
	public function createLogInfoObject(array $info)
	{
		$log_item = new LogItem($info);
		return $log_item;
	}

	

	/**
	 * Write a log item
	 * @param LogItem $log_item
	 */
	public function logItem(LogItem $log_item)
	{
		if ($this->_session_name AND !$log_item[LogItem::SESSION_NAME]) {
			$log_item[LogItem::SESSION_NAME] = $this->_session_name;
		}

		$this->_writer_chain->write($log_item);
	}
}
