<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */
namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\AgentChat\Exceptions\WrongChatableTypeException;
use DeskPRO\Bundle\AppBundle\AgentChat\Interfaces\Chatable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\PersistentCollection;

/**
 * Class AgentChat
 */
class AgentChat extends DomainObject implements PersonList
{
    /**
     * @var integer
     */
    protected $id;
    /**
     * @var boolean
     */
    protected $is_archived = false;
    /**
     * @var \DateTime
     */
    protected $date_created;
    /**
     * @var \DateTime
     */
    protected $date_last_message;
    /**
     * @var AgentChatParticipant[]
     */
    protected $participants;
    /**
     * @var Person[]
     */
    protected $personList = null;
    /**
     * @var AgentChatMessage[]
     */
    protected $messages;
    /**
     * class constructor, insures that date_created equals now
     */
    public function __construct()
    {
        $this->date_created = new \DateTime();
        $this->date_last_message = new \DateTime();
        $this->participants = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
        if(!$this->personList) {
            $this->personList = array();
            foreach($this->participants as $participant) {
                $list = $participant->getPersonList();
                if(is_array($list)) {
                    $this->personList = array_merge($this->personList, $list);
                } elseif($list instanceof PersistentCollection) {
                    $this->personList = array_merge($this->personList, $list->toArray());
                }
            }
        }
        return $this->personList;
    }
    /**
     * @param Chatable $participantPrototype
     *
     * @return $this
     * @throws WrongChatableTypeException
     */
    public function addParticipant(Chatable $participantPrototype)
    {
        $participant = new AgentChatParticipant();
        $type = $participantPrototype->getChatableType();
        switch($type) {
            case Chatable::PARTICIPANT_TYPE_PERSON;
                /** @var Person $participantPrototype */
                $participant->setPerson($participantPrototype);
                break;
            case Chatable::PARTICIPANT_TYPE_TEAM;
                /** @var AgentTeam $participantPrototype */
                $participant->setTeam($participantPrototype);
                break;
            case Chatable::PARTICIPANT_TYPE_DEPARTMENT;
                /** @var Department $participantPrototype */
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
    ############################################################################
    # Doctrine Metadata
    ############################################################################
    /**
     * @param ClassMetadata $metadata
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\AgentChat';
        $metadata->setPrimaryTable(array('name' => 'agent_chat',));
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
        $metadata->mapField(array('fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ));
        $metadata->mapField(array('fieldName'  => 'is_archived',
            'type'       => 'boolean',
            'default'    => false,
            'nullable'   => false,
            'columnName' => 'is_archived',
        ));
        $metadata->mapField(array('fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ));
        $metadata->mapField(array('fieldName'  => 'date_last_message',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_last_message',
        ));
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapOneToMany(array(
            'fieldName' => 'participants',
            'mappedBy' => 'chat',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AgentChatParticipant',
            'inversedBy'   => null,
            'cascade'      => array('persist', 'remove'), // doesn't work ??? why?
        ));
        $metadata->mapOneToMany(array(
            'fieldName'    => 'messages',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AgentChatMessage',
            'mappedBy'     => 'chat',
            'inversedBy'   => null,
            'orderBy'      => array('date_created' => 'DESC'),
            'cascade'      => array('persist', 'remove'), // doesn't work ??? why?
        ));
    }
}