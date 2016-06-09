<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

    ############################################################################
    # Doctrine Metadata
    ############################################################################

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
