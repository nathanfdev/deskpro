<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Addons
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Addon;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Plugin;

use Symfony\Component\EventDispatcher\EventDispatcher;

class PluginListenerFactory
{
	public function create($plugin_listener)
	{
		$class = $listener_class['listener_class'];
		return new $class($plugin_listener);
	}
}