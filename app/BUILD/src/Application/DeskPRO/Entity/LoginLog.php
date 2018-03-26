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
 * Log of agent and admin logins.
 */
class LoginLog extends \Application\DeskPRO\Domain\DomainObject
{
    const AREA_ADMIN = 'admin';
    const AREA_AGENT = 'agent';

    /**
     * @var int
     */
    protected $id = null;

    /**
     * Who the login log is for.
     *
     * @var string
     */
    protected $person;

    /**
     * Where they logged into.
     *
     * @var string
     */
    protected $area;

    /**
     * If the login was successful or failure.
     *
     * @var bool
     */
    protected $is_success = true;

    /**
     * The IP address of the user.
     *
     * @var string
     */
    protected $ip_address;

    /**
     * The traced hostname of the IP address.
     *
     * @var string
     */
    protected $hostname;

    /**
     * The user agent of the user.
     *
     * @var string
     */
    protected $user_agent;

    /**
     * @var string
     */
    protected $note = '';

    /**
     * @var bool
     */
    protected $via_cookie = false;

    /**
     * The date the login was attempted.
     *
     * @var \DateTime
     */
    protected $date_created;

    public function __construct()
    {
        $this->date_created = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\\DeskPRO\\EntityRepository\\LoginLog';
        $metadata->setPrimaryTable(['name' => 'login_log']);
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
            'fieldName'  => 'area',
            'type'       => 'string',
            'length'     => 20,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'area',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_success',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_success',
        ]);
        $metadata->mapField([
            'fieldName'  => 'ip_address',
            'type'       => 'string',
            'length'     => 20,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'ip_address',
        ]);
        $metadata->mapField([
            'fieldName'  => 'hostname',
            'type'       => 'string',
            'length'     => 20,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'hostname',
        ]);
        $metadata->mapField([
            'fieldName'  => 'user_agent',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'user_agent',
        ]);
        $metadata->mapField([
            'fieldName'  => 'note',
            'type'       => 'string',
            'length'     => 1000,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'note',
        ]);
        $metadata->mapField([
            'fieldName'  => 'via_cookie',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'via_cookie',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
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
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
