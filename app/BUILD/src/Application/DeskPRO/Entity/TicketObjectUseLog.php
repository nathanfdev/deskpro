<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * A log of "things" used in tickets.
 */
class TicketObjectUseLog extends DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var int
     * @var \Application\DeskPRO\Entity\Ticket
     */
    protected $ticket;

    /**
     * @var int
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var string
     */
    protected $object_type;

    /**
     * @var int
     * @var \Application\DeskPRO\Entity\TextSnippet
     */
    protected $snippet;

    /**
     * @var int
     * @var \Application\DeskPRO\Entity\TicketMacro
     */
    protected $macro;

    /**
     * @param Ticket      $ticket
     * @param Person      $person
     * @param TextSnippet $snippet
     *
     * @return TicketObjectUseLog
     */
    public static function createSnippetLog(Ticket $ticket, Person $person, TextSnippet $snippet)
    {
        return new self($ticket, $person, $snippet, null);
    }

    /**
     * @param Ticket      $ticket
     * @param Person      $person
     * @param TicketMacro $macro
     *
     * @return TicketObjectUseLog
     */
    public static function createMacroLog(Ticket $ticket, Person $person, TicketMacro $macro)
    {
        return new self($ticket, $person, null, $macro);
    }

    /**
     * @param Ticket           $ticket
     * @param Person           $person
     * @param TextSnippet|null $snippet
     * @param TicketMacro|null $macro
     */
    private function __construct(Ticket $ticket, Person $person, TextSnippet $snippet = null, TicketMacro $macro = null)
    {
        $this['ticket']       = $ticket;
        $this['person']       = $person;
        $this['snippet']      = $snippet;
        $this['macro']        = $macro;
        $this['date_created'] = new \DateTime();

        if ($snippet) {
            $this['object_type'] = 'snippet';
        } elseif ($macro) {
            $this['object_type'] = 'macro';
        } else {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @return mixed
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->object_type;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        if ($this->snippet) {
            return $this->snippet;
        } else {
            return $this->macro;
        }
    }

    /**
     * @return mixed
     */
    public function getSnippet()
    {
        return $this->snippet;
    }

    /**
     * @return mixed
     */
    public function getMacro()
    {
        return $this->macro;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketObjectUseLog';
        $metadata->setPrimaryTable([
            'name' => 'ticket_object_use_logs',
        ]);
        $metadata->mapField([
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'object_type',
            'fieldName'  => 'object_type',
            'type'       => 'string',
            'length'     => 100,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'columnName' => 'date_created',
            'type'       => 'datetime',
            'nullable'   => false,
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'cascade'      => [],
            'joinColumns'  => [
                [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'ticket',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
            'cascade'      => [],
            'joinColumns'  => [
                [
                    'name'                 => 'ticket_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'snippet',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TextSnippet',
            'cascade'      => [],
            'joinColumns'  => [
                [
                    'name'                 => 'snippet_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'macro',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketMacro',
            'cascade'      => [],
            'joinColumns'  => [
                [
                    'name'                 => 'macro_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                ],
            ],
        ]);
    }
}
