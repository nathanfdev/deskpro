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
 * @property int    $id
 * @property Ticket $ticket
 * @property Blob   $blob
 */
class TicketProcLog extends DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    protected $ticket = null;

    /**
     * @var \Application\DeskPRO\Entity\Blob
     */
    protected $blob = null;

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

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType      = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->generatorType        = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->changeTrackingPolicy = ClassMetadataInfo::CHANGETRACKING_NOTIFY;

        $metadata->setPrimaryTable([
            'name'    => 'ticket_proc_log',
            'indexes' => [
                'date_created_idx' => ['columns' => ['date_created']],
            ],
        ]);

        $metadata->mapField([
            'id'         => true,
            'fieldName'  => 'id',
            'columnName' => 'id',
            'type'       => 'integer',
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'fieldName'  => 'date_created',
            'columnName' => 'date_created',
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
            'fieldName'    => 'blob',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
            'joinColumns'  => [
                [
                    'name'                 => 'blob_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
