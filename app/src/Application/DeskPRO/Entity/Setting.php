<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * Settings used by the system.
 *
 * @property int $id
 * @property string $name
 * @property string $value
 * @property \Application\DeskPRO\Entity\Brand $brand
 */
class Setting extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * The name of the setting
     *
     * @var string
     */
    protected $name;

    /**
     * The value of a setting
     *
     * @var string
     */
    protected $value;

    /**
     * The scope this settings is scoped to
     *
     * @var \Application\DeskPRO\Entity\Brand
     */
    protected $brand;

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->mapId();
        $builder->setTable('settings');
        $builder->setCustomRepositoryClass('Application\DeskPRO\EntityRepository\Setting');
        $builder->addUniqueConstraint(array('name', 'brand_id'), 'unique_settings_per_brand');

        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            array(
                'fieldName' => 'name', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0,
                'nullable'  => false, 'columnName' => 'name',
            )
        );
        $metadata->mapField(
            array(
                'fieldName' => 'value', 'type' => 'dpblob', 'length' => -3, 'precision' => 0, 'scale' => 0,
                'nullable'  => true, 'columnName' => 'value',
            )
        );
        $metadata->mapManyToOne(
            array(
                'fieldName'  => 'brand', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Brand', 'mappedBy' => null,
                'inversedBy' => null, 'joinColumns' => array(
                0 => array(
                    'name'     => 'brand_id', 'referencedColumnName' => 'id', 'nullable' => true,
                    'onDelete' => 'cascade', 'columnDefinition' => null,
                ),
            ),
            )
        );
    }
}
