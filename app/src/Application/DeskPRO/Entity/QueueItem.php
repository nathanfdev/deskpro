<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A QueueItem is some piece of work that has been deferred for later.
 * Note that if an alternate queue system is being used, then this table
 * is still used in cases where additional information must be stored.
 *
 * For example, in the case of queue servers that store queues in memory
 * (such as beanstalkd) it's not a good feedback to store large amounts of data
 * in the task. So instead, we simply store the QueueItem ID and the task
 * worker can fetch the data when it processes the task.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="queue_items")
 */
class QueueItem extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * A group that the queue item is part of. This is a way to classify different kinds of jobs.
	 *
	 * In beanstalkd terminology: tube
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="groupname", type="string", length=255, nullable=true)
	 */
	protected $groupname;

	/**
	 * The priority of this job
	 *
	 * @var int
	 * @ORM_Mapping\Column(name="priority", type="integer")
	 */
	protected $priority = 0;

	/**
	 * Don't process this item until this date.
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="delay_until",type="datetime",nullable=true)
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
	 * @ORM_Mapping\Column(name="ttr", type="integer")
	 */
	protected $ttr = 60;

	/**
	 * When this is true, the job is ready to be reserved.
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_ready", type="boolean")
	 */
	protected $is_ready = true;

	/**
	 * Is this item used only for queue datas? Useful if the database queue is used
	 * alongside other queue systems that are using this as a store for data.
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_dataonly", type="boolean")
	 */
	protected $is_dataonly = false;

	/**
	 * Should this job be ignored and not run automatically?
	 * In other words, the job wont be run until something (someone?) unignores it.
	 *
	 * In beanstalkd terminology: buried
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_ignored", type="boolean")
	 */
	protected $is_ignored = false;

	/**
	 * If the job is reserved, this is the time it was reserved at.
	 * Once reserved, the $is_ready becomes false because no other workers
	 * should use this job.
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="reserved_at",type="datetime",nullable=true)
	 */
	protected $reserved_at = null;

	/**
	 * When a job is reserved, this should be the time the job should expire.
	 * That is, $reserved_at+$ttr
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="timeout_at",type="datetime",nullable=true)
	 */
	protected $timeout_at = null;

	/**
	 * When this job was created.
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="created_at",type="datetime")
	 */
	protected $created_at = null;

	/**
	 * Any data pertaining to the job
	 * @var string
	 * @ORM_Mapping\Column(name="data", type="array", nullable=true)
	 */
	protected $data = array();

	public function __construct()
	{
		$this->created_at = new \DateTime();
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE); 
		$metadata->setPrimaryTable(array( 'name' => 'queue_items', )); 
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT); 
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'id', 'id' => true, )); 
		$metadata->mapField(array( 'fieldName' => 'groupname', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'groupname', )); 
		$metadata->mapField(array( 'fieldName' => 'priority', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'priority', )); 
		$metadata->mapField(array( 'fieldName' => 'delay_until', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'delay_until', )); 
		$metadata->mapField(array( 'fieldName' => 'ttr', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'ttr', )); 
		$metadata->mapField(array( 'fieldName' => 'is_ready', 'type' => 'boolean', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'is_ready', )); 
		$metadata->mapField(array( 'fieldName' => 'is_dataonly', 'type' => 'boolean', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'is_dataonly', )); 
		$metadata->mapField(array( 'fieldName' => 'is_ignored', 'type' => 'boolean', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'is_ignored', )); 
		$metadata->mapField(array( 'fieldName' => 'reserved_at', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'reserved_at', )); 
		$metadata->mapField(array( 'fieldName' => 'timeout_at', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'timeout_at', )); 
		$metadata->mapField(array( 'fieldName' => 'created_at', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'created_at', )); 
		$metadata->mapField(array( 'fieldName' => 'data', 'type' => 'array', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'unique' => false, 'columnName' => 'data', )); 
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
	}
}

