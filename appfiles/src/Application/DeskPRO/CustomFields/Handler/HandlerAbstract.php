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
 * A custom field handler knows how to render an HTML form field as well as
 * a render a readable value.
 */
abstract class HandlerAbstract
{
	const CONTEXT_HTML = 'html';
	const CONTEXT_TEXT = 'text';

	/**
	 * The form field definition
	 * @var Entity\CustomDefAbstract
	 */
	protected $field_def;

	public function __construct(Entity\CustomDefAbstract $field_def = null)
	{
		$this->field_def = $field_def;
	}



	/**
	 * Get the standard name/ID for this element in an HTML form.
	 *
	 * @return string
	 */
	public function getFormFieldName()
	{
		return 'field_' . $this->field_def['id'];
	}



	/**
	 * Render the field to HTML for use in a web page.
	 */
	public function renderHtml(array $data)
	{
		return htmlspecialchars($this->renderText($data));
	}



	/**
	 * Render the field
	 */
	public function renderText(array $data)
	{
		// By default, we only know how to render single fields
		// A multi-value field will have to have its own logic.
		if (!empty($data['value'])) {
			$txt = $data['value'];
		} else {
			$txt = '';
		}

		return $txt;
	}



	/**
	 * Render a field in a given context. This is just a strategy for calling other renderX
	 * methods.
	 *
	 * $data is a data structure `array(value=>..., children=>array(...))` as returned
	 * from `Application\DeskPRO\CustomFields\Util::createDataHierarchy()`
	 *
	 * @param string $context
	 * @param array $data
	 * @return mixed
	 */
	public function renderContext($context, array $data)
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

		return $this->$method($data);
	}



	/**
	 * Get the form field
	 *
	 * @return Symfony\Component\Form\Field
	 */
	abstract function getFormField(array $data = null);



	/**
	 * Get data from a posted form that we'll store in the database.
	 *
	 * This must return an array of array(field_id, type, value)
	 * If no value is set, then use null.
	 *
	 * @return array
	 */
	abstract function getDataFromForm(array $form_data);
}