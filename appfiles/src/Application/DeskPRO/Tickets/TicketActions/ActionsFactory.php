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

use Application\DeskPRO\Tickets\TicketActions\ActionInterface;
use Application\DeskPRO\Tickets\TicketActions\Mapper;
use Application\DeskPRO\Tickets\TicketActions\CollectionModifierInterface;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\Person;

use Orb\Util\Util;

/**
 * Creates action objects
 */
class ActionsFactory
{
	protected $global_options = array();

	public function addGlobalOption($name, $value)
	{
		$this->global_options[$name] = $value;
	}

	public function createFromInfo(array $action_info)
	{
		return $this->create($action_info['type'], $action_info['options']);
	}

	public function create($name, array $options)
	{
		$class = str_replace('_', '-', $name);
		$class = ucfirst(Strings::dashToCamelCase($class));

		$options = array_merge($this->global_options, $options);

		$action_class = $class . 'Action';
		$modifier_class = $class . 'Modifier';
		if (is_class($action_class)) {
			return $this->createActionObject($action_class, $options);
		} elseif (is_class($modifier_class)) {
			return $this->createModifierObject($action_class, $options);
		}

		return null;
	}

	public function createActionObject($action_class, array $options)
	{
		$method_refl = new \ReflectionMethod($action_class, '__construct');
		$args = Util::getFunctionParamsFromArray($method_refl, $options);

		$obj = Util::callUserConstructorArray($action_class, $args);
		return $obj;
	}

	public function createModifierObject($action_class, array $options)
	{
		$method_refl = new \ReflectionMethod($action_class, '__construct');
		$args = Util::getFunctionParamsFromArray($method_refl, $options);

		$obj = Util::callUserConstructorArray($action_class, $args);
		return $obj;
	}
}