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
 * Custom ticket data.
 *
 * @property int $id
 * @property \Application\DeskPRO\Entity\CustomFieldDefinition $definition
 * @property \Application\DeskPRO\Entity\CustomFieldDefinition $root_definition
 * @property int $owner_id
 * @property int $value
 * @property string|mixed $input
 * @property DomainObject $owner
 */
class CustomFieldData extends DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\CustomFieldDefinition
     */
    protected $definition;

    /**
     * @var \Application\DeskPRO\Entity\CustomFieldDefinition
     */
    protected $root_definition;

    /**
     * @var int
     */
    protected $owner_id;

    /**
     * @var int
     */
    protected $value = 0;

    /**
     * @var string|mixed input
     */
    protected $input = '';

    /**
     * @var DomainObject
     */
    protected $owner;

    /**
     * @param CustomFieldDefinition $definition
     *
     * @return $this
     */
    public function setDefinition($definition)
    {
        $this->setModelField('definition', $definition);

        return $this;
    }

    /**
     * @return CustomFieldDefinition
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * @param CustomFieldDefinition $root_definition
     *
     * @return $this
     */
    public function setRootDefinition($root_definition)
    {
        $this->setModelField('root_definition', $root_definition);

        return $this;
    }

    /**
     * @return CustomFieldDefinition
     */
    public function getRootDefinition()
    {
        return $this->root_definition;
    }

    /**
     * @param $data
     */
    public function setData($data)
    {
        if (is_int($data)) {
            $this->setModelField('value', $data);
        } else {
            $this->setModelField('input', (string) $data);
        }
    }

    /**
     * @return mixed|string
     */
    public function getData()
    {
        return $this->value ?: $this->input;
    }

    /**
     * @param DomainObject $owner
     *
     * @return $this
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    public function preFlush()
    {
        $this['value'] = (int) $this['value'];
        $this['input'] = (string) $this['input'];

        if ($this->owner && $this->owner['id']) {
            $this['owner_id'] = $this->owner['id'];
        }
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setPrimaryTable([
            'name'              => 'custom_field_data',
            'uniqueConstraints' => [
                'unique_idx' => [
                    'columns' => [
                        'owner_id',
                        'definition_id',
                    ],
                ],
            ],
        ]);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\CustomFieldData';
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->addLifecycleCallback('preFlush', 'preFlush');

        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);

        $metadata->mapField([
            'fieldName'  => 'owner_id',
            'type'       => 'integer',
            'nullable'   => false,
            'columnName' => 'owner_id',
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'definition',
            'targetEntity' => 'Application\DeskPRO\Entity\CustomFieldDefinition',
            'joinColumns'  => [
                [
                    'name'                 => 'definition_id',
                    'referencedColumnName' => 'id',
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'root_definition',
            'targetEntity' => 'Application\DeskPRO\Entity\CustomFieldDefinition',
            'joinColumns'  => [
                [
                    'name'                 => 'root_definition_id',
                    'referencedColumnName' => 'id',
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);

        $metadata->mapField([
            'fieldName'  => 'value',
            'type'       => 'integer',
            'nullable'   => false,
            'columnName' => 'value',
        ]);

        $metadata->mapField([
            'fieldName'  => 'input',
            'type'       => 'text',
            'nullable'   => false,
            'columnName' => 'input',
        ]);
    }
}
