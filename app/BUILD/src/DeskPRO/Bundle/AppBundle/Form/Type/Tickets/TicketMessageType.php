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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Form\Error\FormValidatorChecker;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\HiddenType;
use DeskPRO\Bundle\AppBundle\Form\Type\HtmlTextareaType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsType;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormConfigBuilder;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

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
        $builder->add('message', HtmlTextareaType::class, [
            'property_path' => 'message_html',
            'label'         => $options['message_label'],
            'required'      => $options['required'],
            'constraints'   => $options['message_constraints'],
        ]);

        if ($options['format']) {
            $builder->add('format', HiddenType::class, [
                'empty_data' => 'hidden',
                'mapped'     => false,
            ]);
        } else {
            $builder->add('format', ChoiceType::class, [
                'mapped'            => false,
                'choices_as_values' => true,
                'choices'           => ['html', 'text'],
            ]);
        }

        $ticketMessage = $options['ticket_message'] ?: $builder->getData();

        if ($options['render_is_note']) {
            $builder->add('is_note', ApiBooleanType::class, [
                'property_path' => 'is_agent_note',
            ]);
        }
        if ($options['has_attachments']) {
            $builder->add('attachments', TicketMessageAttachmentCollectionType::class, [
                'required'       => false,
                'person'         => $options['person'],
                'ticket_message' => $ticketMessage,
            ]);
        }

        $builder->add('inline_attachments', TicketMessageInlineAttachmentCollectionType::class, [
            'required'       => false,
            'person'         => $options['person'],
            'ticket_message' => $ticketMessage,
            'mapped'         => false,
        ]);

        if ($options['with_ticket_validation']) {
            $builder->add('ticket', TicketWithLayoutsType::class, [
                'person'              => $options['person'],
                'ticket_view_context' => TicketWithLayoutsContext::VIEW_AGENT,
                'for_api'             => true,
                'disabled'            => true,
                'constraints'         => [
                    // don't add this constraint on the `ticket_message.ticket` property
                    // to allow to add replies to not valid ticket
                    new Assert\Valid(),
                ],
            ]);

            $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onValidateTicket']);
            $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onAccessTicketValidation'], 100);
        }

        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onSetMessageFromOptions']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onChangeMessageFormat'], 100);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetRelations'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'             => TicketMessage::class,
                'message_label'          => $this->language_manager->phrase('portal.forms.label_message'),
                'attr'                   => ['data-rte' => '1'],
                'error_bubbling'         => false,
                'ticket'                 => null,
                'person'                 => null,
                'ticket_message'         => null,
                'render_is_note'         => true,
                'has_attachments'        => false,
                'format'                 => '',
                'with_ticket_validation' => false,
                'message_constraints'    => [],
                'error_mapping'          => [
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
                'person'         => Person::class,
                'ticket'         => Ticket::class,
                'ticket_message' => ['null', TicketMessage::class],
            ])
            ->setAllowedValues([
                'format' => ['', 'html', 'text'],
            ])
        ;
    }

    /**
     * @param FormEvent $event
     */
    public function onSetMessageFromOptions(FormEvent $event)
    {
        $config = $event->getForm()->getConfig();
        if ($config->getOption('ticket_message')) {
            $event->setData($config->getOption('ticket_message'));
        }
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

        /** @var Ticket $ticket */
        $ticket = $config->getOption('ticket');

        if ($data instanceof TicketMessage) {
            if ($ticket && !$ticket->messages->contains($data)) {
                $ticket->addMessage($data);
            }

            $person = $config->getOption('person');
            if ($person) {
                $data->setPerson($person);
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onValidateTicket(FormEvent $event)
    {
        $form = $event->getForm()->get('ticket');

        foreach ($form->all() as $child) {
            FormValidatorChecker::submitForm($child);
        }
    }

    /**
     * We can't get form errors from disabled form so we need to enable the ticket form to access them.
     *
     * @param FormEvent $event
     */
    public function onAccessTicketValidation(FormEvent $event)
    {
        $form   = $event->getForm()->get('ticket');
        $config = $form->getConfig();

        $property = new \ReflectionProperty(FormConfigBuilder::class, 'disabled');
        $property->setAccessible(true);
        $property->setValue($config, false);
        $property->setAccessible(false);
    }
}
