<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Person;

use Application\DeskPRO\Entity\Person as PersonEntity;
use DeskPRO\Bundle\AppBundle\Content\Avatar;
use DeskPRO\Bundle\AppBundle\Entity\AgentData;
use JMS\Serializer\Annotation as JMS;

/**
 * Class BasePerson.
 */
class BasePerson
{
    /**
     * The unique ID of person.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * Main user`s email.
     *
     * @JMS\Type("to_string<Application\DeskPRO\Entity\PersonEmail>")
     *
     * @var string
     */
    protected $primaryEmail;

    /**
     * The users name (best guess from other sources etc).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $firstName;

    /**
     * The users name (best guess from other sources etc).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $lastName;

    /**
     * The users name (best guess from other sources etc).
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $name;

    /**
     * Person display name.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $displayName;

    /**
     * True if person is agent.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isAgent;

    /**
     * Person`s avatar.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Content\Avatar")
     *
     * @var Avatar
     */
    protected $avatar;

    /**
     * Is user online?
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $online;

    /**
     * Is user online for chat?
     *
     * @JMS\Type("deferred<boolean>")
     *
     * @var bool
     */
    protected $onlineForChat;

    /**
     * Date when user was last seen online.
     *
     * @JMS\Type("deferred<DateTime>")
     *
     * @var \DateTime
     */
    protected $lastSeen;

    /**
     * Agent data.
     *
     * @JMS\Type("deferred<DeskPRO\Bundle\AppBundle\Entity\AgentData>")
     *
     * @var AgentData
     */
    protected $agentData;

    /**
     * PersonEntity constructor.
     *
     * @param PersonEntity $person
     * @param Avatar       $avatar
     */
    public function __construct(PersonEntity $person, Avatar $avatar)
    {
        $this->id           = $person->getId();
        $this->firstName    = $person->getFirstName();
        $this->lastName     = $person->getLastName();
        $this->titlePrefix  = $person->getTitlePrefix();
        $this->name         = $person->getName();
        $this->displayName  = $person->getDisplayNameUser();
        $this->isAgent      = $person->isAgent();
        $this->primaryEmail = $person->getPrimaryEmail();
        $this->avatar       = $avatar;
    }

    /**
     * @param bool $online
     *
     * @return $this
     */
    public function setOnline($online)
    {
        $this->online = $online;

        return $this;
    }

    /**
     * @param $onlineForChat
     *
     * @return $this
     */
    public function setOnlineForChat($onlineForChat)
    {
        $this->onlineForChat = $onlineForChat;

        return $this;
    }

    /**
     * @param $lastSeen
     *
     * @return $this
     */
    public function setLastSeen($lastSeen = null)
    {
        $this->lastSeen = $lastSeen;

        return $this;
    }

    /**
     * @param $agentData
     *
     * @return $this
     */
    public function setAgentData($agentData = null)
    {
        $this->agentData = $agentData;

        return $this;
    }
}
