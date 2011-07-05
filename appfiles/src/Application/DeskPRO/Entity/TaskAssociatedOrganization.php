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
 * Task-Ticket association class.
 *
 * @orm:Entity
 */
class TaskAssociatedOrganization extends TaskAssociation
{
	
	/**
	 * @var Application\DeskPRO\Entity\Organization
	 * @orm:ManyToOne(targetEntity="Organization", inversedBy="task_associations")
	 * @orm:JoinColumn(name="organization_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $organization;
	
}

