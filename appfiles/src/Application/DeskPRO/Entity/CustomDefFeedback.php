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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A custom field definition
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\CustomDefIdea")
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="custom_def_feedback")
 */
class CustomDefIdea extends CustomDefAbstract
{
	/**
	 * @var CustomDefIdea
	 * @ORM_Mapping\ManyToOne(targetEntity="CustomDefIdea", inversedBy="children", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $parent = null;

	/**
	 * Field children
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="CustomDefIdea", mappedBy="parent", cascade={"persist", "remove", "merge"}, fetch="EAGER")
	 * @ORM_Mapping\OrderBy({"display_order" = "ASC"})
	 */
	protected $children = null;
}
