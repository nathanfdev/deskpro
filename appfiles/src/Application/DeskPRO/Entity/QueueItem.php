<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DeskPRO\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A QueueItem is some piece of work that has been deferred for later.
 * Note that if an alternate queue system is being used, then this table
 * is still used in cases where additional information must be stored.
 *
 * For example, in the case of queue servers that store queues in memory
 * (such as beanstalkd) it's not a good idea to store large amounts of data
 * in the task. So instead, we simply store the QueueItem ID and the task
 * worker can fetch the data when it processes the task.
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="queue_items")
 */
class QueueItem extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * A group that the queue item is part of. This is a way to classify different kinds of jobs.
	 *
	 * In beanstalkd terminology: tube
	 *
	 * @var string
	 * @orm:Index
	 * @orm:Column(name="groupname", type="string", length=255, nullable=true)
	 */
	protected $groupname;

	/**
	 * The priority of this job
	 * 
	 * @var int
	 * @orm:Column(name="priority", type="integer")
	 */
	protected $priority = 0;

	/**
	 * Don't process this item until this date.
	 * 
	 * @var \DateTime
	 * @orm:Column(name="delay_until",type="datetime")
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
	 * @orm:Column(name="ttr", type="integer")
	 */
	protected $ttr = 60;

	/**
	 * When this is true, the job is ready to be reserved.
	 * 
	 * @var bool
	 * @orm:Column(name="is_ready", type="boolean")
	 */
	protected $is_ready = true;

	/**
	 * Is this item used only for queue datas? Useful if the database queue is used
	 * alongside other queue systems that are using this as a store for data.
	 *
	 * @var bool
	 * @orm:Column(name="is_dataonly", type="boolean")
	 */
	protected $is_dataonly = false;

	/**
	 * Should this job be ignored and not run automatically?
	 * In other words, the job wont be run until something (someone?) unignores it.
	 *
	 * In beanstalkd terminology: buried
	 *
	 * @var bool
	 * @orm:Column(name="is_ignored", type="boolean")
	 */
	protected $is_ignored = false;

	/**
	 * If the job is reserved, this is the time it was reserved at.
	 * Once reserved, the $is_ready becomes false because no other workers
	 * should use this job.
	 *
	 * @var \DateTime
	 * @orm:Column(name="reserved_at",type="datetime")
	 */
	protected $reserved_at = null;

	/**
	 * When a job is reserved, this should be the time the job should expire.
	 * That is, $reserved_at+$ttr
	 *
	 * @var \DateTime
	 * @orm:Column(name="timeout_at",type="datetime")
	 */
	protected $timeout_at = null;

	/**
	 * When this job was created.
	 *
	 * @var \DateTime
	 * @orm:Column(name="created_at",type="datetime")
	 */
	protected $created_at = null;

	/**
	 * Any data pertaining to the job
	 * TODO: Change to BLOB type when Doctrine2 has that type. Or we will have to create it ourselves.
	 * @var string
	 * @orm:Column(name="data", type="text", nullable=true)
	 */
	protected $data = '';
}