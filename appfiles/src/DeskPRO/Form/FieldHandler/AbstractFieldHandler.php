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
	const CONTEXT_HTML = 'html';
	const CONTEXT_TEXT = 'text';
	
	/**
	 * The form field definition
	 * @var Application\CoreBundle\Entity\FormField
	 */
	protected $fielddef;

	public function __construct(Entity\FormField $fielddef = null)
	{
		$this->fielddef = $fielddef;
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
	 * Transforms stored data to data the form controls can use
	 *
	 * @param  mixed $value     The stored data
	 * @return mixed
	 */
	public function transformStoredToForm($value)
	{
		return $value['value'];
	}



	/**
	 * Transforms form data into data we can store.
	 *
	 * If null, it signifies no value. Sometimes empty values can be significant,
	 * in which case the we'd still store an "empty" value in the database.
	 * But if you return null, no such record will be stored at all.
	 *
	 * @param  mixed $value     The form data
	 * @return mixed            The data we can store
	 */
	public function transformFormToStored($value)
	{
		if (!$value) {
			return null;
		}

		return array('value' => $value);
	}



	/**
	 * Get the Orb\Form\Field object for this field type.
	 */
	abstract public function getFormField();

	

	/**
	 * Render the field to HTML for use in a web page.
	 */
	public function renderHtml(Entity\FormFieldData $form_field_data = null)
	{
		if (!$form_field_data) {
			return '';
		}

		return htmlspecialchars($this->renderText($form_field_data));
	}



	/**
	 * Render the field
	 */
	abstract public function renderText(Entity\FormFieldData $form_field_data = null);


	
	/**
	 * Render a field in a given context. This is just a strategy for calling other renderX
	 * methods.
	 *
	 * @param string $context
	 * @param Entity\FormFieldData $form_field_data
	 * @return mixed
	 */
	public function renderContext($context, Entity\FormFieldData $form_field_data = null)
	{
		switch ($context) {
			case self::CONTEXT_HTML:
				$method = 'renderHtml';
				break;

			case self::CONTEXT_TEXT:
				$method = 'renderText';
				break;

			default:
				throw new \InvalidArgumentException("Unknow context `$context`");
		}

		return $this->$method($form_field_data);
	}



	/**
	 * Apply the transformed value to a field_data object.
	 *
	 * This should be called within a transaction, because child values may be added and persisted.
	 */
	public function setValueOnData(Entity\FormFieldData $field_data, $value)
	{
		$field_data['data'] = $value;
	}
}