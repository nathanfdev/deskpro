<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\EmailSource;
use DeskPRO\Bundle\AppBundle\Serializer\Model\EmailSource as EmailSourceModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class EmailSourceHandler extends AbstractEntityHandler
{
    public static function getClassNames()
    {
        return EmailSource::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param EmailSource                  $entity
     * @param SideloadSerializationContext $context
     *
     * @return SnippetModel
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new EmailSourceModel($entity);

        return $model;
    }
}
