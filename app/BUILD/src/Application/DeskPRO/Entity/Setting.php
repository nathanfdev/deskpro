<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Settings used by the system.
 *
 * @property string $name
 * @property string $value
 * @property \Application\DeskPRO\Entity\Brand $brand
 */
class Setting extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The name of the setting.
     *
     * @var string
     *
     * @Assert\NotBlank()
     */
    protected $name;

    /**
     * The value of a setting.
     *
     * @var string
     *
     * @Assert\NotNull()
     */
    protected $value;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->setModelField('name', $name);

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->setModelField('value', $value);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('settings');
        $builder->setCustomRepositoryClass('Application\DeskPRO\EntityRepository\Setting');
        $builder->addUniqueConstraint(['name'], 'unique_setting_name');

        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField(
            [
                'fieldName'  => 'name',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'name',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'value',
                'type'       => 'dpblob',
                'length'     => -3,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'value',
            ]
        );
    }
}
