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

use \Orb\Util\Strings;

/**
 * Ideas (feedback)
 *
 * @orm:Entity
 * @orm:Table(name="ideas")
 */
class Idea extends \Application\DeskPRO\Domain\DomainObject
{
	const STATUS_OPEN = 'visible';
	const STATUS_VALIDATING = 'validating';
	const STATUS_DELETED = 'deleted';

	const COMPLETION_STATUS_REVIEW = 'review';
	const COMPLETION_STATUS_ACCEPTED = 'accepted';
	const COMPLETION_STATUS_DECLINED = 'declined';

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
	 * @var string
	 * @orm:Column(name="status", type="string", length=15)
	 */
	protected $status;

	/**
	 * @var string
	 * @orm:Column(name="completion_status", type="string", length=15)
	 */
	protected $completion_status;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @orm:ManyToOne(targetEntity="IdeaCategory", cascade={"persist", "remove", "merge"})
	 */
	protected $category;

	/**
	 * @orm:OneToMany(targetEntity="LabelIdea", mappedBy="article", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	protected $_label_manager = null;

	public function __consturct()
	{
		$this->date_created = new \DateTime();
		$this->comments = new \Doctrine\Common\Collections\ArrayCollection();
		$this->labels = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getExcerptHtml()
	{
		return Markdown::format($this->excerpt);
	}

	public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelArticle');
		}

		return $this->_label_manager;
	}
}