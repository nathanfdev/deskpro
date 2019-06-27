<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Handler\Entity;

use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueueAgent;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\VoiceBundle\Permissions\VoicePermissionsChecker;
use DeskPRO\Bundle\VoiceBundle\Serializer\Model\VoiceQueue as VoiceQueueModel;

/**
 * Class VoiceQueueHandler.
 */
class VoiceQueueHandler extends AbstractEntityHandler
{
    /**
     * @var VoicePermissionsChecker
     */
    private $permissionsChecker;

    /**
     * Constructor.
     *
     * @param VoicePermissionsChecker $permissionsChecker
     */
    public function __construct(VoicePermissionsChecker $permissionsChecker)
    {
        $this->permissionsChecker = $permissionsChecker;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return VoiceQueue::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param VoiceQueue $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new VoiceQueueModel($entity);
        $model->setAgents($entity->getAgents()->filter(function (VoiceQueueAgent $queueAgent) use ($entity) {
            return $this->permissionsChecker->canBeMemberOfVoiceQueue($entity, $queueAgent->getAgent());
        }));

        return $model;
    }
}
