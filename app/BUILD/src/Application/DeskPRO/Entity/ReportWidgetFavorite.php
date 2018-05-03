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
 * Report builder favorite.
 */
class ReportWidgetFavorite extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var \Application\DeskPRO\Entity\ReportWidget
     */
    protected $report_widget = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Basic';
        $metadata->setPrimaryTable([
            'name'              => 'report_widget_favorite',
            'uniqueConstraints' => [
                'unique_key_idx' => ['columns' => ['report_widget_id', 'person_id', 'params']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
        $metadata->mapField(['fieldName'       => 'id',
                                  'type'       => 'integer',
                                  'precision'  => 0,
                                  'scale'      => 0,
                                  'nullable'   => false,
                                  'columnName' => 'id',
                                  'id'         => true,
        ]);
        $metadata->mapField(['fieldName'       => 'params',
                                  'type'       => 'string',
                                  'length'     => 100,
                                  'precision'  => 0,
                                  'scale'      => 0,
                                  'nullable'   => false,
                                  'columnName' => 'params',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);

        $metadata->mapManyToOne(['fieldName'         => 'report_widget',
                                      'targetEntity' => 'Application\\DeskPRO\\Entity\\ReportWidget',
                                      'mappedBy'     => null,
                                      'inversedBy'   => null,
                                      'joinColumns'  => [0 => ['name'                           => 'report_widget_id',
                                                                         'referencedColumnName' => 'id',
                                                                         'nullable'             => true,
                                                                         'onDelete'             => 'cascade',
                                                                         'columnDefinition'     => null,
                                      ],
                                      ],
        ]);
        $metadata->mapManyToOne(['fieldName'         => 'person',
                                      'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                                      'mappedBy'     => null,
                                      'inversedBy'   => null,
                                      'joinColumns'  => [0 => ['name'                           => 'person_id',
                                                                         'referencedColumnName' => 'id',
                                                                         'nullable'             => true,
                                                                         'onDelete'             => 'cascade',
                                                                         'columnDefinition'     => null,
                                      ],
                                      ],
        ]);
    }
}
