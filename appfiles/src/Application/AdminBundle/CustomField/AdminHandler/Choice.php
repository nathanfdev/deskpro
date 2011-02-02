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

use \Application\DeskPRO\Entity;

use \Application\DeskPRO\App;

use \Orb\Util\Strings;
use \Orb\Util\Arrays;

/**
 * Handles editing and creating single-select field definitions
 *
 * Choices are made up of all null children, and optionally a Text
 * for "other".
 */
class Choice extends AbstractAdminHandler
{
	/**
	 * Return an array of fields we need to add to the form.
	 *
	 * @return array
	 */
	protected function buildRequiredFormFields()
	{
		$fields = array();

		$f = new \Orb\Form\Field\Textarea(array(
			'name' => 'choices',
		));

		// The current choices are those set in the fielddef
		$val = array();
		foreach ($this->custom_def['children'] as $child) {
			if ($child['handler_class']) continue; // would be "other"
			$val[] = $child['title'];
		}
		$f->setData(implode("\n", $val));

		$fields[] = $f;

		return $fields;
	}


	/**
	 * Save options for the current field.
	 *
	 * @param Orb\Form\Field\FieldGroup $form This is the form fragment for this type
	 */
	protected function handleSave(\Orb\Form\Field\FieldGroup $formgroup)
	{
		$new = array();
		$remove = array();
		$have = array();

		$from_form = explode("\n", Strings::standardEol($formgroup['choices']->getData()));
		$from_form = Arrays::removeEmptyString($from_form);

		foreach ($this->custom_def['field_children'] as $child) {
			if ($child['handler_class']) continue; // would be "other"

			if (!in_array($child['title'], $from_form)) {
				$remove[] = $child;
			} else {
				$have[] = $child['title'];
			}
		}

		foreach ($from_form as $title) {
			if (!in_array($title, $have)) {
				$new[] = $title;
			}
		}

		if ($new) {
			foreach ($new as $title) {
				$child = $this->custom_def->createChild();
				$child['title'] = $title;

				$this->custom_def->addChild($child);
			}
		}

		if ($remove) {
			foreach ($remove as $child) {
				$this->removeChild($child);
			}
		}
	}
}