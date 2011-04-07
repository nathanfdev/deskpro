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
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Markdown;

use \Orb\Util\Strings;

/**
 * Ticket
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Article")
 * @orm:Table(name="articles")
 */
class Article extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var Language
	 * @orm:ManyToOne(targetEntity="Language")
	 * @orm:JoinColumn(name="language_id", referencedColumnName="id")
	 */
	protected $language = null;

	/**
	 * @var string
	 * @orm:Column(name="slug", type="string", length=100)
	 */
	protected $slug;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @orm:Column(name="excerpt", type="string", length=1000)
	 */
	protected $excerpt = '';

	/**
	 * @var string
	 * @orm:Column(name="content", type="text")
	 */
	protected $content;

	/**
	 * View counts
	 *
	 * @var string
	 * @orm:Column(name="view_count", type="integer")
	 */
	protected $view_count = 0;

	/**
	 * Total rating
	 *
	 * @var string
	 * @orm:Column(name="total_rating", type="integer")
	 */
	protected $total_rating = 0;

	/**
	 * Total rating
	 *
	 * @var string
	 * @orm:Column(name="num_ratings", type="integer")
	 */
	protected $num_ratings = 0;


	/**
	 * Is the article currently listed for users to read?
	 *
	 * @var bool
	 * @orm:Column(name="is_published", type="boolean")
	 */
	protected $is_published = false;

	/**
	 * Display order of this article. This is mostly used in books.
	 * 
	 * @var string
	 * @orm:Column(name="display_order", type="integer")
	 */
	protected $display_order = 0;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @orm:ManyToMany(targetEntity="ArticleCategory", cascade={"persist", "remove", "merge"})
     * @orm:JoinTable(name="article_to_categories", joinColumns={@orm:JoinColumn(name="article_id", referencedColumnName="id")}, inverseJoinColumns={@orm:JoinColumn(name="category_id", referencedColumnName="id")})
	 */
	protected $categories;

	/**
	 * @orm:OneToMany(targetEntity="LabelArticle", mappedBy="article", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	protected $_label_manager = null;

	public function __consturct()
	{
		$this->date_created = new \DateTime();
		$this->comments = new \Doctrine\Common\Collections\ArrayCollection();
		$this->categories = new \Doctrine\Common\Collections\ArrayCollection();
		$this->labels = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getExcerptHtml()
	{
		return Markdown::format($this->excerpt);
	}

	public function getContentHtml()
	{
		return Markdown::format($this->content);
	}

	public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelArticle');
		}

		return $this->_label_manager;
	}
}