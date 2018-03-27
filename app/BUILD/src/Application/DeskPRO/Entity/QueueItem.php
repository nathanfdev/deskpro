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
 * A QueueItem is some piece of work that has been deferred for later.
 * Note that if an alternate queue system is being used, then this table
 * is still used in cases where additional information must be stored.
 *
 * For example, in the case of queue servers that store queues in memory
 * (such as beanstalkd) it's not a good feedback to store large amounts of data
 * in the task. So instead, we simply store the QueueItem ID and the task
 * worker can fetch the data when it processes the task.
 */
class QueueItem extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * The unique ID.
     *
     * @var int
     */
    protected $id = null;

    /**
     * A group that the queue item is part of. This is a way to classify different kinds of jobs.
     *
     * In beanstalkd terminology: tube
     *
     * @var string
     */
    protected $groupname;

    /**
     * The priority of this job.
     *
     * @var int
     */
    protected $priority = 0;

    /**
     * Don't process this item until this date.
     *
     * @var \DateTime
     */
    protected $delay_until = null;

    /**
     * Time To Run. The maximum amount of time to allow a worker to run this job.
     * If the job isn't deletes, buried or released after this many seconds, then
     * the job will time-out and re-enter the work queue.
     *
     * The minimum value is 1.
     *
     * @var int
     */
    protected $ttr = 60;

    /**
     * When this is true, the job is ready to be reserved.
     *
     * @var bool
     */
    protected $is_ready = true;

    /**
     * Is this item used only for queue datas? Useful if the database queue is used
     * alongside other queue systems that are using this as a store for data.
     *
     * @var bool
     */
    protected $is_dataonly = false;

    /**
     * Should this job be ignored and not run automatically?
     * In other words, the job wont be run until something (someone?) unignores it.
     *
     * In beanstalkd terminology: buried
     *
     * @var bool
     */
    protected $is_ignored = false;

    /**
     * If the job is reserved, this is the time it was reserved at.
     * Once reserved, the $is_ready becomes false because no other workers
     * should use this job.
     *
     * @var \DateTime
     */
    protected $reserved_at = null;

    /**
     * When a job is reserved, this should be the time the job should expire.
     * That is, $reserved_at+$ttr.
     *
     * @var \DateTime
     */
    protected $timeout_at = null;

    /**
     * When this job was created.
     *
     * @var \DateTime
     */
    protected $created_at = null;

    /**
     * Any data pertaining to the job.
     *
     * @var string
     */
    protected $data = [];

    public function __construct()
    {
        $this['created_at'] = new \DateTime();
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
        $metadata->setPrimaryTable([
            'name'    => 'queue_items',
            'indexes' => [
                'priority_idx' => ['columns' => ['priority']],
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
            'fieldName'  => 'groupname',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'groupname',
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
            'fieldName'  => 'delay_until',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'delay_until',
        ]);
        $metadata->mapField([
            'fieldName'  => 'ttr',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'ttr',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_ready',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_ready',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_dataonly',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_dataonly',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_ignored',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_ignored',
        ]);
        $metadata->mapField([
            'fieldName'  => 'reserved_at',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'reserved_at',
        ]);
        $metadata->mapField([
            'fieldName'  => 'timeout_at',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'timeout_at',
        ]);
        $metadata->mapField([
            'fieldName'  => 'created_at',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'created_at',
        ]);
        $metadata->mapField([
            'fieldName'  => 'data',
            'type'       => 'array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'data',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
