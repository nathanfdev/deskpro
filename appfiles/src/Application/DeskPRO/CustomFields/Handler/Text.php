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
 * Handles the text field
 */
class Text extends HandlerAbstract
{
	public function getFormField(array $data = null)
	{
		$field = new \Symfony\Component\Form\TextField($this->getFormFieldName(), array('required' => false));

		if ($data AND !empty($data['value'])) {
			$field->setData($data['value']);
		}

		return $field;
	}

	function getDataFromForm(array $form_data)
	{
		$name = $this->getFormFieldName();

		$value = null;
		if (!empty($form_data[$name])) {
			$value = $form_data[$name];
		}

		return array(
			array($this->field_def['id'], 'input', $value)
		);
	}

	public function getSearchCapabilities()
	{
		return array('is', 'not', 'contains', 'notcontains');
	}

	public function getSearchType()
	{
		return 'input';
	}
}