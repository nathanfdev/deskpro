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
 * A FormField type is a custom field that DeskPRO knows how to handle. This ties together the
 * low-level Form system (from Orb) that knows how to handle HTML forms and validation and such, and the actual
 * logic/processing/etc that DeskPRO needs to do to handle them:
 *
 * - Creating the Form objects
 * - Rendering data
 */
abstract class AbstractFieldHandler implements \Orb\Form\Transformer\TransformerInterface
{
	/**
	 * The form field definition
	 * @var Application\CoreBundle\Entity\FormField
	 */
	protected $fielddef;

	public function __construct(Entity\FormField $fielddef = null)
	{
		$this->fielddef = $form_field;
	}



	/**
	 * Get the standard name or ID for this element in an HTML form.
	 *
	 * @return string
	 */
	public function getFormFieldName()
	{
		return 'field_' . $this->fielddef['id'];
	}

	

	/**
	 * Transforms data stored into form data
	 *
	 * @param  mixed $value     The user input
	 * @return mixed
	 */
	public function transformStoredToForm($value)
	{
		return $value['value'];
	}



	/**
	 * Transforms data stored into data we can put into a form.
	 *
	 * @param  mixed $value     The stored data
	 * @return mixed            The original form data
	 */
	public function transformFormToStored($value)
	{
		return array('value' => $value);
	}



	/**
	 * Get the Orb\Form\Field object for this field type.
	 */
	abstract public function getFormField();


	/**
	 * Render the field to HTML for use in a web page.
	 */
	abstract public function renderHtml(Entity\FormFieldData $form_field_data = null);


	/**
	 * Render the field
	 */
	abstract public function renderText(Entity\FormFieldData $form_field_data = null);
}