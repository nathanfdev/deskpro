<?php

namespace DeskPRO\Bundle\VoiceBundle\Serializer\Handler\Entity;

use DeskPRO\Bundle\AppBundle\Entity\VoiceQueue;
use DeskPRO\Bundle\AppBundle\Entity\VoiceQueueAgent;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\VoiceBundle\Permissions\DepartmentChecker;
use DeskPRO\Bundle\VoiceBundle\Serializer\Model\VoiceQueue as VoiceQueueModel;

/**
 * Class VoiceQueueHandler.
 */
class VoiceQueueHandler extends AbstractEntityHandler
{
    /**
     * @var DepartmentChecker
     */
    private $departmentChecker;

    /**
     * Constructor.
     *
     * @param DepartmentChecker $departmentChecker
     */
    public function __construct(DepartmentChecker $departmentChecker)
    {
        $this->departmentChecker = $departmentChecker;
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
            return $this->departmentChecker->canBeMemberOfVoiceQueue($entity, $queueAgent->getAgent());
        }));

        return $model;
    }
}
