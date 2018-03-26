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
 * Attributes or various other fields that are searchable on some type.
 */
class ContentSearchAttribute extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var string
     */
    protected $object_type;

    /**
     * @var int
     */
    protected $object_id = null;

    /**
     * The name of the attribute like "somefield".
     */
    protected $attribute_id;

    /**
     * The searchable content of the attribuet.
     */
    protected $content;

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(['name' => 'content_search_attribute']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'object_type',
            'type'       => 'string',
            'length'     => 100,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'object_type',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'object_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'object_id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'attribute_id',
            'type'       => 'string',
            'length'     => 200,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'attribute_id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'content',
            'type'       => 'string',
            'length'     => 200,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'content',
            'id'         => true,
        ]);
    }
}
