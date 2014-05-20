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

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @property int $id
 * @property string $sla_status
 * @property \DateTime $warn_date
 * @property \DateTime $fail_date
 * @property bool $is_completed
 * @property bool $is_completed_set
 * @property int $completed_time_taken
 * @property Ticket $ticket
 * @property Sla $sla
 */
class TicketSla extends DomainObject
{
	const STATUS_OK = 'ok';
	const STATUS_WARNING = 'warning';
	const STATUS_FAIL = 'fail';

	/**
	 * The unique ID.
	 *
	 * @var int
	 *
	 */
	protected $id = null;

	/**
	 * @var string
	 */
	protected $sla_status = 'ok';

	/**
	 * @var \DateTime|null
	 */
	protected $warn_date;

	/**
	 * @var \DateTime|null
	 */
	protected $fail_date;

	/**
	 * @var bool
	 */
	protected $is_completed = false;

	/**
	 * @var bool
	 */
	protected $is_completed_set = false;

	/**
	 * @var null|integer
	 */
	protected $completed_time_taken = null;

	/**
	 * @var Ticket
	 */
	protected $ticket;

	/**
	 * @var Sla
	 */
	protected $sla;


	/**
	 * @param bool $value
	 * @param \DateTime|null $date
	 */
	public function setIsCompleted($value, \DateTime $date = null)
	{
		$value = (bool)$value;

		$this->setModelField('is_completed', $value);
		if ($this->is_completed) {
			if ($this->sla_status == self::STATUS_OK) {
				$this->setModelField('warn_date', null);
				$this->setModelField('fail_date', null);
			} else if ($this->sla_status == self::STATUS_WARNING) {
				$this->setModelField('fail_date', null);
			}

			if ($date === null) {
				$date = new \DateTime();
			}
			if ($date) {
				$this->setModelField('completed_time_taken', $this->sla->getCalculator()->calculateTimeUntil($this->ticket, $date));
			} else {
				$this->setModelField('completed_time_taken', null);
			}
		} else {
			$this->setModelField('completed_time_taken', null);
		}
	}


	/**
	 * Same as setIsCompleted but the completed status is set forever (unless its overriden with a trigger etc).
	 * Usually when status changes, the SLA is re-calculated.
	 *
	 * @param $value
	 * @param null $date
	 */
	public function setIsCompletedSet($value, $date = null)
	{
		$this->setIsCompleted($value, $date);
		if ($value) {
			$this->setModelField('is_completed_set', true);
		} else {
			$this->setModelField('is_completed_set', false);
		}
	}


	/**
	 * @return \DateTime|null
	 */
	public function getNextTriggerDate()
	{
		$times = array();

		if ($this->sla_status == self::STATUS_OK && $this->warn_date && $this->warn_date->getTimestamp() > time()) {
			$times[] = $this->warn_date->getTimestamp();
		}

		if (in_array($this->sla_status, array(self::STATUS_OK, self::STATUS_WARNING)) && $this->fail_date && $this->fail_date->getTimestamp() > time()) {
			$times[] = $this->fail_date->getTimestamp();
		}

		if (!$times) {
			return null;
		}

		return new \DateTime('@' . min($times));
	}


	/**
	 * {@inheritDoc}
	 */
	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
		$data = parent::toApiData($primary, $deep, $visited);

		return $data;
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
		$metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
		$metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketSla';

		$metadata->setPrimaryTable(array(
			'name' => 'ticket_slas',
			'indexes' => array(
				'status_completed_warn_date_idx' => array('columns' => array('sla_status', 'is_completed', 'warn_date')),
				'status_completed_fail_date_idx' => array('columns' => array('sla_status', 'is_completed', 'fail_date')),
			),
		));

		$metadata->mapField(array(
			'id'         => true,
			'fieldName'  => 'id',
			'columnName' => 'id',
			'type'       => 'integer',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'sla_status',
			'columnName' => 'sla_status',
			'type'       => 'string',
			'length'     => 20,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'warn_date',
			'columnName' => 'warn_date',
			'type'       => 'datetime',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'fail_date',
			'columnName' => 'fail_date',
			'type'       => 'datetime',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'fieldName'  => 'is_completed',
			'columnName' => 'is_completed',
			'type'       => 'boolean',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'is_completed_set',
			'columnName' => 'is_completed_set',
			'type'       => 'boolean',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'completed_time_taken',
			'columnName' => 'completed_time_taken',
			'type'       => 'integer',
			'nullable'   => true,
		));

		$metadata->mapManyToOne(array(
			'fieldName'    => 'ticket',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
			'joinColumns'  => array(array(
				'name'                 => 'ticket_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'cascade',
			)),
		));
		$metadata->mapManyToOne(array(
			'fieldName'    => 'sla',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\Sla',
			'joinColumns'  => array(array(
				'name'                 => 'sla_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'cascade',
				'columnDefinition'     => NULL
			)),
			'dpApi' => true
		));
	}
}
