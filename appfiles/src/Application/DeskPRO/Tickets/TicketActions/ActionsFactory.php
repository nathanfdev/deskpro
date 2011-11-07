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

use Application\DeskPRO\App;
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
		$name_id = null;
		$m = null;
		if (preg_match('#^(.*?)\[(.*?)\]$#', $name, $m)) {
			$name = $m[1];
			$name_id = $m[2];
		}

		$options = array();
		switch ($name) {
			case 'agent':
				$options['agent'] = $value['agent'];
				break;
			case 'agent_team':
				$options['agent_team'] = $value['agent_team'];
				break;
			case 'category':
				$options['category'] = $value['category'];
				break;
			case 'department':
				$options['department'] = $value['department'];
				break;
			case 'product':
				$options['product'] = $value['product'];
				break;
			case 'flag':
				$options['flag'] = $value['flag'];
				break;
			case 'priority':
				$options['priority'] = $value['priority'];
				break;
			case 'urgency':
				$options['num'] = $value['num'];
				break;
			case 'urgency_set':
				$options['num'] = $value['num'];
				break;
			case 'workflow':
				$options['workflow'] = $value['workflow'];
				break;
			case 'status':
				$options['status'] = $value['status'];
				break;
			case 'add_labels':
				$options['add_labels'] = array();
				if (!empty($value['labels'])) $options['add_labels'] = Strings::explodeTrim(',', $value['labels']);
				break;
			case 'remove_labels':
				$options['remove_labels'] = array();
				if (!empty($value['labels'])) $options['remove_labels'] = Strings::explodeTrim(',', $value['labels']);
				break;
			case 'reply':
				$options['reply_text'] = $value['reply_text'];
				$options['attach_ids'] = !empty($value['attach_ids']) && is_array($value['attach_ids']) ? $value['attach_ids'] : array();
				break;
			case 'add_participants':
				$options['add_participants'] = !empty($value['add_participants']) && is_array($value['add_participants']) ? $value['add_participants'] : array();
				break;
			case 'remove_participants':
				$options['remove_participants'] = !empty($value['remove_participants']) && is_array($value['remove_participants']) ? $value['remove_participantsq'] : array();
				break;
			case 'ticket_field':
				$field_manager = App::getSystemService('ticket_fields_manager');
				$field = $field_manager->getFieldFromId($name_id);

				$options['field_manager'] = $field_manager;
				$options['field_def'] = $field;
				$options['set_value'] = $value;
				break;
			case 'people_field':
				$field_manager = App::getSystemService('person_fields_manager');
				$field = $field_manager->getFieldFromId($name_id);

				$options['field_manager'] = $field_manager;
				$options['field_def'] = $field;
				$options['set_value'] = $value;
				break;
		}

		return $this->create($name, $options);
	}

	public function createFromInfo(array $action_info)
	{
		return $this->createFromForm($action_info['type'], $action_info['options']);
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
