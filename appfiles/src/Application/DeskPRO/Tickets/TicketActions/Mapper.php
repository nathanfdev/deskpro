<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Orb\Util\Strings;

/**
 * Maps a standard action name to an action class
 */
class Mapper
{
	protected $map = array();

	public function addMapName($name, $class)
	{
		$this->map[$name] = $class;
	}

	public function mapName($name)
	{
		// We have a specific name
		if (isset(self::$map[$name])) {
			$class = self::$map[$name];

		// We'll try to generate it
		} else {

			// Example:
			// agent_team
			// agent-team
			// AgentTeam
			// ActionTeamAction

			$class = str_replace('_', '-', $name);
			$class = ucfirst(Strings::dashToCamelCase($class));

			$action_class = $class . 'Action';
			$modifier_class = $class . 'Modifier';
			if (is_class($action_class)) {
				return $action_class;
			} elseif (is_class($modifier_class)) {
				return $modifier_class;
			}
		}

		return null;
	}
}