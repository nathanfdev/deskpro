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

use \Application\DeskPRO\App;

/**
 * Idea categories
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\IdeaCategory")
 * @orm:Table(name="idea_categories")
 */
class IdeaCategory extends CategoryAbstract
{
	/**
	 * @gedmo:TreeParent
	 * @orm:ManyToOne(targetEntity="IdeaCategory", inversedBy="children")
	 */
	protected $parent;

	/**
	 * @orm:OneToMany(targetEntity="IdeaCategory", mappedBy="parent")
	 * @orm:OrderBy({"lft" = "ASC"})
	 */
	protected $children;
}