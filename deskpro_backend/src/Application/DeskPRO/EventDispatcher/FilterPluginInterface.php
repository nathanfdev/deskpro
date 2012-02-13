<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage EmailGateway
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;

/**
 * An event that is filterable is able to tell if a Plugin should be fired based
 * on whatever criteria.
 */
interface FilterPluginInterface
{
	/**
	 * @param Plugin $plugins
	 * @return bool
	 */
	public function filterPlugins($plugin);
}