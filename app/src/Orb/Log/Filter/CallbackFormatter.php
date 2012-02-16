<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Log
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Log\Filter;

use Orb\Log\LogItem;
use Orb\Log\Logger;


/**
 * This wraps up a function callback
 */
class CallbackFormatter extends \Orb\Filter\AbstractFilter
{
	protected $callback;

	public function __construct($callback)
	{
		$this->callback = $callback;
	}

	public function filter($log_item)
	{
		if (!$log_item) return null;

		$log_item = call_user_func($this->callback, $log_item);

		return $log_item;
	}
}
