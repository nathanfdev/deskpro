<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class ApiKeyRateLimit extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var \Application\DeskPRO\Entity\ApiKey
     */
    protected $api_key = null;

    /** @var int */
    protected $hits = 0;
    /** @var int */
    protected $created_stamp;
    /** @var int */
    protected $reset_stamp;

    public function __construct()
    {
        $this->setModelField('created_stamp', time());
        $this->setModelField('reset_stamp', $this->created_stamp + 3600);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Basic';
        $metadata->setPrimaryTable([
            'name' => 'api_key_rate_limit',
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'hits',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'hits',
        ]);
        $metadata->mapField([
            'fieldName'  => 'created_stamp',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'created_stamp',
        ]);
        $metadata->mapField([
            'fieldName'  => 'reset_stamp',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'reset_stamp',
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'api_key',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\ApiKey',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'api_key_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
            'id' => true,
        ]);
    }
}
