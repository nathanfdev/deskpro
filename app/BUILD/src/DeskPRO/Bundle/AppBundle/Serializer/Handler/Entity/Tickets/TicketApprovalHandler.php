<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Tickets;

use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval as TicketApprovalEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketApproval;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class TicketApprovalHandler
 *
 * @package DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Tickets
 */
class TicketApprovalHandler extends AbstractEntityHandler
{
    /**
     * {@inheritDoc}
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return TicketApproval::createFromEntity($entity);
    }

    /**
     * {@inheritDoc}
     */
    public static function getClassNames()
    {
        return TicketApprovalEntity::class;
    }
}
