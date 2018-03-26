<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Tickets;

use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Tickets\LayoutField as LayoutFieldModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class LayoutFieldHandler extends AbstractEntityHandler
{
    public static function getClassNames()
    {
        return LayoutField::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param LayoutField $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        return new LayoutFieldModel($entity, 'agent');
    }
}
