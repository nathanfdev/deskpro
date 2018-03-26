<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity;

use Application\DeskPRO\Entity\AgentTeam;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\Serializer\Model\AgentTeam as SerializedAgentTeam;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class AgentTeamHandler.
 */
class AgentTeamHandler extends AbstractEntityHandler
{
    /**
     * @var AvatarResolver
     */
    private $avatarResolver;

    /**
     * Constructor.
     *
     * @param AvatarResolver $avatarResolver
     */
    public function __construct(AvatarResolver $avatarResolver)
    {
        $this->avatarResolver = $avatarResolver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return AgentTeam::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param AgentTeam $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $agent_team = new SerializedAgentTeam($entity);
        $agent_team->setAvatar($this->avatarResolver->getAvatarModel($entity));

        return $agent_team;
    }
}
