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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Markdown;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * News
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\News")
 * @orm:Table(name="news")
 */
class News extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\NewsCategory
	 * @orm:ManyToOne(targetEntity="NewsCategory", fetch="EAGER")
	 * @orm:JoinColumn(name="category_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $category;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @orm:Column(name="content", type="text")
	 */
	protected $content;

	/**
	 * Is the news item currently listed for users to read?
	 *
	 * @var bool
	 * @orm:Column(name="is_published", type="boolean")
	 */
	protected $is_published = false;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @orm:OneToMany(targetEntity="LabelNews", mappedBy="news", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
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
		$this->labels = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getContentHtml()
	{
		$content = $this->content;

		// Remove the intro separator
		$content = preg_replace('#[\r\n]+\-{3,}[\r\n]+#', "\n", $content);

		return Markdown::format($content);
	}

	public function getExcerptHtml()
	{
		$content = Strings::standardEol($this->content);
		if ($pos = strpos($content, '![more]')) {
			$excerpt = substr($content, $pos);
		} elseif ($pos = strpos($content, "\n\n")) {
			$excerpt = substr($content, 0, $pos);
		} else {
			$excerpt = $content;
		}

		if (str_word_count($excerpt) > 50) {
			$words = str_word_count($excerpt, 2);
			$pos = Arrays::getNthKey($words, 50);
			$excerpt = substr($excerpt, 0, $pos);
			$excerpt = preg_replace('#[^a-zA-Z0-9]$#', '', $excerpt);
			$excerpt .= '...';
		}

		return Markdown::format($excerpt);
	}

	public function getCountWordsAfterExcerpt()
	{
		$content = strip_tags($this->getContentHtml());
		$exceprt = strip_tags($this->getExcerptHtml());

		$diff = str_word_count($content) - str_word_count($exceprt);

		return $diff;
	}

	public function getUrlSlug()
	{
		return $this->id . '-' . Strings::slugifyTitle($this->title);
	}

	public function getPermalink()
	{
		$url = App::getRouter()->generate('user_news_view', array('slug' => $this->id), true);

		return $url;
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