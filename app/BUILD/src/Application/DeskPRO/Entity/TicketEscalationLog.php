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
 * @property int $id
 * @property Ticket $ticket
 * @property TicketEscalation $escalation
 * @property \DateTime $date_ran
 * @property \DateTime $date_criteria
 */
class TicketEscalationLog extends DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var Ticket
     */
    protected $ticket;

    /**
     * @var TicketEscalation
     */
    protected $escalation;

    /**
     * The date the trigger was executed on the ticket.
     *
     * @var \DateTime
     */
    protected $date_ran;

    /**
     * The date criteria that caused the trigger execute in the first place.
     * For example, if a ticket is awaiting_user and the trigger is set to run after 1 day,
     * then when the trigger finally runs, date_criteria = ticket.date_awaiting_user.
     *
     * This is used to ensure a trigger doesnt run multiple times in any escalation period. So
     * that executes after 5 minuts doesnt again run after 10, and 15, and 20 etc.
     *
     * @var \DateTime
     */
    protected $date_criteria;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType      = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType        = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->setPrimaryTable([
            'name' => 'ticket_escalation_logs',
        ]);

        $metadata->mapField([
            'id'         => true,
            'fieldName'  => 'id',
            'columnName' => 'id',
            'type'       => 'integer',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_ran',
            'columnName' => 'date_ran',
            'type'       => 'datetime',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_criteria',
            'columnName' => 'date_criteria',
            'type'       => 'datetime',
            'nullable'   => false,
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'ticket',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
            'joinColumns'  => [
                [
                    'name'                 => 'ticket_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'escalation',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketEscalation',
            'joinColumns'  => [
                [
                    'name'                 => 'escalation_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
