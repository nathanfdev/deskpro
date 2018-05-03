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
 * Password history.
 *
 * @property int $id
 * @property string $password
 * @property string $password_scheme
 * @property Person $person
 * @property \DateTime $date_created
 */
class PasswordHistory extends DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $password_scheme;

    /**
     * @var Person
     */
    protected $person;

    /**
     * @var \DateTime
     */
    protected $date_created;

    public function __construct()
    {
        $this->date_created = new \DateTime();
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\PasswordHistory';
        $metadata->setPrimaryTable(['name' => 'password_history']);
        $metadata->mapField([
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'nullable'   => false,
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'password',
            'columnName' => 'password',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'password_scheme',
            'columnName' => 'password_scheme',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'columnName' => 'date_created',
            'type'       => 'datetime',
            'nullable'   => false,
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'joinColumns'  => [
                [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);
    }
}
