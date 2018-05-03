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
 * General logs.
 */
class LogItem extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * The log group, or "file". Different types of logs can be in different groups for
     * each kind of component (eg. gateways, error_log, etc).
     *
     * @var string
     */
    protected $log_name;

    /**
     * A log 'session'. A way to group many log items together as part of a whole
     * procedure.
     *
     * @var string
     */
    protected $session_name = null;

    /**
     * Any kind of special flag to mark this log item.
     *
     * @var string
     */
    protected $flag = null;

    /**
     * @var int
     */
    protected $priority;

    /**
     * @var string
     */
    protected $priority_name;

    /**
     * The log message.
     *
     * @var string
     */
    protected $message;

    /**
     * Other data, such as backtrace or debug info.
     *
     * @var array
     */
    protected $data = null;

    /**
     * The date the user was inserted into the system.
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

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\LogItem';
        $metadata->setPrimaryTable([
            'name'    => 'log_items',
            'indexes' => [
                'log_name_idx' => [
                    'columns' => [
                        0 => 'log_name',
                        1 => 'session_name',
                    ],
                ],
                'flag_idx' => ['columns' => [0 => 'flag']],
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
            'fieldName'  => 'log_name',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'log_name',
        ]);
        $metadata->mapField([
            'fieldName'  => 'session_name',
            'type'       => 'string',
            'length'     => 100,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'session_name',
        ]);
        $metadata->mapField([
            'fieldName'  => 'flag',
            'type'       => 'string',
            'length'     => 50,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'flag',
        ]);
        $metadata->mapField([
            'fieldName'  => 'priority',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'priority',
        ]);
        $metadata->mapField([
            'fieldName'  => 'priority_name',
            'type'       => 'string',
            'length'     => 25,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'priority_name',
        ]);
        $metadata->mapField([
            'fieldName'  => 'message',
            'type'       => 'text',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'message',
        ]);
        $metadata->mapField([
            'fieldName'  => 'data',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'data',
        ]);
        $metadata->mapField([
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
