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
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;

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
	 * @var \Application\DeskPRO\CustomFields\CustomDataPersister
	 */
	protected $persister;

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
		$this->persister = new CustomDataPersister();
	}

	/**
	 * @param DomainObject $owner
	 * @param DomainObject $context
	 * @param Layout $layout
	 * @return array
	 */
	public function getCustomDataForOwner(DomainObject $owner, DomainObject $context = null, Layout $layout = null)
	{
		$datas = array();

		if (!$owner['id']) {
			return $datas;
		}

		// fetch fields values
		foreach ($this->repData->getAllDataForOwner($owner, $context, $layout) as $data) {
			/** @var $data CustomFieldData */
			$rootId = $data->root_definition['id'];

			if (!array_key_exists($rootId, $datas)) {
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
	public function flushCustomData(FormInterface $form)
	{
		$form->getConfig()->getEventDispatcher()->dispatch(self::EVENT_FLUSH);
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

		foreach ($this->repDefinition->getAllDefinitionsForOwner($owner, $context, $layout) as $def) {
			/** @var $def CustomFieldDefinition */
			$type = $def->createType();
			$data = isset($datas[$def['id']]) ? $datas[$def['id']] : null;

			$builder->add($def['id'], $type, array(
				'owner' => $owner,
				'context' => $context,
				'data' => $data,
				'persister' => $this->persister,
			));
		}

		$persister = $this->persister;
		$em = $this->em;

		$builder
			->addEventListener(self::EVENT_FLUSH, function() use ($persister, $em){
				$persister->flush($em);
			})
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
	 * todo used for ContextualChoiceDefinition only (for now)
	 * the only place this form used is Person view in Agent Interface (to define contextual choices for this person)
	 *
	 * @param DomainObject $context
	 * @return \Symfony\Component\Form\Form
	 */
	public function createDefinitionsFormForContext(DomainObject $context)
	{
		$builder = $this->ff->createNamedBuilder('custom_fields_definitions', 'form');

		// root definitions
		$definitions = $this->repDefinition->findBy(array(
			'parent' => null,
			'context_class' => get_class($context)
		), array('display_order' => 'ASC'));

		// def children for current context
		$children = new ArrayCollection();
		$_children = $this->repDefinition->findBy(array(
			'context_class' => get_class($context),
			'context_id' => $context['id'],
		), array('display_order' => 'ASC'));

		foreach ($_children as $child) {
			if (!$child->parent) {
				continue;
			}
			$pid = $child->parent['id'];
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
				'children_only' => true,
				'label' => $def['title'],
			));
		}

		return $builder->getForm();
	}
}
