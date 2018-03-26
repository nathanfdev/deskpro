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
 * Associates an import word with a piece of content. When someone searches
 * for the word, the content is displayed a the top of result listings.
 */
class SearchStickyResult extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var string
     */
    protected $word;

    /**
     * @var string
     */
    protected $object_type;

    /**
     * @var int
     */
    protected $object_id = null;

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\SearchStickyResult';
        $metadata->setPrimaryTable(['name' => 'search_sticky_result']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'word',
            'type'       => 'string',
            'length'     => 150,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'word',
            'id'         => true,
        ]);
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
    }
}
