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

namespace DeskPRO\Form\FieldHandler;

use \Application\CoreBundle\Entity;

/**
 * Textarea feild
 */
class Textarea extends Text
{
	/**
	 * @return Orb\Form\Field\Textarea
	 */
	public function getFormField(Entity\FormFieldData $form_field_data = null)
	{
		$options = array();
		if ($this->fielddef['options']['field_options']) {
			$options = $this->fielddef['options']['field_options'];
		}

		$options['name'] = $this->getFormFieldName();

		$field = new \Orb\Form\Field\Textarea($options);

		if ($form_field_data) {
			$field->setData($form_field_data['data']['value']);
		}

		return $field;
	}
}