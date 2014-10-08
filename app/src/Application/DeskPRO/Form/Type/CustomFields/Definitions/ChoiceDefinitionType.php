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

namespace Application\DeskPRO\Form\Type\CustomFields\Definitions;

use Application\DeskPRO\Form\Type\DpCategoryBuilderType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChoiceDefinitionType extends CustomFieldDefinitionType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		// if we need to define all properties, not only children
		if (!$options['children_only']) {

			parent::buildForm($builder, $options);
			$builder->get('options')
				->add('multiple', 'checkbox')
				->add('expanded', 'checkbox');

		} else {
			// called in parent
			$builder->addEventSubscriber($this);
		}

		$children = null;
		if ($options['data'] && $options['children_collection']) {
			$children = $options['children_collection']->get($options['data']['id']);
		}

		$builder
			->add('_children', new DpCategoryBuilderType(), array(
				'type' => new SimpleDefinitionType(),
				'label' => false,
				'allow_add' => true,
				'allow_delete' => true,
				'required' => false,
				'data' => $children ?: new ArrayCollection(),
				'mapped' => false,
				'options' => array(
					'label' => false,
					'context' => $options['context'],
					'parent' => $options['data'],
				),
			))
		;
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		parent::setDefaultOptions($resolver);
		$resolver
			->setDefaults(array(
				'children_only' => false,
				'children_collection' => null,
			))
			->setOptional(array('children_collection', 'children_only'))
			->addAllowedTypes(array(
				'children_collection' => array('null', 'Doctrine\Common\Collections\ArrayCollection'),
			))
		;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'cf_definition_choice';
	}
}
