<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Tickets;

use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse as ApprovalResponseEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\ApprovalResponse;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class ApprovalResponseHandler
 *
 * @package DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Tickets
 */
class ApprovalResponseHandler extends AbstractEntityHandler
{
    /**
     * {@inheritDoc}
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return ApprovalResponse::createFromEntity($entity);
    }

    /**
     * {@inheritDoc}
     */
    public static function getClassNames()
    {
        return ApprovalResponseEntity::class;
    }
}
