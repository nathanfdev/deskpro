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
namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TicketMessageType.
 */
class TicketMessageType extends AbstractType
{
    /**
     * @var LanguageManager
     */
    private $language_manager;

    /**
     * Constructor.
     *
     * @param LanguageManager $language_manager
     */
    public function __construct(LanguageManager $language_manager)
    {
        $this->language_manager = $language_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', 'html_textarea', [
                'property_path' => 'message_html',
                'label'         => $options['message_label'],
                'required'      => $options['required'],
                'constraints'   => $options['message_constraints'],
            ])
        ;

        if ($options['format']) {
            $builder->add('format', 'deskpro_hidden', [
                'empty_data' => 'hidden',
                'mapped'     => false,
            ]);
        } else {
            $builder->add('format', 'choice', [
                'choices' => [
                    'html' => 'html',
                    'text' => 'text',
                ],
                'mapped' => false,
            ]);
        }

        if ($options['render_is_note']) {
            $builder->add('is_note', 'api_boolean', [
                'property_path' => 'is_agent_note',
            ]);
        }

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetAttachments']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onChangeMessageFormat']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetRelations']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'          => 'Application\\DeskPRO\\Entity\\TicketMessage',
                'message_label'       => $this->language_manager->phrase('portal.forms.label_message'),
                'attr'                => ['data-rte' => '1'],
                'error_bubbling'      => false,
                'ticket'              => null,
                'person'              => null,
                'render_is_note'      => true,
                'format'              => '',
                'message_constraints' => [],
                'error_mapping'       => [
                    // we use custom setters to modify message,
                    // so we need to map entity property with the form field
                    'message' => 'message',
                ],
            ])
            ->setRequired([
                'ticket',
                'person',
            ])
            ->setAllowedTypes([
                'person' => 'Application\\DeskPRO\\Entity\\Person',
                'ticket' => 'Application\\DeskPRO\\Entity\\Ticket',
            ])
            ->setAllowedValues([
                'format' => ['', 'html', 'text'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ticket_message';
    }

    /**
     * @param FormEvent $event
     */
    public function onSetAttachments(FormEvent $event)
    {
        $form   = $event->getForm();
        $person = $form->getConfig()->getOption('person');

        $form->add('attachments', 'ticket_message_attachment_collection', [
            'required'       => false,
            'person'         => $person,
            'ticket_message' => $form->getData(),
        ]);
    }

    /**
     * @param FormEvent $event
     */
    public function onChangeMessageFormat(FormEvent $event)
    {
        /** @var TicketMessage $data */
        $data = $event->getData();
        $form = $event->getForm();

        if ($form->get('format')->getData() === 'text') {
            $data->setMessageText($data->getMessageHtml());
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        $data   = $event->getData();
        $config = $event->getForm()->getConfig();

        if ($data instanceof TicketMessage) {
            /** @var Ticket $ticket */
            $ticket = $config->getOption('ticket');
            if ($ticket && !$ticket->messages->contains($data)) {
                $ticket->addMessage($data);
            }

            $person = $config->getOption('person');
            if ($person) {
                $data->setPerson($person);
            }
        }
    }
}
