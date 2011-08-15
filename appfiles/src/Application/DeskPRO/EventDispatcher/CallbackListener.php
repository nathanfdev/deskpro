<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage EventDispatcher
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EventDispatcher;

/**
 * An object wrapper that will fire a callback when any event fires.
 * Useful when you want to pass a old-style array(obj, func) callback for a particular event.
 */
class CallbackListener
{
	protected $callback;

	public function __construct($callback)
	{
		$this->callback = $callback;
	}

	public function __invoke()
	{
		$args = func_get_args();
		return call_user_func_array($this->callback, $args);
	}

	public function __call($name, $args)
	{
		return call_user_func_array($this->callback, $args);
	}
}