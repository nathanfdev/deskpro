<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\NewSettings\SettingsBag;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments\WebTicketMessageAttachmentCollectionType;
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
                'ticket'            => $options['ticket'],
                'person'            => $options['person'],
                'ticket_message'    => $options['ticket_message'],
                'message_label'     => $options['message_label'],
                'label'             => false,
                'render_is_note'    => false,
                'ctrl_enter_submit' => true,
                'format'            => 'html',
                'constraints'       => [
                    new AppAssert\Ticket\TicketDupeMessage(),
                    new AppAssert\Ticket\TicketOpenedMessage(),
                ],
                'message_constraints' => [
                    new NotBlank(),
                ],
                'error_mapping' => [
                    '.' => 'message',
                ],
            ])
            ->add('attachments', WebTicketMessageAttachmentCollectionType::class, [
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
    public function getBlockPrefix()
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
