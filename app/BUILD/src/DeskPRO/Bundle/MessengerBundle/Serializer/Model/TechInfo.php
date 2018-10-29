<?php

namespace DeskPRO\Bundle\MessengerBundle\Serializer\Model;

use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;

/**
 * Class TechInfo.
 */
class TechInfo implements MessengerModelInterface
{
    /**
     * @var array
     */
    private $chatDepartments;

    /**
     * @var array
     */
    private $agentsOnline;

    /**
     * @var AvatarResolver
     */
    private $avatarResolver;

    /**
     * TechInfo constructor.
     *
     * @param AvatarResolver $avatarResolver
     */
    public function __construct(AvatarResolver $avatarResolver)
    {
        $this->avatarResolver = $avatarResolver;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $self            = $this;
        $chatDepartments = array_map(function ($department) use ($self) {
            /* @var Department $department */
            return [
                'title'  => $department->getTitle(),
                'avatar' => $self->avatarResolver->getAvatar($department),
                'id'     => $department->getId(),
            ];
        }, $this->chatDepartments);

        $self         = $this;
        $agentsOnline = array_map(function ($agent) use ($self) {
            /* @var Person $agent */
            return [
                'name'             => $agent->getDisplayNameUser(),
                'id'               => $agent->getId(),
                'avatar'           => $self->avatarResolver->getAvatar($agent),
                'chat_departments' => $agent->getAllowedDepartments('chat'),
            ];
        }, $this->agentsOnline);

        return [
            'chat_departments' => $chatDepartments,
            'agents_online'    => $agentsOnline,
        ];
    }

    /**
     * @param array $agentsOnline
     *
     * @return $this
     */
    public function setAgentsOnline(array $agentsOnline)
    {
        $this->agentsOnline = $agentsOnline;

        return $this;
    }

    /**
     * @param array $chatDepartments
     *
     * @return $this
     */
    public function setChatDepartments(array $chatDepartments)
    {
        $this->chatDepartments = $chatDepartments;

        return $this;
    }
}
