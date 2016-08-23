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

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\NewSettings\SettingsBag;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments\TicketMessageAttachmentCollectionType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketMessageType;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class TicketReplyType.
 */
class TicketReplyType extends AbstractType
{
    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * Constructor.
     *
     * @param LanguageManager $languageManager
     */
    public function __construct(LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ticket_message', TicketMessageType::class, [
                'ticket'         => $options['ticket'],
                'person'         => $options['person'],
                'ticket_message' => $options['ticket_message'],
                'message_label'  => $options['message_label'],
                'label'          => false,
                'render_is_note' => false,
                'format'         => 'html',
                'constraints'    => [
                    new AppAssert\Ticket\TicketDupeMessage(),
                ],
                'message_constraints' => [
                    new NotBlank(),
                ],
                'error_mapping' => [
                    '.' => 'message',
                ],
            ])
            ->add('attachments', TicketMessageAttachmentCollectionType::class, [
                'ticket_message' => $options['ticket_message'],
                'person'         => $options['person'],
                'label'          => false,
            ])
            ->add('more_attachments', SubmitType::class, [
                'validation_groups' => false,
                'label'             => $this->languageManager->phrase('portal.forms.label_add_attachment'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => $this->languageManager->phrase('portal.tickets.add-reply'),
            ])
        ;
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'message_label' => false,
            ])
            ->setRequired([
                'person', 'ticket', 'ticket_message', 'settings',
            ])
            ->setAllowedTypes('ticket', Ticket::class)
            ->setAllowedTypes('ticket_message', TicketMessage::class)
            ->setAllowedTypes('settings', SettingsBag::class)
            ->setAllowedTypes('person', Person::class)
        ;
    }
}
