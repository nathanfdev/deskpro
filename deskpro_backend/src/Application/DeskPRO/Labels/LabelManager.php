<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category ORM
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Labels;

class LabelManager
{
	protected $entity;
	protected $label_entity_name;
	protected $labels_property;

	public function __construct($entity, $label_entity_name, $labels_property = 'labels')
	{
		$this->entity = $entity;
		$this->label_entity_name = $label_entity_name;
		$this->label_entity_classname = str_replace('DeskPRO:', 'Application\\DeskPRO\\Entity\\', $this->label_entity_name);
		$this->labels_property = $labels_property;
	}

	public function createLabelEntity()
	{
		$label = new $this->label_entity_classname;
		return $label;
	}

	public function removeLabel($label)
	{
		$label = self::normalizeLabel($label);

		foreach ($this->entity[$this->labels_property] as $k => $labelobj) {
			if ($labelobj['label'] == $label) {
				$this->entity[$this->labels_property]->remove($k);
				return $labelobj;
			}
		}

		return null;
	}

	public function removeLabels(array $labels)
	{
		foreach ($labels as $label) {
			$this->removeLabel($label);
		}
	}

	public function addLabel($label)
	{
		$label = self::normalizeLabel($label);

		foreach ($this->entity[$this->labels_property] as $labelobj) {
			if ($labelobj['label'] == $label) {
				return $labelobj;
			}
		}

		$labelobj = $this->createLabelEntity();
		$labelobj['label'] = $label;
		$this->entity->addLabel($labelobj);

		return $labelobj;
	}

	public function addLabels(array $labels)
	{
		foreach ($labels as $label) {
			$this->addLabel($label);
		}
	}

	public function getLabelsArray()
	{
		$labels = array();
		foreach ($this->entity[$this->labels_property] as $label) {
			$labels[] = $label['label'];
		}

		return $labels;
	}

	public function hasLabel($label)
	{
		$label = self::normalizeLabel($label);

		foreach ($this->entity[$this->labels_property] as $label) {
			if ($label['label'] == $label) {
				return true;
			}
		}

		return false;
	}

	public function setLabelsArray(array $labels)
	{
		$labels_raw = $labels;
		$labels = array();

		foreach ($labels_raw as $label) {
			$label = self::normalizeLabel($label);
			$labels[] = $label;
		}

		$existing_labels = $this->getLabelsArray();
		$added = array_diff($labels, $existing_labels);
		$removed = array_diff($existing_labels, $labels);

		foreach ($added as $added_label) {
			$this->addLabel($added_label);
		}
		foreach ($removed as $removed_label) {
			$this->removeLabel($removed_label);
		}
	}

	public static function normalizeLabel($label)
	{
		$label = strtolower(trim($label));

		return $label;
	}
}
