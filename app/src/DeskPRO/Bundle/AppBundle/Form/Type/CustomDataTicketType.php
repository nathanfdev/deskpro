<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\CustomDataTicket;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class CustomDataTicketType.
 */
class CustomDataTicketType extends AbstractCustomDataType
{
    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($form->all() as $child) {
            // set it to the first child's label
            if (!$view->vars['help']) {
                $child_help = $child->getConfig()->getOption('help');
                if ($child_help) {
                    $view->vars['help'] = $child_help;
                }
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function preDataEvent(FormEvent $event)
    {
        $form   = $event->getForm();
        $config = $form->getConfig();

        /** @var \Application\DeskPRO\Entity\CustomDataTicket $custom_data */
        $custom_data = $event->getData();
        /** @var \Application\DeskPRO\Entity\CustomDefTicket $custom_data_field */
        $custom_data_field = $custom_data ? $custom_data->field : $config->getOption('custom_data_field');

        if (!$custom_data) {
            $custom_data = new CustomDataTicket();
            $event->setData($custom_data);
        }

        if (!$custom_data->getData()) {
            $custom_data->setData($custom_data_field->getDefaultValue());
        }

        list($value_name, $form_type, $options) = $this->field_manager->getCustomTicketField($custom_data_field, $config->getOption('agent_interface'));

        if ($config->getOption('ignore_validation')) {
            $options = array_merge($options, [
                'validation_groups' => [],
                'constraints'       => null,
            ]);
        }

        $form->add($value_name, $form_type, $options);
    }

    /**
     * @param FormEvent $event
     */
    public function submitEvent(FormEvent $event)
    {
        $config = $event->getForm()->getConfig();
        /** @var \Application\DeskPRO\Entity\CustomDataTicket $custom_data */
        $custom_data = $event->getData();
        if (!$custom_data) {
            $custom_data = new CustomDataTicket();
            $event->setData($custom_data);
        }
        $field               = $config->getOption('custom_data_field');
        $ticket              = $config->getOption('ticket');
        $custom_data->field  = $field;
        $custom_data->ticket = $ticket;
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmitEvent(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\CustomDataTicket $custom_data */
        $custom_data = $event->getData();
        $form        = $event->getForm();
        $config      = $form->getConfig();

        if ($custom_data->getInput() === null) {
            $custom_data->setInput('');
        }
        if ($custom_data->getInput()) {
            $custom_data->setValue(0);
        }

        // if admin switched from multi select to single select, we need to fix the data object
        $custom_data_field                      = $custom_data ? $custom_data->field : $config->getOption('custom_data_field');
        list($value_name, $form_type, $options) = $this->field_manager->getCustomTicketField($custom_data_field, $config->getOption('agent_interface'));

        if (array_key_exists('multiple', $options) && !$options['multiple']) {
            $custom_data->setInput('');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'        => 'Application\DeskPRO\Entity\CustomDataTicket',
                'ignore_validation' => false,
                'fully_hidden'      => function (Options $options) {
                    /** @var \Application\DeskPRO\Entity\CustomDefTicket $field */
                    $field = $options['custom_data_field'];
                    if ($field) {
                        return $field->getHandlerClass() === 'Application\DeskPRO\CustomFields\Handler\Hidden';
                    }

                    return false;
                },
            ])
            ->setRequired([
                'custom_data_field',
                'ticket',
                'agent_interface',
            ])
            ->setAllowedTypes([
                'custom_data_field' => 'Application\DeskPRO\Entity\CustomDefTicket',
                'ticket'            => 'Application\DeskPRO\Entity\Ticket',
                'agent_interface'   => 'bool',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'deskpro_custom_data_ticket';
    }
}
