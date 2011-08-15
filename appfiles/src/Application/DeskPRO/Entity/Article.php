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
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Markdown;

use \Orb\Util\Strings;

/**
 * Article
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Article")
 * @ORM_Mapping\Table(name="articles")
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
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToMany(targetEntity="Product", cascade={"persist", "remove", "merge"})
     * @ORM_Mapping\JoinTable(name="article_to_product", joinColumns={@ORM_Mapping\JoinColumn(name="article_id", referencedColumnName="id", onDelete="cascade")}, inverseJoinColumns={@ORM_Mapping\JoinColumn(name="product_id", referencedColumnName="id", onDelete="cascade")})
	 */
	protected $products;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\OneToMany(targetEntity="ArticleRevision", mappedBy="article", cascade={"persist", "remove", "merge"})
	 */
	protected $revisions;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * @var Language
	 * @ORM_Mapping\ManyToOne(targetEntity="Language")
	 * @ORM_Mapping\JoinColumn(name="language_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $language = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="markup_mode", type="string", length=15)
	 */
	protected $markup_mode = 'markdown';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="slug", type="string", length=100)
	 */
	protected $slug;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="excerpt", type="string", length=1000)
	 */
	protected $excerpt = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="content", type="text")
	 */
	protected $content;

	/**
	 * View counts
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="view_count", type="integer")
	 */
	protected $view_count = 0;

	/**
	 * Total rating
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="total_rating", type="integer")
	 */
	protected $total_rating = 0;

	/**
	 * Total rating
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="num_ratings", type="integer")
	 */
	protected $num_ratings = 0;


	/**
	 * @var string
	 * @ORM_Mapping\Column(name="status", type="string", length=15)
	 */
	protected $status;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="hidden_status", type="string", length=15, nullable=true)
	 */
	protected $hidden_status = null;

	/**
	 * Display order of this article. This is mostly used in books.
	 * 
	 * @var string
	 * @ORM_Mapping\Column(name="display_order", type="integer")
	 */
	protected $display_order = 0;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_published",type="datetime", nullable=true)
	 */
	protected $date_published;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_end",type="datetime", nullable=true)
	 */
	protected $date_end;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="end_action", type="string", length=10, nullable=true)
	 */
	protected $end_action = null;

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToMany(targetEntity="ArticleCategory", cascade={"persist", "remove", "merge"})
     * @ORM_Mapping\JoinTable(name="article_to_categories", joinColumns={@ORM_Mapping\JoinColumn(name="article_id", referencedColumnName="id", onDelete="cascade")}, inverseJoinColumns={@ORM_Mapping\JoinColumn(name="category_id", referencedColumnName="id", onDelete="cascade")})
	 */
	protected $categories;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="LabelArticle", mappedBy="article", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	/**
	 * An array of authors,
	 */
	protected $_authors = null;

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

		if (!$this->slug) {
			$this['slug']  = Strings::slugifyTitle($title);
		}
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
			return $this->content;
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

	public function getLink()
	{
		$url = App::getRouter()->generate('user_articles_article', array('slug' => $this->getUrlSlug()), true);

		return $url;
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

	public function addToCategory(ArticleCategory $cat)
	{
		$this->categories->add($cat);
	}

	public function getCategoryNames($sep = ', ', $full = true)
	{
		$cats = array();
		foreach ($this->categories as $cat) {
			if ($full) {
				$cats[] = $cat->getFullTitle();
			} else {
				$cats[] = $cat['title'];
			}
		}

		return implode($sep, $cats);
	}

	public function getRatingPercent()
	{
		if (!$this->total_rating) {
			return 0;
		}

		$neg_ratings = $this->num_ratings - $this->total_rating;
		$rating = ceil(($neg_ratings / $this->total_rating) * 100);

		return $rating;
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

	/**
	 * Get an array of authors
	 * 
	 * @return array
	 */
	public function getAuthors()
	{
		if ($this->_authors !== null) {
			return $this->_authors;
		}

		$this->_authors = array();
		$this->_authors[$this->person['id']] = $this->person;

		$revs = App::getOrm()->createQuery("
			SELECT r, p
			FROM DeskPRO:ArticleRevision r
			LEFT JOIN r.person p
			WHERE r.article = ?1
			ORDER BY r.date_created DESC
		")->setParameter(1, $this)->execute();

		foreach ($revs as $r) {
			$this->_authors[$r['id']] = $r->person;
		}

		return $this->_authors;
	}

	public function getByLine($sep = ', ')
	{
		$names = array();
		foreach ($this->getAuthors() as $a) {
			$names[] = $a->getDisplayName();
		}

		return implode($sep, $names);
	}

	public function getCategoryPath($index = 0)
	{
		$path = array();

		$cat = $this->categories[$index];
		$path[] = $cat;
		while ($cat['parent']) {
			$cat = $cat['parent'];
			$path[] = $cat;
		}

		return $path;
	}
}