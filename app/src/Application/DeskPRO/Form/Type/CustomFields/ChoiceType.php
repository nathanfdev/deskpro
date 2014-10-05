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
use Application\DeskPRO\Entity\CustomDefEntity;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

class ChoiceType extends CustomFieldType
{
	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		parent::setDefaultOptions($resolver);

		$resolver->setDefaults(array(
			'class' => 'DeskPRO:CustomFieldDefinition',
			'property' => 'title',
			'empty_value' => '',
		));

		$that = $this;
		$resolver->setNormalizers(array(
			'choices' => function(Options $options, $configs) use ($that) {
				return $that->fetchChoices($options);
			},
		));
	}

	/**
	 * @param Options $options
	 * @return array
	 */
	public function fetchChoices(Options $options)
	{
		$em = $options['entity_manager'];
		$choices = array();

		$res = $this->getChoicesQueryBuilder($em->getRepository('DeskPRO:CustomFieldDefinition'), $options)
			->getQuery()->execute();

		foreach ($res as $row) {
			$choices[$row['id']] = $row['title'];
		}

		return $choices;
	}

	/**
	 * @param EntityRepository $er
	 * @param Options $options
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function getChoicesQueryBuilder(EntityRepository $er, Options $options)
	{
		$def = $this->definition;

		return $er->createQueryBuilder('d')
			->select('d.id, d.title')
			->where('d.parent = :parent')
			->andWhere('d.owner_class = :owner_class and d.context_class is null')
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
		$data = $form->getData();
		$rendered = null;

		if (null !== $data) {
			$data = (array) $data;
			$rendered = array_intersect_key($options['choices'], array_flip($data));
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
	 * @return null|string|\Symfony\Component\Form\FormTypeInterface
	 */
	public function getParent()
	{
		return 'choice';
	}
}
