<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use DeskPRO\Bundle\AppBundle\Entity\Snippet;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Snippets\Snippet as SnippetModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class SnippetHandler extends AbstractEntityHandler
{
    public static function getClassNames()
    {
        return Snippet::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Snippet                      $entity
     * @param SideloadSerializationContext $context
     *
     * @return SnippetModel
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new SnippetModel($entity);

        return $model;
    }
}
