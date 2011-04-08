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
	 * @var \Application\DeskPRO\Entity\TicketCategory
	 * @orm:ManyToOne(targetEntity="DownloadCategory", fetch="EAGER")
	 * @orm:JoinColumn(name="category_id", referencedColumnName="id")
	 */
	protected $category;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
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
	 * @orm:OneToMany(targetEntity="LabelNews", mappedBy="article", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	protected $_label_manager = null;

	public function __consturct()
	{
		$this->date_created = new \DateTime();
		$this->comments = new \Doctrine\Common\Collections\ArrayCollection();
		$this->labels = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getIntroHtml()
	{
		$content = $this->content;

		// The intro part is whatever text is before a line of three dashes
		$parts = preg_split("#[\r\n]+\-{3,}[\r\n]+#", $content, 2);

		$content = trim($parts[0]);

		return Markdown::format($content);
	}

	public function getContentHtml()
	{
		$content = $this->content;

		// Remove the intro separator
		$content = preg_replace('#[\r\n]+\-{3,}[\r\n]+#', "\n", $content);

		return Markdown::format($content);
	}

	public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelNews');
		}

		return $this->_label_manager;
	}
}