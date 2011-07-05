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

/**
 * Base class for Task Associations.
 *
 * @orm:Entity
 * @orm:InheritanceType("SINGLE_TABLE")
 * @orm:DiscriminatorColumn(name="discr", type="string")
 * @orm:DiscriminatorMap({
 * 	"person" = "TaskAssociatedPerson",
 * 	"ticket" = "TaskAssociatedTicket",
 *  "organization" = "TaskAssociatedOrganization"
 * })
 * @orm:Table(name="task_associations")
 */
abstract class TaskAssociation extends \Application\DeskPRO\Domain\DomainObject
{
	
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id
	 * @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer", nullable=false)
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @var Application\DeskPRO\Entity\Task
	 * @orm:ManyToOne(targetEntity="Task", inversedBy="task_associations")
	 * @orm:JoinColumn(name="task_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $task;
	
}
