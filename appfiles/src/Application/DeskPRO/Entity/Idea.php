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

use Application\DeskPRO\App;

use Orb\Util\Strings;

/**
 * Ideas (feedback)
 *
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Idea")
 * @ORM_Mapping\Table(name="ideas")
 */
class Idea extends \Application\DeskPRO\Domain\DomainObject
{
	const STATUS_NEW      = 'new';
	const STATUS_ACTIVE   = 'active';
	const STATUS_CLOSED   = 'closed';
	const STATUS_HIDDEN   = 'hidden';

	const HIDDEN_STATUS_VALIDATING = 'validating';
	const HIDDEN_STATUS_SPAM = 'spam';
	const HIDDEN_STATUS_DELETED = 'deleted';

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
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="status", type="string", length=15)
	 */
	protected $status;

	/**
	 * @var \Application\DeskPRO\Entity\IdeaStatusCategory
	 * @ORM_Mapping\ManyToOne(targetEntity="IdeaStatusCategory", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="status_category_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $status_category = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="hidden_status", type="string", length=15, nullable=true)
	 */
	protected $hidden_status = null;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="num_votes", type="integer")
	 */
	protected $num_votes = 0;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToOne(targetEntity="IdeaCategory", cascade={"persist", "remove", "merge"})
	 */
	protected $category;

	/**
	 * The primary email address used by this account
	 *
	 * @var \Application\DeskPRO\Entity\IdeaComment
	 * @ORM_Mapping\OneToOne(targetEntity="IdeaComment", fetch="EAGER", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 * @ORM_Mapping\JoinColumn(name="first_comment_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $first_comment;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="IdeaComment", mappedBy="idea", cascade={"persist", "remove", "merge"})
	 */
	protected $comments;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="IdeaVote", mappedBy="idea", cascade={"persist", "remove", "merge"})
	 */
	protected $votes;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="LabelIdea", mappedBy="idea", cascade={"persist", "remove", "merge"}, orphanRemoval=true)
	 */
	protected $labels;

	/**
	 * @var \Application\DeskPRO\Labels\LabelManager
	 */
	protected $_label_manager = null;

	protected $_is_new = false;

	public function __construct()
	{
		$this->_is_new = true;

		$this->date_created = new \DateTime();
		$this->comments = new \Doctrine\Common\Collections\ArrayCollection();
		$this->votes = new \Doctrine\Common\Collections\ArrayCollection();
		$this->labels = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getContent()
	{
		return $this->first_comment['content'];
	}

	public function getContentHtml()
	{
		return $this->first_comment->getContentHtml();
	}

	public function getUserEmail()
	{
		if ($this->person) {
			return $this->person->getPrimaryEmailAddress();
		} elseif ($this->first_comment['user_email']) {
			return $this->first_comment['user_email'];
		} else {
			return '';
		}
	}

	public function getUserName()
	{
		if ($this->person) {
			return $this->person->getDisplayName();
		} elseif ($this->first_comment['user_name']) {
			return $this->first_comment['user_name'];
		} else {
			return '';
		}
	}

	public function setFirstCommentText($text)
	{
		$comment = new IdeaComment();
		$comment->person = $this->person;
		$comment->content = $text;

		$this->setFirstComment($comment);
	}

	public function setFirstComment(IdeaComment $comment)
	{
		$this->_onPropertyChanged('first_comment', $this->first_comment, $comment);

		$comment->idea = $this;
		$this->first_comment = $comment;

		$this->comments->add($comment);

		return $comment;
	}

	public function addComment(IdeaComment $comment)
	{
		$comment->idea = $this;
		$this->comments->add($comment);

		return $comment;
	}

	public function getCategoryId()
	{
		return $this->category['id'];
	}

	public function setCategoryId($id)
	{
		$this->category = App::getEntityRepository('DeskPRO:IdeaCategory')->find($id);
	}


	public function recountVotes()
	{
		if (!$this->id) return;

		$this['num_votes'] = App::getDb()->fetchColumn("
			SELECT SUM(num_votes)
			FROM idea_votes
			WHERE idea_id = ?
		", array($this->id));
	}

	public function getUrlSlug()
	{
		return $this->id . '-' . Strings::slugifyTitle($this->title);
	}

	public function getLink()
	{
		$url = App::getRouter()->generate('user_ideas_view', array('slug' => $this->getUrlSlug()), true);

		return $url;
	}

	public function getPermalink()
	{
		$url = App::getRouter()->generate('user_ideas_view', array('slug' => $this->id), true);

		return $url;
	}

	/**
	 * Get a summary line.
	 *
	 * @return string
	 */
	public function getSummaryLine($max_len = 200)
	{
		$summary = $this->title;
		if (strlen($summary) < $max_len) {
			$summary .= '. ';
			$summary .= Strings::removeLineBreaks($this->first_comment['content']);
		}

		if (strlen($summary) > $max_len) {
			$summary = substr($summary, 0, $max_len);
		}

		return $summary;
	}

	public function getCategoryName()
	{
		return $this->category->getFullTitle();
	}

	public function setStatus($status)
	{
		$this->_onPropertyChanged('status', $this->status, $status);
		$this->status = $status;

		switch ($status) {
			case self::STATUS_NEW:
				$this['hidden_status'] = null;
				$this['status_category'] = null;
				break;

			case self::STATUS_ACTIVE:
			case self::STATUS_CLOSED:
				$this['hidden_status'] = null;
				break;

			case self::STATUS_HIDDEN:
				$this['status_category'] = null;
				break;
		}
	}

	public function setStatusCode($status_code)
	{
		if (strpos($status_code, '.') !== false) {
			list ($status, $sub_status) = explode('.', $status_code, 2);
		} else {
			$status = $status_code;
			$sub_status = null;
		}

		switch ($status) {
			case self::STATUS_NEW:
				$this['status'] = $status;
				break;

			case self::STATUS_ACTIVE:
			case self::STATUS_CLOSED:
				$this['status'] = $status;
				$status_cat = App::findEntity('DeskPRO:IdaeStatusCategory', $sub_status);
				$this->status_category = $status_cat['id'];
				break;

			case self::STATUS_HIDDEN:
				$this['status'] = $status;
				$this['hidden_status'] = $sub_status;
				break;
		}
	}

	public function getStatusCode()
	{
		if ($this->status == self::STATUS_ACTIVE OR $this->status == self::STATUS_CLOSED) {
			return $this->status . '.' . $this->status_category;
		} elseif ($this->status == self::STATUS_HIDDEN) {
			return $this->status . '.' . $this->hidden_status;
		} else {
			return $this->status;
		}
	}

	public function isValidating()
	{
		return ($this->hidden_status == self::HIDDEN_STATUS_VALIDATING);
	}

	public function getCategoryPath()
	{
		$path = array();

		$cat = $this->category;
		$path[] = $cat;
		while ($cat['parent']) {
			$cat = $cat['parent'];
			$path[] = $cat;
		}

		return $path;
	}

	public function getObject()
	{
		return $this->article;
	}

	/**
	 * @return \Application\DeskPRO\Labels\LabelManager
	 */
	public function getLabelManager()
	{
		if ($this->_label_manager === null) {
			$this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:LabelIdea');
		}

		return $this->_label_manager;
	}

	/**
	 * @ORM_Mapping\PostPersist
	 */
	public function _notifyNewIdea()
	{
		if ($this->_is_new) {
			$client_message = new ClientMessage();
			$client_message->fromArray(array(
				'channel' => 'agent-notification.new-idea',
				'data' => array(
					'idea_id'     => $this->id,
					'subject'     => $this->title,
					'author_id'   => $this->person ? $this->person['id'] : 0,
					'author_name' => $this->getUserName(),
				),
				'created_by_client' => 'sys'
			));

			App::getOrm()->transactional(function ($em) use ($client_message) {
				$em->persist($client_message);
				$em->flush();
			});
		}
	}
}
