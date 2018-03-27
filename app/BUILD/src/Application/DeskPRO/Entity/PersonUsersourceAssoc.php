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
 * This tracks associations between a user and a usersource.
 *
 * @property Person $person
 * @property Usersource $usersource
 * @property string $identity
 * @property string $identity_friendly
 * @property mixed  $data
 * @property \DateTime $date_updated
 * @property \DateTime $date_created
 */
class PersonUsersourceAssoc extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * The usersource that this scraper is attached to.
     *
     * @var Usersource
     */
    protected $usersource;

    /**
     * The remote users unique ID. This should not change, so it's smart if this is a system
     * ID such as a UserID.
     *
     * @var string
     */
    protected $identity;

    /**
     * The remote users friendly ID. This is what will be displayed in various interfaces.
     * This should rarely change, like a username. Since we use $identity, we can handle
     * if this changes.
     *
     * @var string
     */
    protected $identity_friendly;

    /**
     * Any raw data returned from the user auth adapter, it might contain useful information
     * such as auth keys (eg: in twitter or facebook).
     *
     * @var array
     */
    protected $data = [];

    /**
     * When the associated person was last "synced" from the remote usersource.
     *
     * @var \DateTime
     */
    protected $date_updated;

    /**
     * When the record was first created in the system.
     *
     * @var \DateTime
     */
    protected $date_created;

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
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     */
    public function setPerson(Person $person)
    {
        $this->setModelField('person', $person);
    }

    /**
     * @return Usersource
     */
    public function getUsersource()
    {
        return $this->usersource;
    }

    /**
     * @param Usersource $usersource
     */
    public function setUsersource(Usersource $usersource)
    {
        $this->setModelField('usersource', $usersource);
    }

    /**
     * @return string
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @param string $identity
     */
    public function setIdentity($identity)
    {
        $this->setModelField('identity', $identity);
    }

    /**
     * @return string
     */
    public function getIdentityFriendly()
    {
        return $this->identity_friendly;
    }

    /**
     * @param string $identity_friendly
     */
    public function setIdentityFriendly($identity_friendly)
    {
        $this->setModelField('identity_friendly', $identity_friendly);
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdated()
    {
        return $this->date_updated;
    }

    /**
     * @param \DateTime $date_updated
     */
    public function setDateUpdated($date_updated)
    {
        $this->setModelField('date_updated', $date_updated);
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
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\PersonUsersourceAssoc';
        $metadata->setPrimaryTable([
            'name'    => 'person_usersource_assoc',
            'indexes' => [
                'identity_idx' => ['columns' => ['identity']],
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
            'fieldName'  => 'identity',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'identity',
        ]);
        $metadata->mapField([
            'fieldName'  => 'identity_friendly',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'identity_friendly',
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
            'fieldName'  => 'date_updated',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'updated_at',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'created_at',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => 'usersource_assoc',
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
        $metadata->mapManyToOne([
            'fieldName'    => 'usersource',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Usersource',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'usersource_id',
                    'referencedColumnName' => 'id',
                    'unique'               => false,
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
