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

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Log\Loggable;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class LogRoundRobin extends DomainObject implements Loggable
{
	protected $id;

	protected $timestamp;

	protected $roundRobinId;

	protected $agentId;

	protected $ticketId;

	protected $triggerId;

	public function __construct($robinId, $agentId, $ticketId, $triggerId)
	{
		$this['timestamp'] = time();
		$this['roundRobinId'] = $robinId;
		$this['agentId'] = $agentId;
		$this['ticketId'] = $ticketId;
		$this['triggerId'] = $triggerId;
	}

	/**
	 * todo
	 * @return string
	 */
	public function __toString()
	{
		return sprintf('Round Robin entry');
	}

	public function context()
	{
		return array();
	}

	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->setPrimaryTable(array( 'name' => 'log_round_robin', ));
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'nullable' => false, 'columnName' => 'id', 'id' => true,));
		$metadata->mapField(array( 'fieldName' => 'timestamp', 'type' => 'integer', 'nullable' => false, 'columnName' => 'timestamp', 'columnDefinition' => 'int(11) unsigned not null'));
		$metadata->mapField(array( 'fieldName' => 'roundRobinId', 'type' => 'integer', 'nullable' => false, 'columnName' => 'round_robin_id', 'columnDefinition' => 'int(11) unsigned not null'));
		$metadata->mapField(array( 'fieldName' => 'agentId', 'type' => 'integer', 'nullable' => false, 'columnName' => 'agent_id', 'columnDefinition' => 'int(11) unsigned not null'));
		$metadata->mapField(array( 'fieldName' => 'ticketId', 'type' => 'integer', 'nullable' => false, 'columnName' => 'ticket_id', 'columnDefinition' => 'int(11) unsigned not null'));
		$metadata->mapField(array( 'fieldName' => 'triggerId', 'type' => 'integer', 'nullable' => false, 'columnName' => 'trigger_id', 'columnDefinition' => 'int(11) unsigned not null'));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
	}
}
