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
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @author Ricardo Rauch <ricardo@gravityonmars.com>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Base class for Task Associations.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\InheritanceType("SINGLE_TABLE")
 * @ORM_Mapping\DiscriminatorColumn(name="discr", type="string")
 * @ORM_Mapping\DiscriminatorMap({
 * 	"person" = "TaskAssociatedPerson",
 * 	"ticket" = "TaskAssociatedTicket",
 *      "organization" = "TaskAssociatedOrganization",
 *      "deal" = "TaskAssociatedDeal"
 * })
 * @ORM_Mapping\Table(name="task_associations")
 */
abstract class TaskAssociation extends \Application\DeskPRO\Domain\DomainObject
{

	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer", nullable=false)
	 *
	 */
	protected $id;

	/**
	 * @var Application\DeskPRO\Entity\Task
	 * @ORM_Mapping\ManyToOne(targetEntity="Task", inversedBy="task_associations")
	 * @ORM_Mapping\JoinColumn(name="task_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $task;



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_SINGLE_TABLE);
		$metadata->setDiscriminatorColumn('discr');
		$metadata->setDiscriminatorMap(array(
			'person' => 'TaskAssociatedPerson',
			'ticket' => 'TaskAssociatedTicket',
			'organization' => 'TaskAssociatedOrganization',
			'deal' => 'TaskAssociatedDeal',
		));
		$metadata->setPrimaryTable(array( 'name' => 'task_associations', ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->mapOneToOne(array( 'fieldName' => 'task', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Task', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => 'task_associations', 'joinColumns' => array( 0 => array( 'name' => 'task_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, ));
	}
}

