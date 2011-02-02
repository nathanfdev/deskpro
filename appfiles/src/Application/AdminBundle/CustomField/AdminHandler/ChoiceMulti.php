<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\CustomField\AdminHandler;


/**
 * Handles editing and creating multiple-select choices
 */
class MultipleChoice extends Choice
{
	protected function handleSave(\Orb\Form\Field\FieldGroup $formgroup)
	{
		parent::handleSave($formgroup);

		$options = $this->custom_def['options'];
		$options['multiple'] = true;
		$this->custom_def = $options;
	}
}