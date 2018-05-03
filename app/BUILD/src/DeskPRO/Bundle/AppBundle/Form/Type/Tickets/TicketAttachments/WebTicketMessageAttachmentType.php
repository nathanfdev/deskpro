<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments;

use Application\DeskPRO\Entity\TicketAttachment;
use DeskPRO\Bundle\AppBundle\Form\Type\Attachments\WebAttachmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WebTicketMessageAttachmentType.
 */
class WebTicketMessageAttachmentType extends AbstractType implements TicketMessageAttachmentCollectionCriteriaInterface
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return WebAttachmentType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ticket_message_attachment';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TicketAttachment::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function matchedCriteria(TicketAttachment $attachment)
    {
        return !$attachment->isInline();
    }
}
