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

use Application\DeskPRO\CustomFields\CustomDataPersister;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Entity\CustomFieldData;
use Application\DeskPRO\Form\Type\CustomFields\Definitions\ContextualChoiceDefinitionType;
use Application\DeskPRO\Form\Type\CustomFields\Definitions\DefinitionChildrenType;
use Application\DeskPRO\Form\Type\CustomFields\Definitions\DefinitionType;
use Application\DeskPRO\TicketLayout\Layout;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Doctrine\Common\Util\ClassUtils;

class CustomFieldManager
{
	const EVENT_FLUSH = 'flush';

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

	/**
	 * @var \Application\DeskPRO\CustomFields\CustomDataPersister
	 */
	protected $persister;

	public function __construct(EntityManager $em, FormFactory $ff)
	{
		$this->em = $em;
		$this->ff = $ff;
		$this->repDefinition = $em->getRepository('DeskPRO:CustomFieldDefinition');
		$this->repData = $em->getRepository('DeskPRO:CustomFieldData');
		$this->persister = new CustomDataPersister();
	}

	/**
	 * @param DomainObject $owner
	 * @param DomainObject $context
	 * @param Layout $layout
	 * @return ArrayCollection
	 */
	public function getCustomDataForOwner(DomainObject $owner, DomainObject $context = null, Layout $layout = null)
	{
		$datas = new ArrayCollection();

		if (!$owner['id']) {
			return $datas;
		}

		// fetch fields values
		foreach ($this->repData->getAllDataForOwner($owner, $context, $layout) as $data) {
			/** @var $data CustomFieldData */
			$rootId = $data->root_definition['id'];

			if (!$datas->containsKey($rootId)) {
				$datas[$rootId] = $data;
			} else {
				if ($datas[$rootId] instanceof CustomFieldData) {
					$datas[$rootId] = array($datas[$rootId]);
				}

				$datas[$rootId][] = $data;
			}
		}

		return $datas;
	}

	/**
	 * @param FormInterface $form
	 */
	public function flush(FormInterface $form = null)
	{
		if ($form && !$form->isSubmitted()) {
			return;
		}
		$this->persister->flush($this->em);
	}

