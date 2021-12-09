<?php

namespace DeskPRO\Bundle\MessengerBundle\Serializer\Model;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;

/**
 * Class TechInfo.
 */
class AgentInfo implements MessengerModelInterface
{
    /**
     * @var AvatarResolver
     */
    private $avatarResolver;

    /**
     * @var Person
     */
    private $person;

    /**
     * AgentInfo constructor.
     *
     * @param Person         $person
     * @param AvatarResolver $avatarResolver
     */
    public function __construct(Person $person, AvatarResolver $avatarResolver)
    {
        $this->avatarResolver = $avatarResolver;
        $this->person         = $person;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $defaultAvatar = false;
        $avatar        = $this->avatarResolver->getAvatar($this->person, 80, $defaultAvatar);

        return [
            'name'             => $this->person->getDisplayNameUser(),
            'id'               => $this->person->getId(),
            'avatar'           => $defaultAvatar ? null : $avatar,
            'chat_departments' => array_map('intval', $this->person->getAllowedDepartments('chat')),
        ];
    }
}
