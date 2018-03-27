<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Tickets;

use Application\DeskPRO\Entity\TicketParticipant;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class TicketParticipant.
 */
class TicketParticipantHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return TicketParticipant::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param TicketParticipant $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return $entity->getPerson();
    }
}
