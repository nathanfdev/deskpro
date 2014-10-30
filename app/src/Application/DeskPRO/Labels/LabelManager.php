<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\EntityRepository\LabelDef;
use Application\DeskPRO\ORM\EntityManager;

class LabelManager
{
	/** @var DomainObject */
	protected $entity;
	/** @var string */
	protected $label_entity_name;
	/** @var string */
	protected $labels_property;
	/** @var \Doctrine\ORM\EntityManager  */
	protected $em;

	public function __construct($entity, $label_entity_name, $labels_property = 'labels')
	{
		$this->entity = $entity;
		$this->label_entity_name = $label_entity_name;
		$this->label_entity_classname = str_replace('DeskPRO:', 'Application\\DeskPRO\\Entity\\', $this->label_entity_name);
		$this->labels_property = $labels_property;

		// todo construct in factory method, em injection
		$this->em = App::getOrm();
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
				if ($this->entity instanceof Ticket || $this->entity instanceof Person) {
					$this->entity->removeLabelByString($label);
				} else {
					$this->entity[$this->labels_property]->remove($k);
				}

				if ($this->entity instanceof Ticket && $this->entity->getTicketLogger()) {
					$this->entity->getTicketLogger()->recordMultiPropertyChanged('label_removed', $label, null);
				}

				$type_name = strtolower(\Orb\Util\Util::getBaseClassname($this->entity)) . 's';
				if ($type_name == 'chatconversations') {
					$type_name = 'chat';
				}

				/** @var LabelDef $rep */
				$rep = $this->em->getRepository('DeskPRO:LabelDef');
				if ($definition = $rep->getDefinition($type_name, $label)) {
					$rep->updateDefinitionUsages($definition);
				}
				// todo should be handled here '$this->entity[$this->labels_property]->remove($k);'
				$this->em->remove($labelobj);
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

	public function addLabel($label, $skip_corrections = false)
	{
		$label = self::normalizeLabel($label);

		if (!$skip_corrections) {
			$rep     = $this->em->getRepository('DeskPRO:LabelDef');
			$type    = $rep->getTypeByEntityName($this->label_entity_name);
			$labels  = $rep->correctLabels($type, array($label), true);
			$label   = array_pop($labels);
		}

		foreach ($this->entity[$this->labels_property] as $labelobj) {
			if ($labelobj['label'] == $label) {
				return $labelobj;
			}
		}

		$labelobj = $this->createLabelEntity();
		$labelobj['label'] = $label;
		$this->entity->addLabel($labelobj);

		$type_name = strtolower(\Orb\Util\Util::getBaseClassname($this->entity)) . 's';
		if ($type_name == 'chatconversations') {
			$type_name = 'chat';
		}

		if ($type_name == 'persons') {
			$type_name = 'people';
		}

		/** @var LabelDef $rep */
		$rep = $this->em->getRepository('DeskPRO:LabelDef');
		if ('chat_conversations' === $type_name) {
			$type_name = 'chat';
		}
		if (!$definition = $rep->getDefinition($type_name, $label)) {
			$definition = new \Application\DeskPRO\Entity\LabelDef(array(
				'label_type' => $type_name,
				'label' => $label,
				'color' => $rep->getColorForLabel($label),
			));
			$this->em->persist($definition);
			$rep->updateDefinitionUsages($definition);
			$this->em->flush();
		}

		if ($this->entity instanceof Ticket && $this->entity->getTicketLogger()) {
			$this->entity->getTicketLogger()->recordMultiPropertyChanged('label_added', null, $label);
		}

		$this->em->persist($labelobj);
		return $labelobj;
	}

	public function addLabels(array $labels)
	{
		$rep     = $this->em->getRepository('DeskPRO:LabelDef');
		$type    = $rep->getTypeByEntityName($this->label_entity_name);
		$labels  = $rep->correctLabels($type, $labels, true);

		foreach ($labels as $label) {
			$this->addLabel($label, true);
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
		$label_test = self::normalizeLabel($label);

		foreach ($this->entity[$this->labels_property] as $label) {
			if ($label['label'] == $label_test) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param null $labels
	 */
	public function setLabelsArray($labels = null)
	{
		// back compatibility
		if (!$labels) {
			$labels = array();
		} elseif (!is_array($labels)) {
			$labels = explode(',', $labels);
		}

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

		/** @var LabelDef $rep */
		$rep     = $this->em->getRepository('DeskPRO:LabelDef');
		$type    = $rep->getTypeByEntityName($this->label_entity_name);
		$perm_name = sprintf('labels.%s.agent_can_create', $type);

		if ($added && !App::getSetting(sprintf('labels.%s.agent_can_create', $type))) {
			$added = $rep->correctLabels($type, $added, false);
		} else {
			$added = $rep->correctLabels($type, $added, true);
		}

		foreach ($added as $added_label) {
			$this->addLabel($added_label, true);
		}
		foreach ($removed as $removed_label) {
			$this->removeLabel($removed_label);
		}
	}

	public static function normalizeLabel($label)
	{
		$label = trim($label);
		$label = str_replace(',', '', $label);

		return $label;
	}
}
