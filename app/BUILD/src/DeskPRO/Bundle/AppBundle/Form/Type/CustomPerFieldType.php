<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type;

use DeskPRO\Bundle\AppBundle\CustomField\Context\CustomPerFieldManager;
use DeskPRO\Bundle\AppBundle\Form\CustomFieldManager\CustomFieldManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CustomPerFieldType.
 */
class CustomPerFieldType extends AbstractType
{
    /**
     * @var CustomPerFieldManager
     */
    private $custom_per_field_manager;

    /**
     * @var CustomFieldManager
     */
    private $field_manager;

    /**
     * Constructor.
     *
     * @param CustomPerFieldManager $custom_per_field_manager
     * @param CustomFieldManager    $field_manager
     */
    public function __construct(CustomPerFieldManager $custom_per_field_manager, CustomFieldManager $field_manager)
    {
        $this->custom_per_field_manager = $custom_per_field_manager;
        $this->field_manager            = $field_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'preDataEvent']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'postSubmitEvent']);
    }

    /**
     * @param FormEvent $event
     */
    public function preDataEvent(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        /** @var \Application\DeskPRO\Entity\CustomFieldData $data */
        $data = $event->getData();
        /** @var \Application\DeskPRO\Entity\CustomFieldDefinition $definition */
        $definition = $data->root_definition;

        if (!$data->getData()) {
            $data->setData($definition->getDefaultValue());
        }

        list($value_name, $form_type, $options) = $this->field_manager->getCustomPerField(
            $definition,
            $config->getOption('agent_interface')
        );

        $contextual_choices = $this->custom_per_field_manager->getCustomPerFieldChoices(
            $config->getOption('custom_per_field_definition'),
            $config->getOption('custom_per_field_context')
        );

        if ($config->getOption('ignore_validation')) {
            $options = array_merge($options, [
                'validation_groups' => [],
                'constraints'       => null,
            ]);
        }

        $options = array_merge(
            $options,
            [
                'contextual_choices' => $contextual_choices,
            ]
        );

        $form->add($value_name, $form_type, $options);
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmitEvent(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\CustomFieldData $custom_data */
        $custom_data = $event->getData();
        $form        = $event->getForm();
        $config      = $form->getConfig();

        if ($custom_data->input === null) {
            $custom_data->input = '';
        }
        if ($custom_data->input) {
            $custom_data->value = 0;
        }

        // if admin switched from multi select to single select, we need to fix the data object
        $custom_data_field                      = $custom_data ? $custom_data->definition : $config->getOption('custom_per_field_definition');
        list($value_name, $form_type, $options) = $this->field_manager->getCustomPerField($custom_data_field, $config->getOption('agent_interface'));

        if (array_key_exists('multiple', $options) && !$options['multiple']) {
            $custom_data->input = '';
        }

        $this->custom_per_field_manager->saveDataToQueue($custom_data);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'      => 'Application\DeskPRO\Entity\CustomFieldData',
                'agent_interface' => false,
            ])
            ->setRequired([
                'custom_per_field_definition',
                'custom_per_field_context',
            ])
            ->setAllowedTypes([
                'custom_per_field_definition' => 'Application\DeskPRO\Entity\CustomFieldDefinition',
                'custom_per_field_context'    => [
                    'DeskPRO\Bundle\AppBundle\CustomField\Context\CustomFieldContext',
                    'DeskPRO\Bundle\AppBundle\CustomField\Context\CustomFieldTicketContext',
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'deskpro_custom_per_field_data';
    }
}
