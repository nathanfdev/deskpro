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
     * These typicaly dont expire.
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
     * Regenerates a new token.
     */
    public function regenerateToken()
    {
        $this['token'] = DpStrings::random(25, Strings::CHARS_KEY);
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken($token)
    {
        $this->setModelField('token', $token);

        return $this;
    }

    /**
     * Get a "key string". This is a combined ID and code like id:code
     * that is used in auth lookups.
     *
     * @return string
     */
    public function getKeyString()
    {
        return $this->id.':'.$this->token;
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
     *
     * @return $this
     */
    public function setPerson($person)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     *
     * @return $this
     */
    public function setScope($scope)
    {
        $this->setModelField('scope', $scope);

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateExpires()
    {
        return $this->date_expires;
    }

    /**
     * @param \DateTime|null $date_expires
     *
     * @return $this
     */
    public function setDateExpires(\DateTime $date_expires = null)
    {
        $this->setModelField('date_expires', $date_expires);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ApiToken';
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;

        $metadata->setPrimaryTable([
            'name' => 'api_token',
        ]);

        $metadata->mapField([
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'fieldName'  => 'token',
            'columnName' => 'token',
            'type'       => 'string',
            'length'     => 25,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'scope',
            'columnName' => 'scope',
            'type'       => 'string',
            'length'     => 50,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_expires',
            'columnName' => 'date_expires',
            'type'       => 'datetime',
            'nullable'   => true,
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'person',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'joinColumns'  => [
                [
                    'name'                 => 'person_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);
    }
}
