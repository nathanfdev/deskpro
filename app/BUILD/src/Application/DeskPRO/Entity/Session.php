<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Active user sessions.
 *
 * @deprecated - avoid using this as much as possible
 */
class Session extends \Application\DeskPRO\Domain\DomainObject
{
    const STATUS_AVAILABLE = 'available';
    const STATUS_AWAY      = 'away';

    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id;

    /**
     * The authcode for the session to verify an id.
     *
     * @var string
     */
    protected $auth;

    /**
     * The Interface the session is for.
     *
     * @var string
     */
    protected $interface = '';

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * The users user agent string.
     *
     * @var string
     */
    protected $user_agent = null;

    /**
     * @var string
     */
    protected $visitor_id = null;

    /**
     * The users IP address.
     *
     * @var string
     */
    protected $ip_address = null;

    /**
     * @var string
     */
    protected $data = '';

    /**
     * @var bool
     */
    protected $is_person = false;

    /**
     * @var bool
     */
    protected $is_bot = false;

    /**
     * @var bool
     */
    protected $is_helpdesk = false;

    /**
     * (Agents) Status (available or away).
     *
     * @var string
     */
    protected $active_status = 'available';

    /**
     * (Agents) Wehn status is available, if they are available for chat.
     *
     * @var bool
     */
    protected $is_chat_available = false;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var \DateTime
     */
    protected $date_last;

    /**
     * @var \DateTime
     */
    protected $date_last_page;

    /**
     * @var bool
     */
    protected $_is_new = false;

    public function __construct()
    {
        $this->setModelField('auth', Strings::random(15, Strings::CHARS_KEY));
        $this->setModelField('date_created', new \DateTime());
        $this->setModelField('date_last', new \DateTime());
        $this->setModelField('date_last_page', new \DateTime());
        $this->_is_new = true;
    }

    /**
     * @return bool
     */
    public function getIsNew()
    {
        return $this->_is_new;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets the session ID for this session. It's an encoded ID and an authcode.
     *
     * @return string
     */
    public function getSessionCode()
    {
        $id_enc = Util::baseEncode($this->id, Util::BASE36_ALPHABET);

        return $id_enc.'-'.$this->auth;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    public function setAuth($auth_code)
    {
        $this->setModelField('auth', substr($auth_code, 0, 15));
    }

    /**
     * @return string
     */
    public function getAuth()
    {
        return $this->auth;
    }

    public function setData($data)
    {
        $this->setModelField('data', $data);
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * Check a session code against some kind o finput to see
     * if they match.
     *
     * @return bool
     */
    public function checkSessionCode($session_code)
    {
        return $this->getSessionCode() === $session_code;
    }

    public function setPerson(Person $person = null)
    {
        if (!$person) {
            $this->setPersonId(0);
        } else {
            $this->setPersonId($person['id']);
        }
    }

    public function setPersonId($person_id)
    {
        if ($person_id) {
            $this->setModelField('is_person', true);
            $this->setModelField('person', App::getEntityRepository('DeskPRO:Person')->find($person_id));
        } else {
            $this->setModelField('is_person', false);
            $this->setModelField('person', null);
        }
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function getPersonId()
    {
        if ($this->person) {
            return $this->person['id'];
        }

        return 0;
    }

    /**
     * A secret hash of this session key with the app secret.
     *
     * Most notably used as the "proxy key"
     *
     * @param string $name    Another component to add to the hash
     * @param bool   $not_vis True for do not use visitor secret. Default is to use visitor if it exists
     *
     * @return string
     */
    public function getSessionSecret($name = '', $not_vis = false)
    {
        return md5($this->id.$this->auth.App::getAppSecret().$name);
    }

    /**
     * Generate a security token based off of this session.
     *
     * @param     $name
     * @param int $timeout
     *
     * @return string
     */
    public function generateSecurityToken($name = '', $timeout = 43200)
    {
        return Util::generateStaticSecurityToken($this->getSessionSecret($name, true), $timeout);
    }

    /**
     * Check a security token to see if its valid.
     *
     * @param $name
     * @param $token
     *
     * @return bool
     */
    public function checkSecurityToken($name, $token)
    {
        return Util::checkStaticSecurityToken($token, $this->getSessionSecret($name, true));
    }

    public function updateLastTime()
    {
        $this->setModelField('date_last', new \DateTime());
    }

    public static function getIdFromCode($sess_code)
    {
        if (!is_string($sess_code) || !strpos($sess_code, '-')) {
            return;
        }

        list($session_id) = explode('-', $sess_code, 2);

        $session_id = Util::baseDecode($session_id, Util::BASE36_ALPHABET);

        return $session_id;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * @param string $ip
     */
    public function setIpAddress($ip)
    {
        $this->setModelField('ip_address', $ip);
    }

    /**
     * @return string
     */
    public function getVisitorId()
    {
        return $this->visitor_id;
    }

    /**
     * @param string $id
     */
    public function setVisitorId($id)
    {
        $this->setModelField('visitor_id', $id);
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Session';
        $metadata->setPrimaryTable([
            'name'    => 'sessions',
            'indexes' => [
                'date_last_idx' => [
                    'columns' => [
                        0 => 'date_last',
                        1 => 'is_person',
                    ],
                ],
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
            'fieldName'  => 'auth',
            'type'       => 'string',
            'length'     => 15,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'auth',
        ]);
        $metadata->mapField([
            'fieldName'  => 'interface',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'interface',
        ]);
        $metadata->mapField([
            'fieldName'  => 'user_agent',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'user_agent',
        ]);
        $metadata->mapField([
            'fieldName'  => 'ip_address',
            'type'       => 'string',
            'length'     => 80,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'ip_address',
        ]);
        $metadata->mapField(
            [
                'fieldName'  => 'visitor_id',
                'type'       => 'string',
                'length'     => 120,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'visitor_id',
            ]
        );
        $metadata->mapField([
            'fieldName'  => 'data',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'data',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_person',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_person',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_bot',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_bot',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_helpdesk',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_helpdesk',
        ]);
        $metadata->mapField([
            'fieldName'  => 'active_status',
            'type'       => 'string',
            'length'     => 15,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'active_status',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_chat_available',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_chat_available',
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
            'fieldName'  => 'date_last',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_last',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_last_page',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_last_page',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
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
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
