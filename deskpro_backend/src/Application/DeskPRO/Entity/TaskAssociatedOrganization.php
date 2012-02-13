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
 * Task-Ticket association class.
 *
 * @ORM_Mapping\Entity
 */
class TaskAssociatedOrganization extends TaskAssociation
{

	/**
	 * @var Application\DeskPRO\Entity\Organization
	 * @ORM_Mapping\ManyToOne(targetEntity="Organization", inversedBy="task_associations")
	 * @ORM_Mapping\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $organization;

}

