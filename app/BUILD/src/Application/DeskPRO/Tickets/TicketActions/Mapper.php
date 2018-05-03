<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Orb\Util\Strings;

/**
 * Maps a standard action name to an action class.
 */
class Mapper
{
    /** @var array */
    protected $map = [];

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
        }

        $action_class   = $class.'Action';
        $modifier_class = $class.'Modifier';
        if (class_exists($action_class)) {
            return $action_class;
        } elseif (class_exists($modifier_class)) {
            return $modifier_class;
        }

        return;
    }
}
