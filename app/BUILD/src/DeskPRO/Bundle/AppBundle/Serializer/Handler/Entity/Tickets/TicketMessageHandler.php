<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Tickets;

use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketMessage as TicketMessageModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class TicketMessageHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     *
     * @param TicketMessage $entity
     *
     * @return TicketMessageModel
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new TicketMessageModel($entity);
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return TicketMessage::class;
    }
}
