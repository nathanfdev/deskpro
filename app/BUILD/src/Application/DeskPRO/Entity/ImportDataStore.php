<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Stores data from importing for long-term (ie unimplemented features).
 */
class ImportDataStore extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The type of id/thing/whatever this is mapping.
     *
     * @var string
     */
    protected $typename;

    /**
     * @var string
     */
    protected $data = [];

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ImportDataStore';
        $metadata->setPrimaryTable(['name' => 'import_datastore']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'typename',
            'type'       => 'dpblob',
            'length'     => 80,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'typename',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'data',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'data',
        ]);
    }
}
