<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Handler\Entity;

use DeskPRO\Bundle\AppBundle\Entity\AbstractUserChatQueueTarget;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\VoiceBundle\Permissions\UserChatPermissionsChecker;
use DeskPRO\Bundle\VoiceBundle\Serializer\Model\UserChatQueue as UserChatQueueModel;

/**
 * Class UserChatQueueHandler.
 */
class UserChatQueueHandler extends AbstractEntityHandler
{
    /**
     * @var UserChatPermissionsChecker
     */
    private $permissionsChecker;

    /**
     * Constructor.
     *
     * @param UserChatPermissionsChecker $permissionsChecker
     */
    public function __construct(UserChatPermissionsChecker $permissionsChecker)
    {
        $this->permissionsChecker = $permissionsChecker;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return UserChatQueue::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param UserChatQueue $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new UserChatQueueModel($entity);
        $model->setTargets($entity->getTargets()->filter(function (AbstractUserChatQueueTarget $target) {
            return $this->permissionsChecker->canBeMemberOfChatQueue($target);
        }));

        return $model;
    }
}
