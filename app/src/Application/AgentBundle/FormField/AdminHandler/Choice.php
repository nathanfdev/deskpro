<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 */

namespace Application\AgentBundle\CustomField\AdminHandler;

use Application\DeskPRO\Entity;

use Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Handles editing and creating single-select field definitions
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
		foreach ($this->fielddef['field_children'] as $child) {
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
		$em = App::getOrm();

		$new = array();
		$remove = array();
		$have = array();

		$from_form = explode("\n", Strings::standardEol($formgroup['choices']->getData()));
		$from_form = Arrays::removeEmptyString($from_form);

		foreach ($this->fielddef['field_children'] as $child) {
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
				$child = new Entity\PersonField();
				$child['title'] = $title;
				$child['handler_class'] = 'x';

				$this->fielddef['field_children']->add($child);
				$child['parent'] = $this->fielddef;

				$em->persist($child);
			}
		}

		if ($remove) {
			foreach ($remove as $child) {
				$this->fielddef['field_children']->removeElement($child);
				$em->remove($child);
			}
		}

		// We'll save id=>title here just so we dont have to fetch
		// children collection when we want to use this field

		$choices_plain = array();
		foreach ($this->fielddef['field_children'] as $child) {
			$choices_plain[$child['id']] = $child['title'];
		}

		$this->fielddef['options'] = array('choices' => $choices_plain);
	}
}
