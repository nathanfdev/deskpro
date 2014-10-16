<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @property int $id
 * @property string $title
 * @property bool $is_enabled
 * @property string $event_trigger
 * @property int $event_trigger_time
 * @property array $terms
 * @property array $terms_any
 * @property \Application\DeskPRO\Tickets\Triggers\TriggerActions $actions
 * @property \DateTime $date_created
 * @property \DateTime $date_last_run
 */
class TicketEscalation extends DomainObject
{
	const EVENT_TYPE_TIME_OPEN                  = 'time.open';
	const EVENT_TYPE_TIME_USER_WAITING          = 'time.user_waiting';
	const EVENT_TYPE_TIME_TOTAL_USER_WAITING    = 'time.total_user_waiting';
	const EVENT_TYPE_TIME_AGENT_WAITING         = 'time.agent_waiting';
	const EVENT_TYPE_TIME_RESOLVED              = 'time.resolved';

	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var bool
	 */
	protected $is_enabled = true;

	/**
	 * @var string
	 */
	protected $event_trigger;

	/**
	 * @var int
	 */
	protected $event_trigger_time;

	/**
	 * @var array
	 */
	protected $terms = array();

	/**
	 * @var array
	 */
	protected $terms_any = array();

	/**
	 * @var \Application\DeskPRO\Tickets\Triggers\TriggerActions
	 */
	protected $actions;

	/**
	 * @var \DateTime
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 */
	protected $date_last_run = null;

	public function __construct()
	{
		$this->actions = new TriggerActions();
		$this->date_created = new \DateTime();
	}


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * Gets the relevant time field on ticket for a particular ticket trigger.
	 * For example, 'EVENT_TYPE_TIME_USER_WAITING' is dependant on ticket.date_user_waiting
	 *
	 * @return string
	 */
	public function getTicketTimeField()
	{
		switch ($this->event_trigger) {
			case self::EVENT_TYPE_TIME_OPEN:
				return 'date_created';

			case self::EVENT_TYPE_TIME_USER_WAITING:
			case self::EVENT_TYPE_TIME_TOTAL_USER_WAITING:
				return 'date_user_waiting';

			case self::EVENT_TYPE_TIME_AGENT_WAITING:
				return 'date_agent_waiting';
				break;

			case self::EVENT_TYPE_TIME_RESOLVED:
				return 'date_resolved';
		}

		return null;
	}



	/**
	 * {@inheritDoc}
	 */
	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
		$data = parent::toApiData($primary, $deep, $visited);
		$data['terms']     = $this->terms;
		$data['terms_any'] = $this->terms_any;
		$data['actions']   = $this->actions->exportToArray();
		return $data;
	}
	

	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketEscalation';
		$metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
		$metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;

		$metadata->setPrimaryTable(array(
			'name' => 'ticket_escalations'
		));

		$metadata->mapField(array(
			'id'         => true,
			'columnName' => 'id',
			'fieldName'  => 'id',
			'type'       => 'integer',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'title',
			'fieldName'  => 'title',
			'type'       => 'string',
			'length'     => 255,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'event_trigger',
			'fieldName'  => 'event_trigger',
			'type'       => 'string',
			'length'     => 50,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'event_trigger_time',
			'fieldName'  => 'event_trigger_time',
			'type'       => 'integer',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'is_enabled',
			'fieldName'  => 'is_enabled',
			'type'       => 'boolean',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'terms',
			'fieldName'  => 'terms',
			'type'       => 'json_array',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'terms_any',
			'fieldName'  => 'terms_any',
			'type'       => 'json_array',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'actions',
			'fieldName'  => 'actions',
			'type'       => 'dp_json_obj',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_created',
			'columnName' => 'date_created',
			'type'       => 'datetime',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_last_run',
			'columnName' => 'date_last_run',
			'type'       => 'datetime',
			'nullable'   => true,
		));
	}
}
