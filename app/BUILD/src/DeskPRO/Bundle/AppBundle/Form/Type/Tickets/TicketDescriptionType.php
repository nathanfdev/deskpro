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

use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TicketDescriptionType.
 */
class TicketDescriptionType extends AbstractType
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
                'attr'          => ['data-rte-field' => 'message'],
                'constraints'   => [
                    new Assert\NotBlank(['message' => 'portal.forms.error_ticket_msg_required']),
                    new Assert\Length(['min' => 10, 'minMessage' => 'portal.forms.error_ticket_msg_length']),
                ],
            ])
            ->add('format', 'hidden', [
                'data'        => 'text',
                'attr'        => ['data-rte-field' => 'format'],
                'constraints' => [
                    new Assert\Choice(['choices' => ['text', 'html']]),
                ],
                'mapped' => false,
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreData']);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreData(FormEvent $event)
    {
        /** @var \Application\DeskPRO\Entity\TicketMessage $message */
        $message = $event->getData();

        if (!$message) {
            $event->setData($message = new TicketMessage());
        }

        $config = $event->getForm()->getConfig();
        $ticket = $config->getOption('ticket');
        $person = $config->getOption('person');

        $message->setTicket($ticket);
        $message->setPerson($person);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ticket_description';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'    => 'Application\\DeskPRO\\Entity\\TicketMessage',
                'message_label' => $this->language_manager->phrase('portal.forms.label_message'),
                'attr'          => ['data-rte' => '1'],
            ])
            ->setRequired([
                'person',
                'ticket',
            ])
            ->setAllowedTypes([
                'person' => 'Application\\DeskPRO\\Entity\\Person',
                'ticket' => 'Application\\DeskPRO\\Entity\\Ticket',
            ])
        ;
    }
}
