<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ApiTicketMessageAttachmentCollectionType.
 */
class ApiTicketMessageAttachmentCollectionType extends AbstractType
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
            'entry_type' => ApiTicketMessageAttachmentType::class,
        ]);
    }
}
