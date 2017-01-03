<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

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
