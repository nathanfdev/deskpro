<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\AgentChat\Exceptions\WrongChatableTypeException;
use DeskPRO\Bundle\AppBundle\AgentChat\Interfaces\Chatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\PersistentCollection;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChat")
 * @ORM\Table(name="agent_chat")
 * @ORM\ChangeTrackingPolicy("DEFERRED_IMPLICIT")
 * @ORM\InheritanceType("NONE")
 *
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route("get_agent_chats", parameters={"id" = "expr(object.getId())"})
 * )
 */
class AgentChat implements PersonList, EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=80)
     */
    protected $type;

    /**
     * @var bool
     * @ORM\Column(type="boolean", options={"default" = 0}, nullable=false)
     * @Assert\NotNull()
     */
    protected $is_archived = false;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\NotNull()
     */
    protected $date_created;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\NotNull()
     */
    protected $date_last_message;

    /**
     * @var AgentChatParticipant[]
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AgentChatParticipant", mappedBy="chat", cascade={"persist", "remove"})
     */
    protected $participants;

    /**
     * @var Person[]
     */
    protected $personList = null;

    /**
     * @var AgentChatMessage[]
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage", mappedBy="chat", cascade={"persist", "remove"})
     * @ORM\OrderBy({"date_created" = "DESC"})
     */
    protected $messages;

    protected $allowedTypes = [
        Chatable::PARTICIPANT_TYPE_AGENT,
        Chatable::PARTICIPANT_TYPE_TEAM,
        Chatable::PARTICIPANT_TYPE_DEPARTMENT,
        Chatable::PARTICIPANT_TYPE_GROUP,
        Chatable::PARTICIPANT_TYPE_EVERYONE,
    ];

    /**
     * class constructor, insures that date_created equals now.
     */
    public function __construct()
    {
        $this->date_created      = new \DateTime();
        $this->date_last_message = new \DateTime();
        $this->participants      = new ArrayCollection();
        $this->messages          = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        if (!in_array($type, $this->allowedTypes)) {
            $err = 'Used invalid Chatable type in AgentChat::setType. Should be one of %s';
            throw new WrongChatableTypeException(sprintf($err, implode(',', $this->allowedTypes)));
        }
        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function isArchived()
    {
        return $this->is_archived;
    }

    /**
     * @param bool $archived
     *
     * @return $this
     */
    public function setArchived($archived = true)
    {
        $this->is_archived = (bool) $archived;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @return \DateTime
     */
    public function getDateLastMessage()
    {
        return $this->date_created;
    }

    /**
     * @return AgentChatParticipant[]
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    public function getPersonList()
    {
        if (!$this->personList) {
            $this->personList = array();
            foreach ($this->participants as $participant) {
                $list = $participant->getPersonList();
                if (is_array($list)) {
                    $this->personList = array_merge($this->personList, $list);
                } elseif ($list instanceof PersistentCollection) {
                    $this->personList = array_merge($this->personList, $list->toArray());
                }
            }
        }

        return $this->personList;
    }

    /**
     * @param Chatable $participantPrototype
     *
     * @throws WrongChatableTypeException
     *
     * @return $this
     */
    public function addParticipant(Chatable $participantPrototype)
    {
        $participant = new AgentChatParticipant();
        $type        = $participantPrototype->getChatableType();
        switch ($type) {
            case Chatable::PARTICIPANT_TYPE_AGENT;
                /* @var Person $participantPrototype */
                $participant->setPerson($participantPrototype);
                break;
            case Chatable::PARTICIPANT_TYPE_TEAM;
                /* @var AgentTeam $participantPrototype */
                $participant->setTeam($participantPrototype);
                break;
            case Chatable::PARTICIPANT_TYPE_DEPARTMENT;
                /* @var Department $participantPrototype */
                $participant->setDepartment($participantPrototype);
                break;
            default:
                throw new WrongChatableTypeException();
        }
        $participant->setChat($this);
        $this->participants->add($participant);

        return $this;
    }

    /**
     * @return AgentChatMessage[]|ArrayCollection
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param AgentChatMessage $message
     *
     * @return $this
     */
    public function addMessage(AgentChatMessage $message)
    {
        $this->messages->add($message);
        $this->date_last_message = new \DateTime();
        $message->setChat($this);

        return $this;
    }
}
