<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\CommunityTopicComment;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\CommunityTopicComment as FeedbackCommentModel;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\CommunityTopicCommentCsv;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class FeedbackCommentHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     *
     * @param CommunityTopicComment $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $serializerClass = $context->getMappedClass(CommunityTopicComment::class);

        if ($serializerClass === CommunityTopicCommentCsv::class) {
            return new CommunityTopicCommentCsv($entity);
        }

        return new FeedbackCommentModel($entity);
    }

    /**
     * @return string|string[]
     */
    public static function getClassNames()
    {
        return CommunityTopicComment::class;
    }
}
