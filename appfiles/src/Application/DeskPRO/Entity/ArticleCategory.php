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

use \Application\DeskPRO\App;

/**
 * Article categories
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\ArticleCategory")
 * @ORM_Mapping\Table(name="article_categories")
 */
class ArticleCategory extends CategoryAbstract
{
	/**
	 * @gedmo:TreeParent
	 * @ORM_Mapping\ManyToOne(targetEntity="ArticleCategory", inversedBy="children")
	 */
	protected $parent;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="ArticleCategory", mappedBy="parent")
	 * @ORM_Mapping\OrderBy({"lft" = "ASC"})
	 */
	protected $children;
	
	/**
	 * If this is true, then all categories and articles under this one
	 * are considered agent KB articles and wont be displayed in
	 * the user interface
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_agent", type="boolean")
	 */
	protected $is_agent = false;

	/**
	 * If this is true, then all the articles and categories under this category
	 * is treated as a book (aka manual).
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_book", type="boolean")
	 */
	protected $is_book = false;

	/**
	 * The template suffix to use when rendering the category, and articles within
	 * the category.
	 *
	 * Eg UserBundle:Articles:article.html.twig
	 * With suffix 'download' becomes
	 * UserBundle:Articles:article-download.html.twig
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="template_suffix", type="string", length=100, nullable=true)
	 */
	protected $template_suffix = '';
}