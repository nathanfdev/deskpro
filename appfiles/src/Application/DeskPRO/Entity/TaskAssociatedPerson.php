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
 * Task-Person association class.
 *
 * @orm:Entity
 */
class TaskAssociatedPerson extends TaskAssociation 
{
	
	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", inversedBy="task_associations")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person;
	
}
