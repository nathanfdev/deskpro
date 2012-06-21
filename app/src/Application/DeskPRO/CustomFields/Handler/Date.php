<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage Form
 */

namespace Application\DeskPRO\CustomFields\Handler;

use Application\DeskPRO\Entity;
use Application\DeskPRO\App;


/**
 * Handles the date field
 */
class Date extends Text
{
	public function getFormField(array $data = null)
	{
		$setData = null;
		if ($data AND !empty($data['value'])) {
			$setData = $data['value'];
		}

		$field = App::getFormFactory()->createNamedBuilder('text', $this->getFormFieldName(), $setData, array(
			//'widget' => 'text',
			//'input' => 'timestamp',
			//'format' => 3,
			'required' => false
		));

		return $field;
	}

	public function validateFormData(array $form_data, $context = self::CONTEXT_USER)
	{
		$data = isset($form_data[$this->getFormFieldName()]) ? $form_data[$this->getFormFieldName()] : '';

		if (!is_scalar($data)) {
			return $this->makeErrorArray(array('invalid_input'));
		}

		#------------------------------
		# Validate options
		#------------------------------

		$opt_prefix = '';
		if ($context == self::CONTEXT_AGENT) {
			$opt_prefix = 'agent_';
		}

		$options = array();
		foreach (array('required', 'min_length', 'max_length', 'regex') as $k) {
			$options[$k] = $this->field_def->getOption($opt_prefix . $k);
		}

		if ($options['required']) {
			if (!$data) {
				return $this->makeErrorArray(array('min_length'));
			}

			// Make sure its a valid date
			// todo
		}

		return array();
	}
}
