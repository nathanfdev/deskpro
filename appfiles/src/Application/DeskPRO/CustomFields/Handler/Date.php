<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Form
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\CustomFields\Handler;

use \Application\DeskPRO\Entity;

/**
 * Handles the date field
 */
class Date extends HandlerAbstract
{
	public function getFormField(array $data = null)
	{
		$field = new \Symfony\Component\Form\DateField($this->getFormFieldName(), array(
			'widget' => 'input',
			'type' => 'timestamp',
			'format' => 'medium',
		));

		if ($data AND !empty($data['value'])) {
			$date = date('Y-m-d', $data['value']);
			$field->setData($date);
		}

		return $field;
	}

	function getDataFromForm(array $form_data)
	{
		$name = $this->getFormFieldName();

		$value = null;
		if (!empty($form_data[$name])) {
			$value = $form_data[$name];
			$value = strtotime($value);
		}

		return array(
			array($this->field_def['id'], 'input', $value)
		);
	}
}