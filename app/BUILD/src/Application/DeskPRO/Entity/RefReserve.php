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
 * Class Ticket.
 *
 * @property string $obj_type
 * @property string $ref
 */
class RefReserve extends DomainObject
{
    /**
     * @var string
     */
    protected $obj_type = null;

    /**
     * @var string
     */
    protected $ref = null;

    /**
     * @var \DateTime
     */
    protected $date_created;

    public function __construct()
    {
        $this->date_created = new \DateTime();
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType      = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType        = ClassMetadataInfo::GENERATOR_TYPE_NONE;
        $metadata->setPrimaryTable([
            'name' => 'ref_reserve',
        ]);

        $metadata->mapField([
            'columnName' => 'obj_type',
            'fieldName'  => 'obj_type',
            'type'       => 'string',
            'length'     => 50,
            'nullable'   => false,
            'id'         => true,
        ]);
        $metadata->mapField([
            'columnName' => 'ref',
            'fieldName'  => 'ref',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'columnName' => 'date_created',
            'type'       => 'datetime',
            'nullable'   => false,
        ]);
    }
}
