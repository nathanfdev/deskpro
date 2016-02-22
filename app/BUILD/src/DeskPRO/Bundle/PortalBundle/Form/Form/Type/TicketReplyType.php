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
namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TicketReplyType.
 */
class TicketReplyType extends AbstractType
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
            ->add('ticket_message', 'ticket_message', [
                'ticket'              => $options['ticket'],
                'person'              => $options['person'],
                'message_label'       => $options['message_label'],
                'label'               => false,
                'render_is_note'      => false,
                'format'              => 'html',
                'message_constraints' => [
                    new NotBlank(['message' => 'portal.forms.error_ticket_msg_required']),
                ],
            ])
            ->add('attachments', 'ticket_message_attachment_collection', [
                'ticket_message' => $options['ticket_message'],
                'person'         => $options['person'],
                'label'          => false,
            ])
            ->add('more_attachments', 'submit', [
                'validation_groups' => false,
                'label'             => $this->language_manager->phrase('portal.forms.label_add_attachment'),
            ])
            ->add('submit', 'submit', [
                'label' => $this->language_manager->phrase('portal.tickets.add-reply'),
            ])
        ;

        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        /* @var \Application\DeskPRO\Entity\TicketMessage $message */
        $data                 = $event->getData();
        $message              = $data['ticket_message'];
        $message->attachments = $data['attachments'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ticket_reply';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults([
                'message_label' => false,
            ])
            ->setRequired([
                'person', 'ticket', 'ticket_message', 'settings',
            ])
            ->setAllowedTypes([
                'ticket'         => 'Application\\DeskPRO\\Entity\\Ticket',
                'ticket_message' => 'Application\\DeskPRO\\Entity\\TicketMessage',
                'settings'       => 'Application\\DeskPRO\\NewSettings\\SettingsBag',
                'person'         => 'Application\\DeskPRO\\Entity\\Person',
            ])
        ;
    }
}
