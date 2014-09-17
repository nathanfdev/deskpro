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
use Application\DeskPRO\Entity;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Article
 */
class RoundRobinAgent extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\RoundRobin
	 */
	protected $robin;

	/**
	 * Next agent in queue
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $agent;

	/**
	 * Sort field
	 *
	 * @var int
	 */
	protected $sort;

	public function __construct()
	{
		$this['sort'] = 0;
	}

	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
//		$data = parent::toApiData($primary, $deep, $visited);
		$data = array(
			'id' => $this->agent ? $this->agent['id'] : null,
		);

		return $data;
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->setPrimaryTable(array( 'name' => 'round_robin_agents', ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\RoundRobinAgent';

		$metadata->mapField(array( 'fieldName' => 'sort', 'type' => 'integer', 'nullable' => false, 'columnName' => 'sort',));

		$metadata->mapOneToOne(array(
			'id' => true,
			'fieldName' => 'agent',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
			'mappedBy' => NULL,
			'inversedBy' => NULL,
			'joinColumns' => array(
				0 => array(
					'name' => 'agent_id',
					'referencedColumnName' => 'id',
					'nullable' => false,
					'onDelete' => 'cascade',
					'columnDefinition' => NULL,
				),
			),
		));

		$metadata->mapManyToOne(array(
			'id' => true,
			'fieldName' => 'robin',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\RoundRobin',
			'mappedBy' => NULL,
			'inversedBy' => 'agents',
			'joinColumns' => array(
				0 => array(
					'name' => 'robin_id',
					'referencedColumnName' => 'id',
					'nullable' => false,
					'onDelete' => 'cascade',
					'columnDefinition' => NULL,
				),
			),
		));
	}
}