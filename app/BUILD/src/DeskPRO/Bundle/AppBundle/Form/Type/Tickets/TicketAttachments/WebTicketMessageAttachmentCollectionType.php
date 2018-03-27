<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WebTicketMessageAttachmentCollectionType.
 */
class WebTicketMessageAttachmentCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseTicketMessageAttachmentCollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type' => WebTicketMessageAttachmentType::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ticket_message_attachment_collection';
    }
}
