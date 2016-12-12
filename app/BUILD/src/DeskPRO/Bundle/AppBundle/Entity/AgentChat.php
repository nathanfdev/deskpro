<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
use DeskPRO\Component\Util\ListUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChatRepository")
 * @ORM\Table(name="agent_chat")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 * @JMS\ExclusionPolicy("all")
 */
class AgentChat implements EntityInterface, NotifyPropertyChanged, PersonList
{
    use NotifyPropertyChangedTrait;

    const TYPE_AGENT      = 'agent';
    const TYPE_TEAM       = 'team';
    const TYPE_DEPARTMENT = 'department';
    const TYPE_EVERYONE   = 'everyone';
    const TYPE_GROUP      = 'group';

    /**
     * Id of chat.
     *
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    protected $id;

    /**
     * Current chat type, could be agent, agent_team, department or everyone (one instance per whole helpdesk).
     *
     * @var string
     * @ORM\Column(type="string", length=80)
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\SerializedName("chat_type")
     */
    protected $type;

    /**
     * Indicate is chat archived or not.
     *
     * @var bool
     * @ORM\Column(type="boolean", options={"default" = 0}, nullable=false)
     */
    protected $is_archived = false;

    /**
     * DateTime when chat was first time created.
     *
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     */
    protected $date_created;

    /**
     * Obviously - last message date time.
     *
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     */
    protected $date_last_message;

    /**
     * List of participating in chat entities.
     *
     * @var AgentChatParticipant[] an id array of participants
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AgentChatParticipant", mappedBy="chat", cascade={"persist", "remove"})
     */
    protected $participants;

    /**
     * An array if ids corresponding to chat messages.
     *
     * @var AgentChatMessage[]
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage", mappedBy="chat", cascade={"persist", "remove"})
     * @ORM\OrderBy({"date_created" = "DESC"})
     * @JMS\Type("array")
     */
    protected $messages;

    protected $allowedTypes = [
        self::TYPE_AGENT,
        self::TYPE_TEAM,
        self::TYPE_DEPARTMENT,
        self::TYPE_EVERYONE,
    ];

    /**
     * class constructor, insures that date_created equals now.
     */
    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
        $this->setModelField('date_last_message', new \DateTime());
        $this->setModelField('participants', new ArrayCollection());
        $this->setModelField('messages', new ArrayCollection());
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
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->setModelField('type', $type);

        return $this;
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
        $this->setModelField('archived', (bool) $archived);

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
        return $this->date_last_message;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDateLastMessage(\DateTime $date)
    {
        $this->setModelField('date_last_message', $date);

        return $this;
    }

    /**
     * @return AgentChatParticipant[]
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * Collection of departments participating in this chat.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Department>>")
     *
     * @return Department[]
     */
    public function getDepartments()
    {
        return ListUtils::filterMap($this->participants, function (AgentChatParticipant $p) {
            return $p->getDepartment();
        });
    }

    /**
     * Collection of agent teams participating in this chat.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\AgentTeam>>")
     *
     * @return AgentTeam[]
     */
    public function getAgentTeams()
    {
        return ListUtils::filterMap($this->participants, function (AgentChatParticipant $p) {
            return $p->getTeam();
        });
    }

    /**
     * Collection of agents participating (directly added) in this chat.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("array<entity<Application\DeskPRO\Entity\Person>>")
     *
     * @return Person[]
     */
    public function getAgents()
    {
        return ListUtils::filterMap($this->participants, function (AgentChatParticipant $p) {
            return $p->getPerson();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getPersonList()
    {
        $people    = [];
        $addPerson = function (Person $person = null) use (&$people) {
            if ($person && !array_key_exists($person->getId(), $people)) {
                $people[$person->getId()] = $person;
            }
        };

        foreach ($this->participants as $participant) {
            $addPerson($participant->getPerson());
            if ($participant->getTeam()) {
                foreach ($participant->getTeam()->getPersonList() as $person) {
                    $addPerson($person);
                }
            }
            if ($participant->getDepartment() && $persons = $participant->getDepartment()->getPersonList()) {
                foreach ($persons as $person) {
                    $addPerson($person);
                }
            }
        }

        return array_values($people);
    }

    /**
     * @param $participant
     *
     * @return bool
     */
    public function containsParticipant($participant)
    {
        if ($participant instanceof Person) {
            return in_array($participant, $this->getAgents(), true);
        } elseif ($participant instanceof AgentTeam) {
            return in_array($participant, $this->getAgentTeams());
        } elseif ($participant instanceof Department) {
            return in_array($participant, $this->getDepartments());
        } else {
            throw new \InvalidArgumentException('Unknown participant type');
        }
    }

    /**
     * @param mixed $participant
     *
     * @return $this
     */
    public function addParticipant($participant)
    {
        $chatParticipant = new AgentChatParticipant();
        $chatParticipant->setChat($this);

        if ($participant instanceof Person) {
            $chatParticipant->setPerson($participant);
        } elseif ($participant instanceof AgentTeam) {
            $chatParticipant->setTeam($participant);
        } elseif ($participant instanceof Department) {
            $chatParticipant->setDepartment($participant);
        }

        $this->participants->add($chatParticipant);

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
        $message->setChat($this);
        $this->messages->add($message);
        $this->setModelField('date_last_message', new \DateTime());

        return $this;
    }
}
