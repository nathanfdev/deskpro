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
 * @category ORM
 */

namespace Application\DeskPRO\Labels;

use Application\DeskPRO\App;

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

		$type_name = strtolower(\Orb\Util\Util::getBaseClassname($this->entity)) . 's';
		App::getDb()->replace('label_defs', array(
			'label_type' => $type_name,
			'label' => $label
		));

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
			if ($label) {
				$labels[] = $label;
			}
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
