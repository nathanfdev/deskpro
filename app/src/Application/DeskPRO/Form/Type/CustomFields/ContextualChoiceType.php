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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\From;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContextualChoiceType extends ChoiceType
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		parent::buildForm($builder, $options);

		if ($options['allow_edit']) {
			$builder->add('custom_choice', 'text', array(
				'required' => false,
				'label' => false,
				'mapped' => false,
				'attr' => array(
					'placeholder' => 'Custom choice',
					'style' => 'display:none;',
				),
			));
		}
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		parent::setDefaultOptions($resolver);
		$options = $this->definition['options'];
		$resolver
			->setRequired(array('context'))
			->setDefaults(array(
				'allow_edit' => isset($options['allow_edit']) ? $options['allow_edit'] : false
			))
		;
	}

	/**
	 * @param EntityRepository $er
	 * @param array $options
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getChoicesQueryBuilder(EntityRepository $er, array $options)
	{
		/** @var DomainObject $ctx */
		$ctx = $options['context'];
		$def = $this->definition;

		return $er->createQueryBuilder('d')
			->add('from', new From('DeskPRO:CustomFieldDefinition', 'd', 'd.id'), false)
			->where('d.parent = :parent')
			->andWhere('d.owner_class = :owner_class and d.context_class = :cc and d.context_id = :cid')
			->orderBy('d.display_order', 'ASC')
			->setParameter('parent', $def['id'])
			->setParameter('owner_class', $def['owner_class'])
			->setParameter('cc', ClassUtils::getClass($ctx))
			->setParameter('cid', $ctx['id']);
	}

	/**
	 * @param FormView $view
	 * @param FormInterface $form
	 * @param array $options
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		parent::buildView($view, $form, $options);
		$view->vars['allow_edit'] = $options['allow_edit'];
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'cf_contextual_choice';
	}

	/**
	 * @param FormEvent $event
	 */
	public function onPreSubmit(FormEvent $event)
	{
		if (!$data = $event->getData()) {
			parent::onPreSubmit($event);
			return;
		}

		$form = $event->getForm();
		$editable = $form->getConfig()->getOption('allow_edit');

		if ($editable && !empty($data['custom_choice'])) {

			/** @var EntityChoiceList $choices */
			$choices = $form->get('value')->getConfig()->getOption('choice_list')->getChoices();
			$this->handleCustomChoice($form, $choices, $data);
			$form->remove('value');
			$form->add('value', 'entity', array_merge($this->getValueOptions(), array(
				'class' => 'DeskPRO:CustomFieldDefinition',
				'choices' => $choices,
			)));

			$event->setData($data);
		}
	}

	/**
	 * select choice or add new if not exist
	 * @param FormInterface $form
	 * @param array $choices
	 * @param $data
	 */
	protected function handleCustomChoice(FormInterface $form, array &$choices, &$data)
	{
		if (empty($data['custom_choice'])) {
			return;
		}

		$check = strtolower($data['custom_choice']);
		$newVal = isset($choices[$data['custom_choice']]) ? $choices[$data['custom_choice']] : null;

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
			$newDef['title'] = $data['custom_choice'];

			if ($context = $form->getConfig()->getOption('context')) {
				$newDef['context_id'] = $context['id'];
			}

			$form->get('value')->getConfig()->getOption('em')->persist($newDef);
			$form->get('value')->getConfig()->getOption('em')->flush($newDef);
			$newVal = $newDef['id'];
			$choices[$newVal] = $newDef;
		}

		$data['value'] = $newVal;
		$data['custom_choice'] = null;
	}
}