	/**
	 * creates form of defined custom fields
	 *
	 * @param DomainObject $owner
	 * @param DomainObject $context add contextual fields to form if context provided
	 * @param Layout $layout
	 * @return \Symfony\Component\Form\Form
	 */
	public function createFormForOwner(DomainObject $owner, DomainObject $context = null, Layout $layout = null)
	{
		$datas = $this->getCustomDataForOwner($owner, $context, $layout);

		// build types and bind values
		$builder = $this->ff->createNamedBuilder('custom_fields', 'form');

		foreach ($this->getDefinitions($owner, $context, $layout) as $def) {
			/** @var $def CustomFieldDefinition */
			$builder->add($this->createFieldFormBuilder($def, $owner, $context, $datas));
		}

		$builder
			->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
				// clean extra data
				if ($data = $event->getData()) {
					$data = array_intersect_key($data, $event->getForm()->all());
					$event->setData($data);
				}
			})
		;

		return $builder->getForm();
	}

	/**
	 * @param CustomFieldDefinition $definition
	 * @param DomainObject $owner
	 * @param DomainObject $context
	 * @param ArrayCollection $datas
	 * @param array $options
	 * @return \Symfony\Component\Form\FormBuilderInterface
	 */
	public function createFieldFormBuilder(CustomFieldDefinition $definition, DomainObject $owner, DomainObject $context = null, ArrayCollection $datas = null, $options = array())
	{
		if ($definition->parent) {
			throw new InvalidArgumentException('Can\'t create form for definition child');
		}

		if (null === $datas) {
			$datas = new ArrayCollection();
			if ($childData = $this->repData->getFieldData($definition, $owner, $context)) {
				$datas->set($definition['id'], count($childData) > 1 ? $childData : $childData[0]);
			}
		}

		$data = $datas->containsKey($definition['id']) ? $datas->get($definition['id']) : null;
		$options = array_merge($options, array(
			'owner' => $owner,
			'context' => $context,
			'persister' => $this->persister,
		));

		return $this->ff->createNamedBuilder($definition['id'], $definition->createType(), $data, $options);
	}

	/**
	 * @param CustomFieldDefinition $definition
	 * @param DomainObject $owner
	 * @param DomainObject $context
	 * @return \Symfony\Component\Form\FormBuilderInterface
	 */
	public function createFieldForm(CustomFieldDefinition $definition, DomainObject $owner, DomainObject $context = null, $options = array())
	{
		if (!$definition['is_enabled']) {
			// todo exception?
			return null;
		}

		return $this->createFieldFormBuilder($definition, $owner, $context, null, $options)->getForm();
	}

	/**
	 * @param $fieldId
	 * @param DomainObject $owner
	 * @return array|null
	 * @throws \Symfony\Component\Form\Exception\InvalidArgumentException
	 */
	public function getFieldRawData($fieldId, DomainObject $owner)
	{
		if (!$owner['id']) {
			return null;
		}

		if (!$definition = $this->repDefinition->find($fieldId)) {
			return null;
		}

		if ($definition->parent) {
			throw new InvalidArgumentException('Can\'t create form for definition child');
		}

		if (!$data = $this->repData->getFieldRawData($definition, $owner)) {
			return null;
		}

		$ret = array();
		foreach ($data as $row) {
			$ret[] = $row['value'] ? $row['title'] : $row['input'];
		}

		return count($ret) > 1 ? $ret : $ret[0];
	}

	/**
	 * @param FormInterface $form1
	 * @param FormInterface $form2
	 * @return FormInterface
	 */
	public function merge(FormInterface $form1, FormInterface $form2)
	{
		foreach ($form2 as $name => $field) {
			/** @var $field FormInterface */
			$form2->remove($name);
			$form1->add($field);
		}

		return $form1;
	}

	/**
	 * todo used for ContextualChoiceDefinition only (for now)
	 * the only place this form used is Person view in Agent Interface (to define contextual choices for this person)
	 *
	 * @param DomainObject $context
	 * @return \Symfony\Component\Form\Form
	 */
	public function createDefinitionsFormForContext(DomainObject $context)
	{
		// root definitions
		$definitions = $this->repDefinition->findBy(array(
			'parent' => null,
			'context_class' => ClassUtils::getClass($context),
			'is_enabled' => true,
		), array('display_order' => 'ASC'));

		$children = $this->buildDefinitionChildrenCollectionForContext($context);

		// build form
		$builder = $this->ff->createNamedBuilder('custom_fields_definitions', 'form');

		foreach ($definitions as $def) {
			/** @var $def CustomFieldDefinition */

			$builder->add('definition_' . $def['id'], new ContextualChoiceDefinitionType(), array(
				'context' => $context,
				'data' => $def,
				'children_collection' => $children,
				'children_only' => true,
				'label' => $def['title'],
				'persister' => $this->persister,
			));
		}

		return $builder->getForm();
	}

	/**
	 * @param DomainObject $context
	 * @return ArrayCollection
	 */
	protected function buildDefinitionChildrenCollectionForContext(DomainObject $context)
	{
		// def children for current context
		$collection = new ArrayCollection();

		if (!$context['id']) {
			return $collection;
		}

		$_children = $this->repDefinition->findBy(array(
			'context_class' => ClassUtils::getClass($context),
			'context_id' => $context['id'],
		), array('display_order' => 'ASC'));

		// build child tree
		foreach ($_children as $child) {
			if (!$child->parent) {
				continue;
			}
			$pid = $child->parent['id'];
			if (!$sub = $collection->get($pid)) {
				$sub = new ArrayCollection();
				$collection->set($pid, $sub);
			}
			$sub->add($child);
		}

		return $collection;
	}

	/**
	 * @param DomainObject $owner
	 * @param DomainObject $context
	 * @param Layout $layout
	 * @return array
	 */
	public function getDefinitions(DomainObject $owner, DomainObject $context = null, Layout $layout = null)
	{
		return $this->repDefinition->getAllDefinitionsForOwner($owner, $context, $layout);
	}

	/**
	 * @param $fieldId
	 * @return CustomFieldDefinition|null
	 */
	public function getDefinition($fieldId)
	{
		return $this->repDefinition->findOneBy(array('id' => $fieldId, 'is_enabled' => true));
	}
}
