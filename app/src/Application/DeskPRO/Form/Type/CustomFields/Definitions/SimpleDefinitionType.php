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

use Application\DeskPRO\Domain\DomainObject;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SimpleDefinitionType extends AbstractType implements EventSubscriberInterface
{
	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('title');
		$builder->addEventSubscriber($this);
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver
			->setDefaults(array(
				'data_class' => 'Application\DeskPRO\Entity\CustomFieldDefinition',
			))
			->setRequired(array(
				'context',
			))
			->setAllowedTypes(array(
				// todo
				'context' => array(
					'Application\DeskPRO\Entity\Person',
					'Application\DeskPRO\Entity\Ticket',
					'Application\DeskPRO\Entity\Organization',
				)
			))
		;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'simple_definition';
	}


	/**
	 * @param FormEvent $event
	 */
	public function onPreSubmit(FormEvent $event)
	{
		if (!$definition = $event->getData()) {
			return;
		}

		if ($context = $event->getForm()->getConfig()->getOption('context')) {
			$definition['context_class'] = get_class($context);
			$definition['context_id'] = $context['id'];
		}
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			FormEvents::PRE_SUBMIT   => 'onPreSubmit',
		);
	}
}
