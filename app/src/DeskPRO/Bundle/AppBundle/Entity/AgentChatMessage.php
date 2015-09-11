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
use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Class AgentChat
 */
class AgentChatMessage extends DomainObject
{
    /**
     * @var integer
     */
    protected $id;
    /**
     * @var int
     */
    protected $agent_chat_id;
    /**
     * @var AgentChat
     */
    protected $chat;
    /**
     * @var int
     */
    protected $person_id;
    /**
     * @var Person|null
     */
    protected $person;
    /**
     * @var string
     */
    protected $person_name;
    /**
     * @var string
     */
    protected $message;
    /**
     * @var array
     */
    protected $metadata;
    /**
     * @var \DateTime
     */
    protected $date_created;
    public function __construct()
    {
        $this->date_created = new \DateTime();
    }
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }
    /**
     * @return Person|null
     */
    public function getPerson()
    {
        return $this->person;
    }
    public function setPerson(Person $person)
    {
        $this->person = $person;
        $this->person_name = $person->getDisplayName();
        return $this;
    }
    public function getPersonName()
    {
        return $this->person_name;
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
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
    /**
     * @param string $message
     *
     * @return $this
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
    /**
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
    /**
     * @param array $metadata
     *
     * @return $this
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
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
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\AgentChatMessage';
        $metadata->setPrimaryTable(array('name' => 'agent_chat_message',));
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
        $metadata->mapField(array('fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ));
        $metadata->mapField(array('fieldName'  => 'agent_chat_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'agent_chat_id',
        ));
        $metadata->mapField(array('fieldName'  => 'person_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'person_id',
        ));
        $metadata->mapField(array('fieldName'  => 'person_name',
            'type'       => 'string',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'person_name',
        ));
        $metadata->mapField(array('fieldName'  => 'message',
            'type'       => 'string',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'message',
        ));
        $metadata->mapField(array('fieldName'  => 'metadata',
            'type'       => 'json_array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'metadata',
        ));
        $metadata->mapField(array('fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ));
        $metadata->mapManyToOne(array('fieldName'    => 'chat',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AgentChat',
            'mappedBy'     => 'messages',
            'inversedBy'   => null,
            'joinColumns'  => array(0 => array('name'                 => 'agent_chat_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'cascade',
                'columnDefinition'     => NULL,
            ),
            ),
        ));
        $metadata->mapManyToOne(array('fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => array(0 => array('name'                 => 'person_id',
                'referencedColumnName' => 'id',
                'nullable'             => true,
                'onDelete'             => 'set null',
                'columnDefinition'     => NULL,
            ),
            ),
        ));
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}