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

class RateLimitLog extends DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $action;

    /**
     * @var int
     */
    protected $ip;

    /**
     * @var int
     */
    protected $person_id;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /** @var bool */
    protected $is_lockout = false;

    /**
     * RateLimitLog constructor.
     */
    public function __construct()
    {
        $this->date_created = new \DateTime();
    }

    /**
     * @param string $ip
     *
     * @return $this
     */
    public function setIp($ip)
    {
        $this->setModelField('ip', $ip);

        return $this;
    }

    /**
     * @param string $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->setModelField('action', $action);

        return $this;
    }

    /**
     * @param $lockout
     *
     * @return $this
     */
    public function setIsLockout($lockout)
    {
        $this->setModelField('lockout', $lockout);

        return $this;
    }

    /**
     * @param int $person_id
     *
     * @return $this
     */
    public function setPersonId($person_id)
    {
        $this->setModelField('person_id', (int) $person_id);

        return $this;
    }

    /**
     * @param \DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $date_created)
    {
        $this->setModelField('date_created', $date_created);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    /**
     * @param ClassMetadata $metadata
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->customRepositoryClassName = 'Application\\DeskPRO\\EntityRepository\\RateLimitLog';

        $metadata->setPrimaryTable([
            'name'    => 'rate_limit_log',
            'indexes' => [
                'search_idx' => [
                    'columns' => [
                        'action',
                        'date_created',
                        'ip',
                    ],
                ],
            ],
        ]);

        $metadata->mapField([
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'action',
            'fieldName'  => 'action',
            'type'       => 'string',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'ip',
            'fieldName'  => 'ip',
            'type'       => 'string',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'person_id',
            'fieldName'  => 'person_id',
            'type'       => 'integer',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'date_created',
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'columnName' => 'is_lockout',
            'fieldName'  => 'is_lockout',
            'type'       => 'boolean',
            'nullable'   => false,
        ]);
    }
}
