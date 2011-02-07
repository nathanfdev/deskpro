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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A custom field definition
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\CustomDefOrganization")
 * @orm:Table(name="custom_def_organizations")
 */
class CustomDefOrganization extends CustomDefAbstract
{
	/**
	 * @var CustomDefOrganization
	 * @orm:ManyToOne(targetEntity="CustomDefOrganization", inversedBy="children")
	 * @orm:JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent = null;

	/**
	 * Field children
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="CustomDefOrganization", mappedBy="parent", cascade={"persist", "remove", "merge"})
	 */
	protected $children = null;
}