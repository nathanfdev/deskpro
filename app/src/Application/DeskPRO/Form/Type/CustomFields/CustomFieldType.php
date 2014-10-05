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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

abstract class CustomFieldType extends AbstractType implements EventSubscriberInterface
{
	/**
	 * @var \Application\DeskPRO\Entity\CustomFieldDefinition
	 */
	protected $definition;

	public function __construct(CustomFieldDefinition $definition)
	{
		$this->definition = $definition;
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$options = $this->definition['options'];
		$options['label'] = $this->definition['title'];
		$options['attr']['data-definition-type'] = $this->getName();
		$options['attr']['data-definition-id'] = $this->definition['id'];

		$resolver
			->setDefaults($options)
			->setRequired(array('owner', 'entity_manager'))
			->setOptional(array('context'))
			->setAllowedTypes(array(
				'owner' => 'Application\DeskPRO\Domain\DomainObject',
				'entity_manager' => 'Doctrine\ORM\EntityManager',
				'context' => 'Application\DeskPRO\Domain\DomainObject',
			));
	}

	/**
	 * @param FormView $view
	 * @param FormInterface $form
	 * @param array $options
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		$view->vars['rendered_data'] = $form->getData();
	}

	/**
	 * @return CustomFieldDefinition
	 */
	public function getDefinition()
	{
		return $this->definition;
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
//			FormEvents::PRE_SUBMIT => 'onPreSubmit',
//			FormEvents::POST_SUBMIT => 'onPostSubmit',
		);
	}
}
