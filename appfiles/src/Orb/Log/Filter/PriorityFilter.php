<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Log
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Log\Writer;

use \Orb\Log\Logger;
use \Orb\Log\LogItem;



/**
 * This filter fitlers out log events whose priority is below a certain level.
 */
class PriorityFilter extends \Orb\Filter\AbstractFilter
{
	protected $min_level = Logger::WARN;

	public function __construct($min_level)
	{
		$this->min_level = $min_level;
	}

	public function filter($log_item)
	{
		if (!$log_item) return null;

		if ($log_item[LogItem::PRIORITY] < $this->min_level && !$log_item['ignore_priority_filter']) {
			return null;
		}

		// Remove this special flag
		if ($log_item['ignore_priority_filter']) {
			unset($log_item['ignore_priority_filter']);
		}

		return $log_item;
	}
}