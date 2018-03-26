<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\TextSnippets;

use Application\DeskPRO\Entity\TextSnippetCategory as TextSnippetCategoryEntity;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\TextSnippets\TextSnippetCategory as TextSnippetCategoryModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class TextSnippetCategoryHandler.
 */
class TextSnippetCategoryHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return TextSnippetCategoryEntity::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param TextSnippetCategoryEntity $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $title = $entity->getObjectPropLanguageTranslationValue('title', $context->getUser()->getLanguage());

        return new TextSnippetCategoryModel($entity, $title);
    }
}
