<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage ApiBundle
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\DpStrings;
use Orb\Util\Strings;

/**
 * @property int $id
 * @property string $token
 * @property string $scope
 * @property \DateTime|null $date_expires
 * @property Person $person
 */
class ApiToken extends DomainObject
{
    /**
     * Scope used when the token should be acompanied by a session.
     */
    const SCOPE_SESSION = 'session';

    /**
     * This scope is any type of client (eg mobile app)
     * These typicaly dont expire
     */
    const SCOPE_CLIENT = 'client';

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $token;

    /**
     * This is 'where' this token is valid. Right now we use this to scope the token
     * in the admin interface. We say 'admin_interface' which means there must also
     * be an active session for it to be valid. This in turn enforces request tokens
     * on every request which prevents XSS.
     *
     * @var string
     */
    protected $scope;

    /**
     * @var \DateTime|null
     */
    protected $date_expires = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;


    public function __construct()
    {
        $this['token'] = DpStrings::random(25, Strings::CHARS_KEY);
    }


    /**
     * Regenerates a new token
     */
    public function regenerateToken()
    {
        $this['token'] = DpStrings::random(25, Strings::CHARS_KEY);
    }


    /**
     * Get a "key string". This is a combined ID and code like id:code
     * that is used in auth lookups.
     *
     * @return string
     */
    public function getKeyString()
    {
        return $this->id . ':' . $this->token;
    }


    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ApiToken';
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;

        $metadata->setPrimaryTable(array(
            'name' => 'api_token'
        ));

        $metadata->mapField(array(
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ));

        $metadata->mapField(array(
            'fieldName'  => 'token',
            'columnName' => 'token',
            'type'       => 'string',
            'length'     => 25,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'fieldName'  => 'scope',
            'columnName' => 'scope',
            'type'       => 'string',
            'length'     => 50,
            'nullable'   => false,
        ));
        $metadata->mapField(array(
            'fieldName'  => 'date_expires',
            'columnName' => 'date_expires',
            'type'       => 'datetime',
            'nullable'   => true,
        ));

        $metadata->mapManyToOne(array(
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'joinColumns'  => array(array(
                'name'                 => 'person_id',
                'referencedColumnName' => 'id',
                'nullable'             => false,
                'onDelete'             => 'cascade',
            ))
        ));
    }
}
