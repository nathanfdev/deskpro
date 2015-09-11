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

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Class AgentChat
 */
class AgentChatParticipant extends DomainObject
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var int
     */
    protected $agent_chat_id;
    /**
     * @var int
     */
    protected $person_id;
    /**
     * @var int
     */
    protected $agent_team_id;
    /**
     * @var int
     */
    protected $department_id;
    /**
     * @var AgentChat
     */
    protected $chat;
    /**
     * @var Person
     */
    protected $person;
    /**
     * @var AgentTeam
     */
    protected $team;
    /**
     * @var Department
     */
    protected $department;
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @return AgentChat
     */
    public function getChat()
    {
        return $this->chat;
    }
    /**
     * @param AgentChat $chat
     *
     * @return $this
     */
    public function setChat(AgentChat $chat)
    {
        $this->chat = $chat;
        return $this;
    }
    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }
    /**
     * @return Person[]
     */
    public function getPersonList()
    {
        if($this->person) {
            return array($this->person);
        } elseif($this->team) {
            return $this->team->getPersonList();
        } elseif($this->department) {
            return $this->department->getPersonList();
        } else {
            return null;
        }
    }
    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;
        $this->department = null;
        $this->team = null;
        return $this;
    }
    /**
     * @return AgentTeam
     */
    public function getTeam()
    {
        return $this->team;
    }
    /**
     * @param AgentTeam $team
     *
     * @return $this
     */
    public function setTeam(AgentTeam $team)
    {
        $this->team = $team;
        $this->department = null;
        $this->person = null;
        return $this;
    }
    /**
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }
    /**
     * @param Department $department
     *
     * @return $this
     */
    public function setDepartment(Department $department)
    {
        $this->department = $department;
        $this->person = null;
        $this->team = null;
        return $this;
    }
    /**
     * @param bool $is_admin
     *
     * @return $this
     */
    public function setAdmin($is_admin = false) {
        $this->is_admin = $is_admin;
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
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\AgentChatParticipant';
        $metadata->setPrimaryTable(array('name' => 'agent_chat_participant',));
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapField(array(
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ));
        $metadata->mapField(array(
            'fieldName'  => 'agent_chat_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'agent_chat_id',
        ));
        $metadata->mapField(array(
            'fieldName'  => 'person_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'person_id',
        ));
        $metadata->mapField(array(
            'fieldName'  => 'agent_team_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'agent_team_id',
        ));
        $metadata->mapField(array(
            'fieldName'  => 'department_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'department_id',
        ));
        $metadata->mapManyToOne(array(
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => array(
                0 => array(
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => NULL,
                ),
            ),
        ));
        $metadata->mapManyToOne(array(
            'fieldName'    => 'department',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Department',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => array(
                0 => array(
                    'name'                 => 'department_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => NULL,
                ),
            ),
        ));
        $metadata->mapManyToOne(array(
            'fieldName'    => 'team',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AgentTeam',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => array(
                0 => array(
                    'name'                 => 'agent_team_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => NULL,
                ),
            ),
        ));
        $metadata->mapManyToOne(array(
            'fieldName'    => 'chat',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AgentChat',
            'mappedBy'     => null,
            'inversedBy'   => 'participants',
            'joinColumns'  => array(
                0 => array(
                    'name'                 => 'agent_chat_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => NULL,
                ),
            ),
        ));
    }
}