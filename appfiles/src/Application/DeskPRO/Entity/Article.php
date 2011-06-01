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
 * Article
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Article")
 * @orm:Table(name="articles")
 */
class Article extends \Application\DeskPRO\Domain\DomainObject
{
	const MARKUP_MODE_MARKDOWN = 'markdown';
	const MARKUP_MODE_HTML = 'html';

	const END_ACTION_DELETE  = 'delete';
	const END_ACTION_ARCHIVE = 'archive';

	const STATUS_PUBLISHED   = 'published';
	const STATUS_ARCHIVED    = 'archived';
	const STATUS_HIDDEN      = 'hidden';

	const HIDDEN_STATUS_UNPUBLISHED   = 'unpublished';
	const HIDDEN_STATUS_VALIDATING    = 'validating';
	const HIDDEN_STATUS_DELETED       = 'deleted';
	const HIDDEN_STATUS_DRAFT         = 'draft';

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @orm:ManyToMany(targetEntity="Product", cascade={"persist", "remove", "merge"})
     * @orm:JoinTable(name="article_to_product", joinColumns={@orm:JoinColumn(name="article_id", referencedColumnName="id")}, inverseJoinColumns={@orm:JoinColumn(name="product_id", referencedColumnName="id")})
	 */
	protected $products;

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
	 * @orm:Column(name="markup_mode", type="string", length=15)
	 */
	protected $markup_mode = 'markdown';

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
	 * @var string
	 * @orm:Column(name="status", type="string", length=15)
	 */
	protected $status;

	/**
	 * @var string
	 * @orm:Column(name="hidden_status", type="string", length=15, nullable=true)
	 */
	protected $hidden_status = null;

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
	 * @var \DateTime
	 * @orm:Column(name="date_published",type="datetime", nullable=true)
	 */
	protected $date_published;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_end",type="datetime", nullable=true)
	 */
	protected $date_end;

	/**
	 * @var string
	 * @orm:Column(name="end_action", type="string", length=10, nullable=true)
	 */
	protected $end_action = null;

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

	/**
	 * @var \Application\DeskPRO\Labels\LabelManager
	 */
	protected $_label_manager = null;

	public function __construct()
	{
		$this->date_created = new \DateTime();
		$this->comments = new \Doctrine\Common\Collections\ArrayCollection();
		$this->products = new \Doctrine\Common\Collections\ArrayCollection();
		$this->categories = new \Doctrine\Common\Collections\ArrayCollection();
		$this->labels = new \Doctrine\Common\Collections\ArrayCollection();

		$this->status = self::STATUS_HIDDEN;
		$this->hidden_status = self::HIDDEN_STATUS_DRAFT;
	}

	public function setTitle($title)
	{
		$this->title = $title;
		$this->slug  = Strings::slugifyTitle($title);
	}

	public function setStatusCode($status_code)
	{
		if (strpos($status_code, 'hidden.') === 0) {
			$status_code = str_replace('hidden.', '', $status_code);
			$this['status'] = 'hidden';
			$this['hidden_status'] = $status_code;

			$this->date_published = null;
		} else {
			$this['status'] = $status_code;
			$this['hidden_status'] = null;

			if (!$this->date_published) {
				$this->date_published = new \DateTime();
			}
		}
	}

	public function getStatusCode()
	{
		if ($this->hidden_status) {
			return 'hidden.' . $this->hidden_status;
		} else {
			return $this->status;
		}
	}

	public function getExcerptHtml()
	{
		if ($this->markup_mode == self::MARKUP_MODE_HTML) {
			return $this->excerpt;
		} else {
			return Markdown::format($this->excerpt);
		}
	}

	public function getContentHtml()
	{
		if ($this->markup_mode == self::MARKUP_MODE_HTML) {
			$this->content
		} else {
			return Markdown::format($this->content);
		}
	}

	public function getContentPlainHtml()
	{
		$content = htmlspecialchars($this->content);
		$content = nl2br($content);

		return $content;
	}

	public function getUrlSlug()
	{
		return $this->id . '-' . $this->slug;
	}

	public function getPermalink()
	{
		$url = App::getRouter()->generate('user_articles_article', array('slug' => $this->id), true);

		return $url;
	}

	public function setHtmlContent($content)
	{
		$this->content = $content;
		$this->markup_mode = self::MARKUP_MODE_HTML;
	}

	public function setMarkdownContent($content)
	{
		$this->content = $content;
		$this->markup_mode = self::MARKUP_MODE_MARKDOWN;
	}

	/**
	 * @return \Application\DeskPRO\Labels\LabelManager
	 */
	public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelArticle');
		}

		return $this->_label_manager;
	}
}