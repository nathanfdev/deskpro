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

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @property int $id
 * @property Ticket $ticket
 * @property TicketEscalation $escalation
 * @property \DateTime $date_ran
 * @property \DateTime $date_criteria
 */
class TicketEscalationLog extends DomainObject
{
	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * @var Ticket
	 */
	protected $ticket;

	/**
	 * @var TicketEscalation
	 */
	protected $escalation;

	/**
	 * The date the trigger was executed on the ticket.
	 *
	 * @var \DateTime
	 */
	protected $date_ran;

	/**
	 * The date criteria that caused the trigger execute in the first place.
	 * For example, if a ticket is awaiting_user and the trigger is set to run after 1 day,
	 * then when the trigger finally runs, date_criteria = ticket.date_awaiting_user.
	 *
	 * This is used to ensure a trigger doesnt run multiple times in any escalation period. So
	 * that executes after 5 minuts doesnt again run after 10, and 15, and 20 etc.
	 *
	 * @var \DateTime
	 */
	protected $date_criteria;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->inheritanceType      = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
		$metadata->changeTrackingPolicy = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
		$metadata->generatorType        = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
		$metadata->setPrimaryTable(array(
			'name' => 'ticket_escalation_logs'
		));

		$metadata->mapField(array(
			'id'         => true,
			'fieldName'  => 'id',
			'columnName' => 'id',
			'type'       => 'integer',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_ran',
			'columnName' => 'date_ran',
			'type'       => 'datetime',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'date_criteria',
			'columnName' => 'date_criteria',
			'type'       => 'datetime',
			'nullable'   => false,
		));

		$metadata->mapManyToOne(array(
			'fieldName'    => 'ticket',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
			'joinColumns'  => array(array(
				'name'                 => 'ticket_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'cascade',
				'columnDefinition'    => NULL
			))
		));
		$metadata->mapManyToOne(array(
			'fieldName'    => 'escalation',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketEscalation',
			'joinColumns'  => array(array(
				'name'                 => 'escalation_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'cascade',
				'columnDefinition'    => NULL
			))
		));
	}
}
