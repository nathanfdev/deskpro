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

use Application\DeskPRO\CustomFields\CustomDataPersister;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\CustomFieldData;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use Application\DeskPRO\Form\Transformer\CustomDataTransformer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormBuilderInterface;
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
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		parent::buildForm($builder, $options);
		$builder->addEventSubscriber($this);
	}

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver
			->setDefaults(array(
				'data_class' => 'Application\DeskPRO\Entity\CustomFieldData',
				'label' => $this->definition['title'],
				'attr' => array(
					'data-definition-type' => $this->getName(),
					'data-definition-id' => $this->definition['id'],
				),
			))
			->setRequired(array(
				'owner', 'persister',
			))
			->setOptional(array(
				'context',
			))
			->setAllowedTypes(array(
				'owner' => 'Application\DeskPRO\Domain\DomainObject',
				'persister' => 'Application\DeskPRO\CustomFields\CustomDataPersister',
				'context' => array('null', 'Application\DeskPRO\Domain\DomainObject'),
			));
	}

	/**
	 * @return array
	 */
	protected function getValueOptions()
	{
		$options = $this->definition['options'];
		$options['label'] = false;
		unset($options['allow_edit']);

		return $options;
	}

	/**
	 * @param FormView $view
	 * @param FormInterface $form
	 * @param array $options
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		$view->vars['def'] = $this->definition;
		$view->vars['rendered_data'] = null;

		if (!($data = $form->getData()) instanceof CustomFieldData) {
			return;
		}
		$view->vars['rendered_data'] = $data['input'];
	}

	/**
	 * @return CustomFieldDefinition
	 */
	public function getDefinition()
	{
		return $this->definition;
	}

	/**
	 * @param FormEvent $event
	 */
	public function onPreSubmit(FormEvent $event)
	{
		// clean extra data
		if ($data = $event->getData()) {
			$data = array_intersect_key($data, $event->getForm()->all());
			$event->setData($data);
		}
	}

	/**
	 * @param FormEvent $event
	 */
	public function onPostSubmit(FormEvent $event)
	{
		if (!($data = $event->getData()) instanceof CustomFieldData) {
			return;
		}
		$form = $event->getForm();

		$options = $form->getConfig()->getOptions();
		/** @var CustomDataPersister $persister */
		$persister = $options['persister'];
		/** @var DomainObject $owner */
		$owner = $options['owner'];

		if ($data->getData()) {
			$persister->add($data);
			$data->owner = $owner;
			$data->definition = $this->definition;
			$data->root_definition = $this->definition;
		} else {
			$persister->remove($data);
		}
	}

	/**
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return array(
			FormEvents::PRE_SUBMIT => 'onPreSubmit',
			FormEvents::POST_SUBMIT => 'onPostSubmit',
		);
	}
}
