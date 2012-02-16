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
 * Feedback categories
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\NewsCategory")
 * @ORM_Mapping\Table(name="news_categories")
 */
class NewsCategory extends CategoryAbstract
{
	/**
	 * @ORM_Mapping\ManyToOne(targetEntity="DownloadCategory", inversedBy="children")
	 */
	protected $parent;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="DownloadCategory", mappedBy="parent")
	 * @ORM_Mapping\OrderBy({"display_order" = "ASC"})
	 */
	protected $children;

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToMany(targetEntity="Usergroup", cascade={"persist", "remove", "merge"})
     * @ORM_Mapping\JoinTable(name="news_category2usergroup", joinColumns={@ORM_Mapping\JoinColumn(name="category_id", referencedColumnName="id", onDelete="cascade")}, inverseJoinColumns={@ORM_Mapping\JoinColumn(name="usergroup_id", referencedColumnName="id", onDelete="cascade")})
	 */
	protected $usergroups;
}
