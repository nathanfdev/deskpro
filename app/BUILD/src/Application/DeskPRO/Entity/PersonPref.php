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
 * Every person can have various data or preferences associated with their account.
 * These are just key value pairs basically.
 */
class PersonPref extends DomainObject
{
    /**
     * @var Person
     */
    protected $person;

    /**
     * The name of the pref.
     *
     * @var string
     */
    protected $name;

    /**
     * String value.
     *
     * @var string
     */
    protected $value_str = null;

    /**
     * Array value.
     *
     * @var array
     */
    protected $value_array = null;

    /**
     * @var \DateTime
     */
    protected $date_expire = null;

    public function getValue()
    {
        return is_array($this->value_array) ? $this->value_array : $this->value_str;
    }

    /**
     * @param string|array $val
     *
     * @return PersonPref
     */
    public function setValue($val)
    {
        $old_val_str = $this->value_str;
        $old_val_arr = $this->value_array;

        $this->value_str   = null;
        $this->value_array = null;

        if (is_array($val)) {
            $this->value_array = $val;
        } else {
            $this->value_str = (string) $val;
        }

        $this->_onPropertyChanged('value_str', $old_val_str, $this->value_str);
        $this->_onPropertyChanged('value_array', $old_val_arr, $this->value_array);

        return $this;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return PersonPref
     */
    public function setName($name)
    {
        $this->setModelField('name', $name);

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value_str.
     *
     * @param string $valueStr
     *
     * @return PersonPref
     */
    public function setValueStr($valueStr)
    {
        $this->setModelField('value_str', $valueStr);

        return $this;
    }

    /**
     * Get value_str.
     *
     * @return string
     */
    public function getValueStr()
    {
        return $this->value_str;
    }

    /**
     * Set value_array.
     *
     * @param array $valueArray
     *
     * @return PersonPref
     */
    public function setValueArray($valueArray)
    {
        $this->setModelField('value_array', $valueArray);

        return $this;
    }

    /**
     * Get value_array.
     *
     * @return array
     */
    public function getValueArray()
    {
        return $this->value_array;
    }

    /**
     * Set date_expire.
     *
     * @param \DateTime $dateExpire
     *
     * @return PersonPref
     */
    public function setDateExpire($dateExpire)
    {
        $this->setModelField('date_expire', $dateExpire);

        return $this;
    }

    /**
     * Get date_expire.
     *
     * @return \DateTime
     */
    public function getDateExpire()
    {
        return $this->date_expire;
    }

    /**
     * Set person.
     *
     * @param Person $person
     *
     * @return PersonPref
     */
    public function setPerson(Person $person)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * Get person.
     *
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\PersonPref';
        $metadata->setPrimaryTable(['name' => 'people_prefs']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapManyToOne(
            [
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
            ]
        );
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
                'fieldName'  => 'value_str',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'value_str',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'value_array',
                'type'       => 'array',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'value_array',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'date_expire',
                'type'       => 'datetime',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'date_expire',
            ]
        );
        $metadata->setIdentifier([
            'person',
            'name',
        ]);
    }
}
