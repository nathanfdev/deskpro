<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments;

use Application\DeskPRO\Entity\TicketAttachment;
use DeskPRO\Bundle\AppBundle\Form\Type\Attachments\WebInlineAttachmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * An inline attachment is one that is uploaded via pasting. So this field has no actual UI because
 * its only filled by JS.
 */
class WebTicketMessageInlineAttachmentType extends AbstractType implements TicketMessageAttachmentCollectionCriteriaInterface
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return WebInlineAttachmentType::class;
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
        return $attachment->isInline();
    }
}
