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

namespace DeskPRO\Bundle\AppBundle\Form\Type\CustomFields;

use Application\DeskPRO\Entity\CustomFieldData;
use Application\DeskPRO\Entity\CustomFieldDefinition;
use DeskPRO\Bundle\AppBundle\CustomField\Context\CustomFieldContext;
use DeskPRO\Bundle\AppBundle\CustomField\Context\CustomFieldTicketContext;
use DeskPRO\Bundle\AppBundle\CustomField\Context\CustomPerFieldManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CustomPerFieldType.
 */
class CustomPerFieldType extends AbstractType
{
    /**
     * @var CustomPerFieldManager
     */
    private $perFieldManager;

    /**
     * Constructor.
     *
     * @param CustomPerFieldManager $perFieldManager
     */
    public function __construct(CustomPerFieldManager $perFieldManager)
    {
        $this->perFieldManager = $perFieldManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreData']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPreData(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        /** @var \Application\DeskPRO\Entity\CustomFieldData $data */
        $data = $event->getData();
        /** @var \Application\DeskPRO\Entity\CustomFieldDefinition $def */
        $def = $data->root_definition;

        if (!$data->getData()) {
            $data->setData($def->getDefaultValue());
        }

        $constraints = [];
        if ($def->isRequired($config->getOption('agent_interface'))) {
            $constraints[] = new Assert\NotBlank();
        }

        $options = [
            'required'     => $def->isRequired($config->getOption('agent_interface')),
            'expanded'     => $def->isExpanded(),
            'multiple'     => $def->isMultiple(),
            'custom_field' => $def,
            'label'        => false,
            'constraints'  => $constraints,
            'help'         => $def->getDescription(),
        ];

        $contextualChoices = $this->perFieldManager->getCustomPerFieldChoices(
            $config->getOption('custom_per_field_definition'),
            $config->getOption('custom_per_field_context')
        );

        $options = array_merge($options, [
            'contextual_choices' => $contextualChoices,
        ]);

        $form->add('data', ContextualPerFieldChoiceType::class, $options);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
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
        $def = $custom_data ? $custom_data->definition : $config->getOption('custom_per_field_definition');
        if (!$def->isMultiple()) {
            $custom_data->input = '';
        }

        $this->perFieldManager->saveDataToQueue($custom_data);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'      => CustomFieldData::class,
                'agent_interface' => false,
            ])
            ->setRequired([
                'custom_per_field_definition',
                'custom_per_field_context',
            ])
            ->setAllowedTypes([
                'custom_per_field_definition' => CustomFieldDefinition::class,
                'custom_per_field_context'    => [CustomFieldContext::class, CustomFieldTicketContext::class],
            ])
        ;
    }
}
