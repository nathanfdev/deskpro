<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2011 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Ricardo Rauch <ricardo@gravityonmars.com>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * Base class for Task Associations.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\InheritanceType("SINGLE_TABLE")
 * @ORM_Mapping\DiscriminatorColumn(name="discr", type="string")
 * @ORM_Mapping\DiscriminatorMap({
 * 	"person" = "TaskAssociatedPerson",
 * 	"ticket" = "TaskAssociatedTicket",
 *  "organization" = "TaskAssociatedOrganization",
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
	
}
