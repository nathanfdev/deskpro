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

namespace Application\DeskPRO\Service;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Form\Type\CustomFields\Definitions\ContextualChoiceDefinitionType;
use Application\DeskPRO\Form\Type\CustomFields\Definitions\DefinitionChildrenType;
use Application\DeskPRO\Form\Type\CustomFields\Definitions\DefinitionType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactory;

class CustomFieldManager
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Symfony\Component\Form\FormFactory
	 */
	protected $ff;

	/**
	 * @var \Application\DeskPRO\EntityRepository\CustomFieldDefinition
	 */
	protected $repDefinition;

	/**
	 * @var \Application\DeskPRO\EntityRepository\CustomFieldData
	 */
	protected $repData;

	public function __construct(EntityManager $em, FormFactory $ff)
	{
		$this->em = $em;
		$this->ff = $ff;
		$this->repDefinition = $em->getRepository('DeskPRO:CustomFieldDefinition');
		$this->repData = $em->getRepository('DeskPRO:CustomFieldData');
	}

	/**
	 * creates form of defined custom fields
	 *
	 * @param DomainObject $owner
	 * @param DomainObject $context add contextual fields to form if context provided
	 * @return \Symfony\Component\Form\Form
	 */
	public function createFormForOwner(DomainObject $owner, DomainObject $context = null)
	{
		$builder = $this->ff->createNamedBuilder('custom_fields', 'form');
		$datas = array();

		// fetch fields values
		foreach ($this->repData->getAllDataAsArrayForOwner($owner, $context) as $row) {
			$did = $row['definition_parent_id'] ?: $row['definition_id'];
			$value = $row['value'] . $row['input'];

			if (!array_key_exists($did, $datas)) {
				$datas[$did] = $value;
			} else {
				if (!is_array($datas[$did])) {
					$datas[$did] = array($datas[$did]);
				}

				$datas[$did][] = $value;
			}
		}

		// build types and bind values
		foreach ($this->repDefinition->getAllDefinitionsForOwner($owner, $context) as $def) {
			/** @var $def CustomFieldDefinition */
			$type = $def->createType();

			$builder->add($type->getName() . '_' . $def['id'], $type, array(
				'entity_manager' => $this->em,
				'owner' => $owner,
				'context' => $context,
				'data' => array_key_exists($def['id'], $datas) ? $datas[$def['id']] : null,
			));
		}

		return $builder->getForm();
	}

	/**
	 * @param DomainObject $context
	 * @return \Symfony\Component\Form\Form
	 */
	public function createDefinitionsFormForContext(DomainObject $context)
	{
		$builder = $this->ff->createNamedBuilder('custom_fields_definitions', 'form');
		$definitions = $this->repDefinition->findBy(array(
			'parent' => null,
			'context_class' => get_class($context)
		));

		$children = new ArrayCollection();
		$_children = $this->repDefinition->findBy(array(
			'context_class' => get_class($context),
			'context_id' => $context['id'],
		));
		foreach ($_children as $child) {
			if (!$pid = $child->parent['id']) {
				continue;
			}
			if (!$sub = $children->get($pid)) {
				$sub = new ArrayCollection();
				$children->set($pid, $sub);
			}
			$sub->add($child);
		}

		foreach ($definitions as $def) {
			/** @var $def CustomFieldDefinition */

			$builder->add('definition_' . $def['id'], new ContextualChoiceDefinitionType(), array(
				'context' => $context,
				'data' => $def,
				'children_collection' => $children,
			));
		}

		return $builder->getForm();
	}
}
