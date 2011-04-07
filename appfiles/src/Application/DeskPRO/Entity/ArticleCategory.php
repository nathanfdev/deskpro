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

use DoctrineExtensions\NestedSet\Node;

/**
 * Article categories
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\ArticleCategory")
 * @orm:Table(name="article_categories")
 */
class ArticleCategory extends CategoryAbstract
{
	/**
	 * If this is true, then all the articles and categories under this category
	 * is treated as a book (aka manual).
	 *
	 * @var bool
	 * @orm:Column(name="is_book", type="boolean")
	 */
	protected $is_book = false;

	/**
	 * @gedmo:TreeParent
	 * @orm:ManyToOne(targetEntity="ArticleCategory", inversedBy="children")
	 */
	protected $parent;

	/**
	 * @orm:OneToMany(targetEntity="ArticleCategory", mappedBy="parent")
	 * @orm:OrderBy({"lft" = "ASC"})
	 */
	protected $children;
}