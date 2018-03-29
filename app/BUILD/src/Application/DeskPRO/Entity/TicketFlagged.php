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
 * Flagged tickets.
 */
class TicketFlagged extends DomainObject
{
    const STAR_BLUE   = 1;
    const STAR_GREEN  = 2;
    const STAR_ORANGE = 3;
    const STAR_PINK   = 4;
    const STAR_PURPLE = 5;
    const STAR_RED    = 6;
    const STAR_YELLOW = 7;

    public static $colorMap = [
        self::STAR_BLUE   => 'blue',
        self::STAR_GREEN  => 'green',
        self::STAR_ORANGE => 'orange',
        self::STAR_PINK   => 'pink',
        self::STAR_PURPLE => 'purple',
        self::STAR_RED    => 'red',
        self::STAR_YELLOW => 'yellow',
    ];

    public static $hexMap = [
        self::STAR_BLUE   => '#0000FF',
        self::STAR_GREEN  => '#008000',
        self::STAR_ORANGE => '#FFA500',
        self::STAR_PINK   => '#FFC0CB',
        self::STAR_PURPLE => '#800080',
        self::STAR_RED    => '#FF0000',
        self::STAR_YELLOW => '#FFFF00',
    ];

    /**
     * @var Ticket
     */
    protected $ticket;

    /**
     * @var int
     */
    protected $person_id = null;

    /**
     * @var string
     */
    protected $color = 'blue';

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $color
     *
     * @return $this
     */
    public function setColor($color)
    {
        $this->setModelField('color', $color);

        return $this;
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function setTicket(Ticket $ticket = null)
    {
        $this->setModelField('ticket', $ticket);

        return $this;
    }

    /**
     * @return int
     */
    public function getPersonId()
    {
        return $this->person_id;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        if ($person) {
            $this->setPersonId($person->getId());
        } else {
            $this->setPersonId(null);
        }

        return $this;
    }

    /**
     * @param int|null $person_id
     *
     * @return $this
     */
    public function setPersonId($person_id)
    {
        $this->setModelField('person_id', $person_id);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketFlagged';
        $metadata->setPrimaryTable(
            [
                'name'    => 'tickets_flagged',
                'indexes' => [
                    // already have a PK index on (person_id, ticket_id),
                    // but need one on just ticket_id as well (used when merging or deleting tickets):
                    'ticket_id_idx' => ['columns' => ['ticket_id']],
                ],
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'person_id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'person_id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'color',
                'type'       => 'string',
                'length'     => 20,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'color',
            ]
        );

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'ticket',
                'targetEntity' => Ticket::class,
                'cascade'      => ['persist'],
                'id'           => true,
                'inversedBy'   => 'stars',
                'joinColumns'  => [
                    [
                        'name'                 => 'ticket_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                    ],
                ],
            ]
        );
    }
}
