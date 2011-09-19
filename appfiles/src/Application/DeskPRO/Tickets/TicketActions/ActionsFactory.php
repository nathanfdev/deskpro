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
use Orb\Util\Strings;
use Orb\Util\Arrays;

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

	/**
	 * Create an action object from a posted form representation of an action.
	 * These are generally just an action name and a single value to represent the actions
	 * new value.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return object
	 */
	public function createFromForm($name, $value)
	{
		$options = array();
		switch ($name) {
			case 'agent':
				$options['agent'] = $value;
				break;
			case 'agent_team':
				$options['agent_team'] = $value;
				break;
			case 'category':
				$options['category'] = $value;
				break;
			case 'department':
				$options['department'] = $value;
				break;
			case 'product':
				$options['product'] = $value;
				break;
			case 'flag':
				$options['flag'] = $value;
				break;
			case 'priority':
				$options['priority'] = $value;
				break;
			case 'urgency':
				$options['num'] = $value;
				break;
			case 'urgency_set':
				$options['num'] = $value;
				break;
			case 'workflow':
				$options['workflow'] = $value;
				break;
			case 'status':
				$options['status'] = $value;
				break;
			case 'reply':
				$options['reply_text'] = $value['reply_text'];
				break;
		}

		return $this->create($name, $options);
	}

	public function createFromInfo(array $action_info)
	{
		return $this->create($action_info['type'], $action_info['options']);
	}

	public function create($name, array $options)
	{
		$class = str_replace('_', '-', $name);
		$class = ucfirst(Strings::dashToCamelCase($class));
		$class = 'Application\\DeskPRO\\Tickets\\TicketActions\\' . $class;

		$options = array_merge($this->global_options, $options);

		$action_class = $class . 'Action';
		$modifier_class = $class . 'Modifier';

		if (class_exists($action_class)) {
			return $this->createActionObject($action_class, $options);
		} elseif (class_exists($modifier_class)) {
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
