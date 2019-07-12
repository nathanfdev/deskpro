<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content\Categories;

use Application\DeskPRO\Entity\CommunityChannel;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories\CommunityChannel as CommunityChannelModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class CommunityChannelHandler.
 */
class CommunityChannelHandler extends AbstractCategoryHandler
{
    /**
     * {@inheritdoc}
     *
     * @param CommunityChannel $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new CommunityChannelModel($entity);
        $model->setTitleTranslations($this->getTitleTranslations($entity));

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return CommunityChannel::class;
    }
}
