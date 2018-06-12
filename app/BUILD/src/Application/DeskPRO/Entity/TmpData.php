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
class TmpData extends \Application\DeskPRO\Domain\DomainObject
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
     * The authcode for the session to verify an id.
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
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var \DateTime
     */
    protected $date_expire;

    /**
     * @param string     $type
     * @param array      $data
     * @param string|int $expire Seconds until expire or a relative date string like '+1 days'
     *
     * @return \Application\DeskPRO\Entity\TmpData
     */
    public static function create($type, array $data = [], $expire = '+1 week', $name = null)
    {
        $tmpdata = new self();
        $tmpdata->setType($type);

        foreach ($data as $k => $v) {
            $tmpdata->setData($k, $v);
        }

        if (ctype_digit($expire)) {
            $tmpdata['date_expire'] = new \DateTime('@'.(time() + $expire));
        } else {
            $tmpdata['date_expire'] = new \DateTime($expire);
        }

        if ($name) {
            $tmpdata->name = $name;
        }

        return $tmpdata;
    }

    public function __construct()
    {
        $this->auth           = DpStrings::random(15, Strings::CHARS_KEY);
        $this['date_created'] = new \DateTime();
        $this['date_expire']  = new \DateTime('+1 week');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAuth()
    {
        return $this->auth;
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
     * Set the type.
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->setData('_type', $type);
    }

    /**
     * @param \DateTime $dateExpire
     *
     * @return $this
     */
    public function setDateExpire(\DateTime $dateExpire = null)
    {
        $this->setModelField('date_expire', $dateExpire);

        return $this;
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
     *
     * @return $this
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

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return Util::baseEncode($this->id, Util::LETTERS_ALPHABET).'-'.$this->auth;
    }

    /**
     * @return \DateTime
     */
    public function getDateExpire()
    {
        return $this->date_expire;
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

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TmpData';
        $metadata->setPrimaryTable([
            'name'    => 'tmp_data',
            'indexes' => [
                'name_idx'        => ['columns' => ['name']],
                'date_expire_idx' => ['columns' => ['date_expire']],
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
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_expire',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_expire',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
