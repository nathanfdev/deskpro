<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Form
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DeskPRO\Form\FieldHandler;

use \Application\CoreBundle\Entity;

/**
 * Text field
 */
class Text extends AbstractFieldHandler
{
	/**
	 * @return Orb\Form\Field\Text
	 */
	public function getFormField()
	{
		$options = array();
		if ($this->fielddef['options']['min_length']) {
			$options['min_length'] = $this->fielddef['options']['min_length'];
		}
		if ($this->fielddef['options']['max_length']) {
			$options['max_length'] = $this->fielddef['options']['max_length'];
		}

		$options['name'] = $this->getFormFieldName();

		$field = new \Orb\Form\Field\Text($options);
		$field->addTransformer($this);

		return $field;
	}


	
	/**
	 * Render the field
	 */
	public function renderText(Entity\FormFieldData $form_field_data = null)
	{
		if (!$form_field_data) {
			return '';
		}

		$value = '';
		if (isset($form_field_data['data']['value'])) {
			$value = $form_field_data['data']['value'];
		}

		return $value;
	}
}