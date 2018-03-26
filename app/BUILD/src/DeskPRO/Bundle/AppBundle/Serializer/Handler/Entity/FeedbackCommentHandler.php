<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\FeedbackComment;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\FeedbackComment as FeedbackCommentModel;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\FeedbackCommentCsv;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class FeedbackCommentHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     *
     * @param FeedbackComment $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $serializerClass = $context->getMappedClass(FeedbackComment::class);

        if ($serializerClass === FeedbackCommentCsv::class) {
            return new FeedbackCommentCsv($entity);
        }

        return new FeedbackCommentModel($entity);
    }

    /**
     * @return string|string[]
     */
    public static function getClassNames()
    {
        return FeedbackComment::class;
    }
}
