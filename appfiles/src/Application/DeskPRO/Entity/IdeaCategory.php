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

use Application\DeskPRO\App;

/**
 * Idea categories
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\IdeaCategory")
 * @ORM_Mapping\Table(name="idea_categories")
 */
class IdeaCategory extends CategoryAbstract
{
	/**
	 * @ORM_Mapping\ManyToOne(targetEntity="IdeaCategory", inversedBy="children")
	 */
	protected $parent;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="IdeaCategory", mappedBy="parent")
	 * @ORM_Mapping\OrderBy({"display_order" = "ASC"})
	 */
	protected $children;

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToMany(targetEntity="Usergroup", cascade={"persist", "remove", "merge"})
     * @ORM_Mapping\JoinTable(name="idea_category2usergroup", joinColumns={@ORM_Mapping\JoinColumn(name="category_id", referencedColumnName="id", onDelete="cascade")}, inverseJoinColumns={@ORM_Mapping\JoinColumn(name="usergroup_id", referencedColumnName="id", onDelete="cascade")})
	 */
	protected $usergroups;
}
