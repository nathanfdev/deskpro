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

use Application\DeskPRO\Tickets\Escalations\EscalationTerms;
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Ticket triggers
 *
 */
class TicketEscalation extends \Application\DeskPRO\Domain\DomainObject
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
	 * @var \Application\DeskPRO\Tickets\Escalations\EscalationTerms
	 */
	protected $terms;

	/**
	 * @var \Application\DeskPRO\Tickets\Triggers\TriggerActions
	 */
	protected $actions;

	/**
	 * @var int
	 */
	protected $run_order = 0;

	public function __construct()
	{
		$this->terms   = new EscalationTerms();
		$this->actions = new TriggerActions();
	}


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * {@inheritDoc}
	 */
	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
		$data = parent::toApiData($primary, $deep, $visited);
		$data['terms']   = $this->terms->exportToArray();
		$data['actions'] = $this->actions->exportToArray();
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
			'type'       => 'object',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'actions',
			'fieldName'  => 'actions',
			'type'       => 'object',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'run_order',
			'fieldName'  => 'run_order',
			'type'       => 'integer',
			'nullable'   => false,
		));
	}
}
