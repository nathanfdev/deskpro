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
 * A specific log event.
 */
class LogItem implements \IteratorAggregate, \ArrayAccess
{
	/**@#+ Standard fields **/
	const PRIORITY       = 'priority';
	const PRIORITY_NAME  = 'priority_name';
	const MESSAGE        = 'message';
	const MESSAGE_LINE   = 'message_line';
	const DATETIME       = 'datetime';
	const SESSION_NAME   = 'session_name';
	/**@#-*/

	protected $_standard_fields = array(
		self::PRIORITY,
		self::PRIORITY_NAME,
		self::MESSAGE,
		self::MESSAGE_LINE,
		self::DATETIME,
		self::SESSION_NAME
	);

	protected $info = array();

	public function __construct(array $info)
	{
		if ($info) {
			$this->info = $info;
		}

		if (!isset($this[self::DATETIME])) {
			$this[self::DATETIME] = new \DateTime();
		}
		if (!isset($this[self::PRIORITY])) {
			$this[self::PRIORITY] = Logger::INFO;
		}
		if (!isset($this[self::PRIORITY_NAME])) {
			$this[self::PRIORITY_NAME] = $this[self::PRIORITY];
		}
		if (!isset($this[self::MESSAGE])) {
			$this[self::MESSAGE] = '';
		}
		if (!isset($this[self::MESSAGE_LINE])) {
			$this[self::MESSAGE_LINE] = $this[self::MESSAGE];
		}
		if (!isset($this[self::SESSION_NAME])) {
			$this[self::SESSION_NAME] = null;
		}

		$this->init();
	}


	
	/**
	 * Empty init method for children
	 */
	protected function init()
	{

	}



	/**
	 * Get the numeric priority
	 * @return int
	 */
	public function getPriority()
	{
		return $this[self::PRIORITY];
	}


	
	/**
	 * Get the priority name
	 * @return string
	 */
	public function getPriorityName()
	{
		return $this[self::PRIORITY_NAME];
	}



	/**
	 * Get the log message
	 * @return string
	 */
	public function getMessage()
	{
		return $this[self::MESSAGE];
	}



	/**
	 * Get the message line. This may simply be the message, but it may have been
	 * transformed with other information. Generally a transformer will leave message
	 * original and just transform this. For example, to log to a file you may
	 * want to include priority name, timestamp etc in the line.
	 *
	 * @return string
	 */
	public function getMessageLine()
	{
		return $this[self::MESSAGE_LINE];
	}

	

	/**
	 * Get the time of the event
	 * 
	 * @return DateTime
	 */
	public function getDatetime()
	{
		return $this[self::DATETIME];
	}

	

	/**
	 * Get the session name
	 *
	 * @return string
	 */
	public function getSessionName()
	{
		return $this[self::SESSION_NAME];
	}


	
	/**
	 * Get extra, non-standard event data.
	 */
	public function getExtra()
	{
		$ret = array();

		foreach ($this->info as $k => $v) {
			if (!in_array($k, $this->_standard_fields)) {
				$ret[$k] = $v;
			}
		}

		return $ret;
	}




	/**@#+ ArrayAccess implementation */
	public function offsetSet($offset, $value)
	{
		$this->info[$offset] = $value;
	}
	public function offsetExists($offset)
	{
		return isset($this->info[$offset]);
	}
	public function offsetUnset($offset)
	{
		unset($this->info[$offset]);
	}
	public function offsetGet($offset)
	{
		return $this->info[$offset];
	}
	/**@#-*/

	public function getIterator()
	{
		return new \ArrayIterator($this->info);
	}
}