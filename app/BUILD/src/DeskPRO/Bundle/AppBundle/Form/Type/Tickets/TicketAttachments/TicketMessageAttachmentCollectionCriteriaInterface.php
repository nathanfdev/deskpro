<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments;

use Application\DeskPRO\Entity\TicketAttachment;

/**
 * Interface TicketMessageAttachmentCollectionCriteriaInterface.
 */
interface TicketMessageAttachmentCollectionCriteriaInterface
{
    /**
     * @param TicketAttachment $attachment
     *
     * @return bool
     */
    public static function matchedCriteria(TicketAttachment $attachment);
}
