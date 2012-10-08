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
class Date extends HandlerAbstract
{
	public function renderHtml($data = null, array $template_vars = array())
	{
		if ($data === null) return '';

		if (!ctype_digit($data['value'])) {
			$data['value'] = time();
		}

		$data['value'] = new \DateTime('@' . $data['value']);
		return parent::renderText($data, $template_vars);
	}

	public function renderText($data = null, array $template_vars = array())
	{
		if ($data === null) return '';

		if (!ctype_digit($data['value'])) {
			$data['value'] = time();
		}

		$data['value'] = new \DateTime('@' . $data['value']);
		return  parent::renderText($data, $template_vars);
	}

	function getDataFromForm(array $form_data)
	{
		$name = $this->getFormFieldName();

		$value = null;
		if (!empty($form_data[$name])) {
			$value = $form_data[$name];
		}

		if (!$value) {
			return array();
		}

		$date = \DateTime::createFromFormat('Y-m-d', $value, App::getCurrentPerson()->getDateTimezone());
		if (!$date) {
			return array();
		}

		$date = \Orb\Util\Dates::convertToUtcDateTime($date);

		return array(
			array($this->field_def['id'], 'value', $date->getTimestamp())
		);
	}

	public function getFormField($data = null)
	{
		$setData = null;
		if ($data AND !empty($data['value'])) {
			try {
				$date = new \DateTime('@' . $data['value']);
				$date->setTimezone(App::getCurrentPerson()->getDateTimezone());
				$setData = $date->getTimestamp();
			} catch (\Exception $e) {
				$setData = null;
			}
		}

		$field = App::getFormFactory()->createNamedBuilder('text', $this->getFormFieldName(), $setData, array(
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
		foreach (array('required') as $k) {
			$options[$k] = $this->field_def->getOption($opt_prefix . $k);
		}

		if ($options['required']) {
			if (!$data) {
				return $this->makeErrorArray(array('required'));
			}
		}

		if ($data) {
			$date = \DateTime::createFromFormat('Y-m-d', $data);
			if (!$date) {
				return $this->makeErrorArray(array('invalid_input'));
			}
		}

		return array();
	}
}
