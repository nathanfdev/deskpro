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

namespace Application\AdminBundle\Form\CustomField\Model;

use Orb\Util\Strings;

class ChoiceField extends CustomFieldAbstract
{
	public $multiple = false;
	public $expanded = false;

	// Will be id=>choices on load,
	// On form submit will be new:label or exist:id:label
	public $choices = array();

	protected function init()
	{
		if ($this->_field->getOption('multiple')) {
			$this->multiple = true;
		}
		if ($this->_field->getOption('expanded')) {
			$this->expanded = true;
		}

		if (!$this->isNewField()) {
			foreach ($this->_field->children as $child) {
				$this->choices[$child->id] = $child->title;
			}
		}
	}

	protected function setFieldProperties()
	{
		$field = $this->_field;

		$field->setOption('multiple', $this->multiple);
		$field->setOption('expanded', $this->expanded);
	}

	protected function saveAdditional()
	{
		// Parse choices array
		$new_choices = array();
		$exist_choices = array();
		$order = 0;

		foreach ($this->choices as $c) {
			$match = null;
			if (preg_match('#^new:(?P<label>.*?)$#', $c, $match)) {
				$new_choices[] = array($match['label'], $order += 10);
			} elseif (preg_match('#^exist:(?P<id>[0-9]+):(?P<label>.*?)$#', $c, $match)) {
				$exist_choices[$match['id']] = array($match['label'], $order += 10);
			}
		}

		// Delete/update fields we have
		if (!$this->isNewField()) {
			foreach ($this->_field->children as $k => $child) {
				// Delete
				if (!isset($exist_choices[$child->id])) {
					$this->_em->remove($child);
					$this->_field->children->remove($k);

				// Update
				} else {
					$child->title = $exist_choices[$child->id][0];
					$child->display_order = $exist_choices[$child->id][1];

					$this->_em->persist($child);
				}
			}
		}

		// Add new fields
		foreach ($new_choices as $new_choice) {
			$child = $this->_field->createChild();
			$child->title = $new_choice[0];
			$child->display_order = $new_choice[1];

			$this->_em->persist($child);
		}
	}
}
