<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Tickets;

use Application\DeskPRO\Entity\TicketFeedback;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\TicketFeedback as TicketFeedbackModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class TicketFeedbackHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     *
     * @param TicketFeedback $entity
     *
     * @return TicketFeedbackModel
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new TicketFeedbackModel($entity);
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return TicketFeedback::class;
    }
}
