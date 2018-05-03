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
 * A result cache is a cached result from a search or filter.
 */
class ResultCache extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * Criteria information like what the user searched for.
     *
     * @var array
     */
    protected $criteria = [];

    /**
     * An array of results.
     *
     * @var array
     */
    protected $results = [];

    /**
     * Any extra data.
     *
     * @var array
     */
    protected $extra = [];

    /**
     * @var int
     */
    protected $num_results = 0;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var string
     */
    protected $results_type;

    public function __construct()
    {
        $this['date_created'] = new \DateTime();
    }

    /**
     * Get some data from the extra array.
     */
    public function getExtraData($key, $default = null)
    {
        return isset($this->extra[$key]) ? $this->extra[$key] : $default;
    }

    /**
     * Set some data on the extra array.
     *
     * @param  $key
     * @param  $value
     */
    public function setExtraData($key, $value)
    {
        $old = $this->extra;

        if ($value === null) {
            unset($this->extra[$key]);
        } else {
            $this->extra[$key] = $value;
        }

        $this->_onPropertyChanged('extra', $old, $this->extra);
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    public function getPersonId()
    {
        if (!$this->person) {
            return 0;
        }

        return $this->person['id'];
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(['name' => 'result_cache']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'criteria',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'criteria',
        ]);
        $metadata->mapField([
            'fieldName'  => 'results',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'results',
        ]);
        $metadata->mapField([
            'fieldName'  => 'extra',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'extra',
        ]);
        $metadata->mapField([
            'fieldName'  => 'num_results',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'num_results',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'results_type',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'results_type',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => 'preferences',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
