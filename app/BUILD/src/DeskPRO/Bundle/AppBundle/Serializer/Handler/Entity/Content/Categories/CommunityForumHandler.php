<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content\Categories;

use Application\DeskPRO\Entity\CommunityForum;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories\CommunityForum as CommunityForumModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class CommunityForumHandler.
 */
class CommunityForumHandler extends AbstractCategoryHandler
{
    /**
     * {@inheritdoc}
     *
     * @param CommunityForum $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new CommunityForumModel($entity);
        $model->setTitleTranslations($this->getTitleTranslations($entity));

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return CommunityForum::class;
    }
}
