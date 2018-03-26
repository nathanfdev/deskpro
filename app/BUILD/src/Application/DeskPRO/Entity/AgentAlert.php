<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Arrays;

class AgentAlert extends \Application\DeskPRO\Domain\DomainObject
{
    const TARGET_BROWSER = 'browser';
    const TARGET_MOBILE  = 'mobile';

    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var string
     */
    protected $typename;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var bool
     */
    protected $is_dismissed = false;

    public function __construct()
    {
        $this->setModelField('date_created', new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get data. Specify $target to optionally get only data to deliver to a $target type.
     *
     * @param string|null $target
     *
     * @return array
     */
    public function getData($target = null)
    {
        if ($target === null || empty($this->data['@target_maps']) || empty($this->data['@target_maps'][$target])) {
            return $this->data;
        }

        $ret = Arrays::reduceToKeys($this->data, $this->data['@target_maps'][$target], null);
        if (!empty($this->data['@target_maps']['default'])) {
            $ret = array_merge($ret, Arrays::reduceToKeys($this->data, $this->data['@target_maps']['default'], null));
        }

        return $ret;
    }

    /**
     * Add a target map.
     *
     * A target map specifies the keys in data that sholud be returned for a specific alert target.
     * For example, if this is a newticket alert then we might want to deliver different data
     * to the client depending on if the client is a browser or if they are a mobile device.
     *
     * @param string $target
     * @param array  $keys
     */
    public function addTargetMap($target, array $keys)
    {
        if (!isset($this->data['@target_maps'])) {
            $this->data['@target_maps'] = [];
        }

        $this->data['@target_maps'][$target] = $keys;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @return string
     */
    public function getTypename()
    {
        return $this->typename;
    }

    /**
     * @param Person $person
     */
    public function setPerson(Person $person = null)
    {
        $this->person = $person;
    }

    /**
     * @param string $typename
     */
    public function setTypename($typename)
    {
        $this->typename = $typename;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isDismissed()
    {
        return $this->is_dismissed;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->setPrimaryTable([
            'name'    => 'agent_alerts',
            'indexes' => [
                'date_created_idx' => ['columns' => ['date_created']],
                'is_dismissed_idx' => [
                    'columns' => [
                        'is_dismissed',
                        'date_created',
                    ],
                ],
            ],
        ]);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'typename',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
            'columnName' => 'typename',
        ]);
        $metadata->mapField([
            'fieldName'  => 'data',
            'type'       => 'array',
            'nullable'   => false,
            'columnName' => 'data',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_dismissed',
            'type'       => 'boolean',
            'nullable'   => false,
            'columnName' => 'is_dismissed',
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
