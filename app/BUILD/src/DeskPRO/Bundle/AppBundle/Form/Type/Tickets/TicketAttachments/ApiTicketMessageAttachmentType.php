<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments;

use Application\DeskPRO\Entity\TicketAttachment;
use DeskPRO\Bundle\AppBundle\Form\Type\Attachments\ApiAttachmentType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ApiTicketMessageAttachmentType.
 */
class ApiTicketMessageAttachmentType extends AbstractType implements TicketMessageAttachmentCollectionCriteriaInterface
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ApiAttachmentType::class;
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
        return true;
    }
}
