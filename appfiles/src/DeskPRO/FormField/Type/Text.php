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

namespace DeskPRO\FormField\Type;

use \Application\CoreBundle\Entity\FormField;
use \Application\CoreBundle\Entity\FormFieldData;

/**
 * Text field
 */
class Text extends AbstractType
{
	/**
	 * Get the Orb\Form\Field object for this field type.
	 */
	public function getField(FormFieldData $form_field_data = null)
	{

	}



	/**
	 * Render the field to HTML for use in a web page.
	 */
	public function renderHtml(FormFieldData $form_field_data)
	{
		return htmlspecialchars($this->renderText($form_field_data));
	}



	/**
	 * Render the field
	 */
	public function renderText(FormFieldData $form_field_data)
	{
		$value = '';
		if (isset($form_field_data['data']['value'])) {
			$value = $form_field_data['data']['value'];
		}

		return $value;
	}
}