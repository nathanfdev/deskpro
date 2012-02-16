<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * Labels on organizations
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="labels_organizations")
 */
class LabelOrganization extends LabelAssocAbstract
{
	const LABEL_TYPENAME = 'organizations';
	
	/**
	 * @var \Application\DeskPRO\Entity\Organization
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\ManyToOne(targetEntity="Organization")
	 * @ORM_Mapping\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $organization;
}