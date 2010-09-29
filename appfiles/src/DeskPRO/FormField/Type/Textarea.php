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
 * Textarea feild
 */
class Textarea extends Text
{
	/**
	 * Get the Orb\Form\Field object for this field type.
	 */
	public function getField(FormFieldData $form_field_data = null)
	{

	}
}