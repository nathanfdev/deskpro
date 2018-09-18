<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Other;

use DeskPRO\Bundle\AppBundle\Entity\Zapier\TicketUpdate;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Zapier\TicketUpdate as TicketUpdateModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class ZapierTicketUpdateHandler extends AbstractEntityHandler
{
    public static function getClassNames()
    {
        return TicketUpdate::class;
    }

    /**
     * @param $entity
     * @param SideloadSerializationContext $context
     *
     * @return TicketUpdateModel|mixed
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new TicketUpdateModel($entity);
    }
}
