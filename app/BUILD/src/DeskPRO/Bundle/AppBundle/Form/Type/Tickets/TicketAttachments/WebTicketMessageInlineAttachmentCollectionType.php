<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WebTicketMessageInlineAttachmentCollectionType.
 */
class WebTicketMessageInlineAttachmentCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'entry_type'          => WebTicketMessageInlineAttachmentType::class,
            'blob_auth_prototype' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return BaseTicketMessageAttachmentCollectionType::class;
    }
}
