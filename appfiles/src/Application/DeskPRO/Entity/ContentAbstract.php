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
use Gedmo\Mapping\Annotation as Gedmo_Mapping;

use Application\DeskPRO\App;
use Application\DeskPRO\Translate\HasPhraseName;
use Application\DeskPRO\Translate\Translate;

use DoctrineExtensions\NestedSet\Node;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Basic properties on content
 *
 * @ORM_Mapping\MappedSuperclass
 */
abstract class ContentAbstract extends \Application\DeskPRO\Domain\DomainObject
{
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
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_published",type="datetime", nullable=true)
	 */
	protected $date_published;

	// Implement in children
	///**
	// * @var \Doctrine\Common\Collections\ArrayCollection
	// * @ORM_Mapping\OneToMany(targetEntity="ArticleRevision", mappedBy="article", cascade={"persist", "remove", "merge"}, indexBy="id")
	// */
	//protected $revisions;

	// Implement in children
	///**
	// * @ORM_Mapping\OneToMany(targetEntity="LabelArticle", mappedBy="article", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	// */
	//protected $labels;

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
		$this->revisions = new \Doctrine\Common\Collections\ArrayCollection();
		$this->labels = new \Doctrine\Common\Collections\ArrayCollection();

		$this->status = self::STATUS_HIDDEN;
		$this->hidden_status = self::HIDDEN_STATUS_DRAFT;
	}

	public function setTitle($title)
	{
		$this->setModelField('title', $title);

		if (!$this->slug) {
			$this['slug']  = Strings::slugifyTitle($title);
		}
	}

	public function setStatus($status)
	{
		$this->setStatusCode($status);
	}

	public function setStatusCode($status_code)
	{
		if (strpos($status_code, 'hidden.') === 0) {
			$status_code = str_replace('hidden.', '', $status_code);
			$this->setModelField('status', 'hidden');
			$this->setModelField('hidden_status', $status_code);

			$this->setModelField('date_published', null);
		} else {
			$this->setModelField('status', $status_code);
			$this->setModelField('hidden_status', null);

			if (!$this->date_published) {
				$this->setModelField('date_published', new \DateTime());
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

	public function getContentHtml()
	{
		return $this->content;
	}

	public function getContentPlainHtml()
	{
		$content = htmlspecialchars($this->content);
		$content = nl2br($content);

		return $content;
	}

	public function getContentPlain()
	{
		if (!$this->content) {
			return '';
		}
		$content = Strings::standardEol($this->content);
		$content = preg_replace("#<br\s*/?><p>#", "<p>", $content);
		$content = preg_replace("#<p></p><br\s*/?>#", "<p>", $content);
		$content = preg_replace("#</p><br\s*/?>#", "</p>", $content);
		$content = preg_replace("#<br\s*/?></p>#", "</p>", $content);
		$content = preg_replace("#<br\s*/?>?#", "\n", $content);
		$content = preg_replace("#<p>\n?#", "\n", $content);
		$content = preg_replace("#\n?</p>#", "\n", $content);
		$content = html_entity_decode(strip_tags($content), \ENT_QUOTES, 'UTF-8');
		$content = trim($content);

		$lines_raw = explode("\n", $content);
		$lines = array();
		foreach ($lines_raw as $l) {
			$lines[] = trim($l);
		}

		$content = implode("\n", $lines);
		$content = preg_replace("#\n{3,}#", "\n\n", $content);

		return $content;
	}

	public function getUrlSlug()
	{
		return $this->id . '-' . $this->slug;
	}

	abstract public function getLink();

	abstract public function getPermalink();

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

	public function getUpVotes()
	{
		return $this->total_rating;
	}

	public function getDownVotes()
	{
		return $this->num_ratings - $this->total_rating;
	}

	public function getRatingPercent()
	{
		if (!$this->num_ratings) {
			return 0;
		}

		return min(100, ceil(($this->total_rating / $this->num_ratings) * 100));
	}

	public function addRating($rating)
	{
		$this->num_ratings = $this->num_ratings + 1;
		$this->total_rating = $this->total_rating + $rating->rating;
		$rating->setContentObject($this);
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
	 * @return string
	 */
	public static function getContentType()
	{
		static $name = null;

		if ($name === null) {
			$name = self::getEntityName();
			$name = strtolower(str_replace('DeskPRO:', '', $name));
		}

		return $name;
	}
}
