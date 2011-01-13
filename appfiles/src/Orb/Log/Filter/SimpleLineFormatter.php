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
 * This formats the 'line' value of an item with other properties. This gives
 * a single, suitable value that can be written to a flat file for example.
 */
class SimpleLineFormatter extends \Orb\Filter\AbstractFilter
{
	const DEFAULT_FORMAT = '%datetime% %priority_name% (%priority%): %message%';
	const DEFAULT_TIME_FORMAT = 'c';

    /**
     * @var string
     */
	protected $_format;

	protected $_time_format;

	public function __construct($format = self::DEFAULT_FORMAT, $time_format = self::DEFAULT_TIME_FORMAT)
	{
		$this->_format = $format;
		$this->_time_format = $time_format;
	}

	public function filter(LogItem $log_item)
	{
		if (!$log_item) return null;

		$message = $log_item[LogItem::MESSAGE];
		$message_line = $message;

		foreach ($log_item as $k => $v) {
			if ($v instanceof \DateTime) {
				$v = $v->format($this->_time_format);
			}

			$message_line = str_replace("%$k%", $v, $message);
		}

		$log_item[LogItem::MESSAGE_LINE] = $message_line;

		return $log_item;
	}
}