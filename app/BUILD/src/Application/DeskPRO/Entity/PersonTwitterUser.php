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
 * A persons contact data.
 */
class PersonTwitterUser extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * @var bigint
     */
    protected $twitter_user_id;

    /**
     * @var string
     */
    protected $screen_name;

    /**
     * @var bool
     */
    protected $is_verified = false;

    /**
     * @var string|null
     */
    protected $oauth_token = null;
    /**
     * @var string|null
     */
    protected $oauth_token_secret = null;

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\PersonTwitterUser';
        $metadata->setPrimaryTable([
            'name'              => 'people_twitter_users',
            'uniqueConstraints' => [
                'unique_key_idx' => [
                    'columns' => [
                        'person_id',
                        'screen_name',
                    ],
                ],
            ],
            'indexes' => [
                'screen_name_idx'     => ['columns' => ['screen_name']],
                'twitter_user_id_idx' => ['columns' => ['twitter_user_id']],
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
            'fieldName'  => 'screen_name',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'screen_name',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_verified',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_verified',
        ]);
        $metadata->mapField([
            'fieldName'  => 'oauth_token',
            'type'       => 'string',
            'length'     => 4000,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'oauth_token',
            'dpApi'      => false,
            'dpqlAccess' => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'oauth_token_secret',
            'type'       => 'string',
            'length'     => 4000,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'oauth_token_secret',
            'dpApi'      => false,
            'dpqlAccess' => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'twitter_user_id',
            'type'       => 'bigint',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'twitter_user_id',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => 'twitter_users',
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
