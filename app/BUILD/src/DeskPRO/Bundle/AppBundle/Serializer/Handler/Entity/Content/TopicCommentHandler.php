<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content;

use Application\DeskPRO\Entity\TopicComment;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Comment\TopicComment as TopicCommentModel;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

class TopicCommentHandler extends AbstractEntityHandler
{
    /**
     * @var AvatarResolver
     */
    protected $avatarResolver;

    public function __construct(AvatarResolver $avatarResolver)
    {
        $this->avatarResolver = $avatarResolver;
    }

    /**
     * {@inheritdoc}
     *
     * @param TopicComment $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $avatar = $this->avatarResolver->getAvatar($entity->getPerson(), 50, $default);

        return new TopicCommentModel($entity, $default ? null : $avatar);
    }

    /**
     * @return string|string[]
     */
    public static function getClassNames()
    {
        return TopicComment::class;
    }
}
