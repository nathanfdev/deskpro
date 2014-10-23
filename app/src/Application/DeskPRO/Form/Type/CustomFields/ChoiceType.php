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

namespace Application\DeskPRO\Form\Type\CustomFields;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Form\Transformer\CustomDataTransformer;
use Application\DeskPRO\Form\Transformer\CustomFields\ChoiceDataTransformer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\From;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChoiceType extends CustomFieldType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$that = $this;
		$fieldOptions = $this->getValueOptions();

		$builder
			->add('value', 'entity', array_merge($fieldOptions, array(

				'empty_value' => ! empty($fieldOptions['expanded']) ? false : 'Choose an option',

				'class' => 'DeskPRO:CustomFieldDefinition',
				'property' => 'title',
				'query_builder' => function(EntityRepository $er) use ($that, $options) {
					return $that->getChoicesQueryBuilder($er, $options);
				},
			)))
			->addModelTransformer(new ChoiceDataTransformer($options['persister'], $options['owner']))
		;

		parent::buildForm($builder, $options);
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		parent::setDefaultOptions($resolver);
		$resolver->setDefaults(array(
			'data_class' => null,
			'allow_add' => false,
		));
	}

	/**
	 * @param EntityRepository $er
	 * @param array $options
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function getChoicesQueryBuilder(EntityRepository $er, array $options)
	{
		$def = $this->definition;

		return $er->createQueryBuilder('d')
			->add('from', new From('DeskPRO:CustomFieldDefinition', 'd', 'd.id'), false)
			->where('d.parent = :parent')
			->andWhere('d.owner_class = :owner_class and d.context_class is null')
			->orderBy('d.display_order', 'ASC')
			->setParameter('owner_class', $def['owner_class'])
			->setParameter('parent', $def['id']);
	}

	/**
	 * @param FormView $view
	 * @param FormInterface $form
	 * @param array $options
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		parent::buildView($view, $form, $options);
		if (!$data = $form->get('value')->getData()) {
			return;
		}

		$rendered = null;

		if ($data instanceof CustomFieldDefinition) {
			$rendered = $data['title'];
		} else {
			// @var $data CustomFieldDefinition[]
			$rendered = array();
			foreach ($data as $el) {
				$rendered[] = $el['title'];
			}
			$rendered = implode(', ', $rendered);
		}

		$view->vars['rendered_data'] = $rendered;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'cf_choice';
	}

	/**
	 * override parent call
	 */
	public function onPostSubmit(FormEvent $event)
	{
		return;
	}

	public function onPreSubmit(FormEvent $event)
	{
		if (!$data = $event->getData()) {
			return;
		}
		$form = $event->getForm();
		$allowAdd = $form->getConfig()->getOption('allow_add');

		if ($allowAdd && isset($data['value'])) {

			/** @var EntityChoiceList $choices */
			$choices = $form->getConfig()->getOption('choice_list')->getChoices();
			$this->handleChoice($form, $choices, $data['value']);
			$form->remove('value');
			$form->add('value', 'entity', array_merge($this->getValueOptions(), array(
				'class' => 'DeskPRO:CustomFieldDefinition',
				'choices' => $choices,
			)));

			$event->setData($data);
		}

		parent::onPreSubmit($event);
	}

	/**
	 * select choice or add new if not exist
	 * @param FormInterface $form
	 * @param array $choices
	 * @param $data
	 */
	protected function handleChoice(FormInterface $form, array &$choices, &$data)
	{
		if (!$data) {
			return;
		}

		if (is_array($data)) {
			foreach ($data as &$dataEl) {
				$this->handleChoice($form, $choices, $dataEl);
			}

			return;
		}

		$check = strtolower($data);
		$newVal = isset($choices[$data]) ? $choices[$data] : null;

		// first, string comparison
		if (!$newVal) {
			foreach ($choices as $choice) {
				/** @var CustomFieldDefinition $choice */
				if (strtolower($choice['title']) === $check) {
					$newVal = $choice['id'];
				}
			}
		}

		// then, add new choice to list
		if (!$newVal) {
			$newDef = clone $this->definition;
			$newDef['id'] = null;
			$newDef->parent = $this->definition;
			$newDef->children = new ArrayCollection();
			$newDef['title'] = $data['value'];

			if ($context = $form->getConfig()->getOption('context')) {
				$newDef['context_id'] = $context['id'];
			}

			$form->get('value')->getConfig()->getOption('em')->persist($newDef);
			$form->get('value')->getConfig()->getOption('em')->flush($newDef);
			$newVal = $newDef['id'];
			$choices[$newVal] = $newDef;
		}

		$data = $newVal;
	}
}
