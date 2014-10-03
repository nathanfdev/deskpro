<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Orb\Util\CheckedOptionsArray;

/**
 * Checks the value of a ticket field
 *
 * @option int field_id   The field to check
 * @option mixed value    The value. For choice, this will be multiple ints. For others, it will be a string.
 */
abstract class AbstractCheckCustomField extends AbstractTriggerTerm
{
	/**
	 * {@inheritDoc}
	 */
	protected function getOptionsDef()
	{
		$options = new CheckedOptionsArray();
		$options->addRequiredNames('field_id', 'value');
		return $options;
	}


	/**
	 * @param Ticket                   $ticket
	 * @param ExecutorContextInterface $context
	 * @return array
	 */
	abstract function getCustomDataArray(Ticket $ticket, ExecutorContextInterface $context);


	/**
	 * {@inheritDoc}
	 */
	public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
	{
		$options = $this->getTermOptions();
		$op = $this->getTermOperator();

		#------------------------------
		# Get the field value
		#------------------------------

		$custom_data_array = $this->getCustomDataArray($ticket, $context);

		$field_id = $options->get('field_id');
		$field = null;
		$field_data = null;

		foreach ($custom_data_array as $custom_data) {
			if ($custom_data->field->id == $field_id) {
				$field_data = $custom_data->input;
				$field = $custom_data->field;
				break;
			} else if ($custom_data->field->parent && $custom_data->field->parent->id == $field_id) {
				$field = $custom_data->field->parent;
				$field_data = array();
				break;
			}
		}

		if ($field && $field->getTypeName() == 'choice') {
			foreach ($custom_data_array as $custom_data) {
				if ($custom_data->field->parent and $custom_data->field->parent->id == $field_id) {
					$field_data[] = $custom_data->field->id;
				}
			}
		}

		#------------------------------
		# Check for existence
		#------------------------------

		if (!$field_data) {
			if (in_array($op, array('not', 'notcontains', 'not_regex', 'not_isset'))) return true;
			else return false;
		}

		if ($op == 'isset') {
			return true;
		} else if ($op == 'not_isset') {
			return false;
		}

		#------------------------------
		# Handle choice check
		#------------------------------

		if ($field->getTypeName() == 'choice') {
			$check_value = $options->get('value');
			$check_ids = array_fill_keys($field_data, true);
			if (!is_array($check_value)) $check_value = array($check_value);

			$has = false;
			foreach ($check_value as $v) {
				if (isset($check_ids[$v])) {
					$has = true;
					break;
				}
			}

			switch ($op) {
				case 'is':
				case 'contains':
					if ($has) {
						return true;
					}
					break;

				case 'not':
				case 'notcontains':
					if (!$has) {
						return true;
					}
			}

			return false;

		#------------------------------
		# Handle text check
		#------------------------------

		} else {
			return $this->isStringMatch($ticket, $context, TermValue::createWithValue($field_data), $options->get('value'));
		}
	}


	/**
	 * @return string
	 */
	public function getTermType()
	{
		return 'CheckTicketField' . $this->getTermOptions()->get('field_id');
	}
}