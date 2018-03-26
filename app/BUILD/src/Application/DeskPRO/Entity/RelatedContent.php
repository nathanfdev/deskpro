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
 * Related content.
 */
class RelatedContent extends \Application\DeskPRO\Domain\DomainObject
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
     * @var string
     */
    protected $rel_object_type;

    /**
     * @var int
     */
    protected $rel_object_id = null;

    public function setRelation($entity1, $entity2)
    {
        $this['object_type'] = $entity1->getTableName();
        $this['object_id']   = $entity1->getId();

        $this['rel_object_type'] = $entity2->getTableName();
        $this['rel_object_id']   = $entity2->getId();
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\RelatedContent';
        $metadata->setPrimaryTable(['name' => 'related_content']);
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
            'fieldName'  => 'rel_object_type',
            'type'       => 'string',
            'length'     => 100,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'rel_object_type',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'rel_object_id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'rel_object_id',
            'id'         => true,
        ]);
    }
}
