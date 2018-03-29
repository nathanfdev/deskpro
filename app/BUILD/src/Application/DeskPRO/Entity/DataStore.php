<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\DpStrings;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * A general data store.
 */
class DataStore extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * A string name to uniquely identify the record.
     *
     * @var string
     */
    protected $name = null;

    /**
     * The authcode to possibly verify with.
     *
     * @var string
     */
    protected $auth;

    /**
     * Data.
     *
     * @var array
     */
    protected $data = [];

    /**
     * @param string $type
     * @param array  $data
     *
     * @return \Application\DeskPRO\Entity\TmpData
     */
    public static function create($type, array $data = [])
    {
        $ds = new self();
        $ds->setType($type);

        foreach ($data as $k => $v) {
            $ds->setData($k, $v);
        }

        return $ds;
    }

    public function __construct()
    {
        $this->auth = DpStrings::random(15, Strings::CHARS_KEY);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $name
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->getData('_type');
    }

    /**
     * Set the type.
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->setData('_type', $type);
    }

    /**
     * Get some data from the extra array.
     */
    public function getData($key = null, $default = null)
    {
        if ($key === null) {
            return $this->data;
        }

        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    /**
     * Set some data on the extra array.
     *
     * @param  $key
     * @param  $value
     */
    public function setData($key, $value)
    {
        $old = $this->data;
        if ($value === null) {
            unset($this->data[$key]);
        } else {
            $this->data[$key] = $value;
        }

        $this->_onPropertyChanged('data', $old, $this->data);
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return Util::baseEncode($this->id, Util::LETTERS_ALPHABET).'-'.$this->auth;
    }

    /**
     * Splits a code into its id and auth.
     *
     * @param  $code
     *
     * @return array
     */
    public static function getPartsFromCode($code)
    {
        $parts = explode('-', $code, 2);
        if (count($parts) != 2) {
            return;
        }

        $parts[0] = Util::baseDecode($parts[0], Util::LETTERS_ALPHABET);

        return [
            'id'   => $parts[0],
            'auth' => $parts[1],
        ];
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\DataStore';
        $metadata->setPrimaryTable([
            'name'    => 'datastore',
            'indexes' => [
                'name_idx' => ['columns' => ['name']],
            ],
        ]);
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
            'fieldName'  => 'name',
            'type'       => 'string',
            'length'     => 100,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'name',
        ]);
        $metadata->mapField([
            'fieldName'  => 'auth',
            'type'       => 'string',
            'length'     => 15,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'auth',
        ]);
        $metadata->mapField([
            'fieldName'  => 'data',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'data',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
