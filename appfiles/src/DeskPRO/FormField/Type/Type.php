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
 * A FormField type is a custom field that DeskPRO knows how to handle. This ties together the
 * low-level Form system (from Orb) that knows how to handle HTML forms and validationa and such, and the actual
 * logic/processing/etc that DeskPRO needs to do to handle them:
 *
 * - Creating the Form objects
 * - Rendering data
 */
abstract class AbstractType
{
	/**
	 * The form field definition
	 * @var Application\CoreBundle\Entity\FormField
	 */
	protected $form_field;

	public function __construct(FormField $form_field = null)
	{
		$this->form_field = $form_field;
	}


	/**
	 * Get the Orb\Form\Field object for this field type.
	 */
	abstract public function getField(FormFieldData $form_field_data = null);


	/**
	 * Render the field to HTML for use in a web page.
	 */
	abstract public function renderHtml(FormFieldData $form_field_data = null);


	/**
	 * Render the field
	 */
	abstract public function renderText(FormFieldData $form_field_data = null);
}