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

use Application\DeskPRO\Markdown;

use Application\DeskPRO\App;

use Orb\Util\Util;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Base comments
 *
 * @ORM_Mapping\MappedSuperclass
 */
abstract class CommentAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	const STATUS_VISIBLE    = 'visible';
	const STATUS_VALIDATING = 'validating';
	const STATUS_DELETED    = 'deleted';
	const STATUS_AGENT      = 'agent';

	/**
	 * The unique ID.
	 *
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
	 * @var \Application\DeskPRO\Entity\Visitor
	 * @ORM_Mapping\ManyToOne(targetEntity="Visitor", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="visitor_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $visitor = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="ip_address", type="string", length=30)
	 */
	protected $ip_address = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="email", type="string", length=255, nullable=true)
	 */
	protected $email = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="name", type="string", length=255, nullable=true)
	 */
	protected $name = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="content", type="text")
	 */
	protected $content;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="status", type="string", length=30)
	 */
	protected $status = 'visible';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="validating", type="string", length=35, nullable=true)
	 */
	protected $validating = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @static
	 * @param Person $person
	 * @param bool $use_request Use the current request to set visitor (and thus ip etc)
	 * @return \Application\DeskPRO\Entity\CommentAbstract
	 */
	public static function newForPerson(Person $person, $use_request = true)
	{
		$comment = new static();
		$comment->person = $person;

		if ($use_request) {
			$comment->visitor = App::getSession()->getVisitor();
		}

		return $comment;
	}

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}

	public function getUserEmail()
	{
		if ($this->person) {
			return $this->person->getPrimaryEmailAddress();
		} elseif ($this->email) {
			return $this->email;
		} else {
			return '';
		}
	}

	public function getUserName()
	{
		if ($this->person) {
			return $this->person->getDisplayName();
		} elseif ($this->name) {
			return $this->name;
		} else {
			return '';
		}
	}

	public function setVisitor(Visitor $visitor = null)
	{
		$this->_onPropertyChanged('visitor', $this->visitor, $visitor);
		$this->visitor = $visitor;

		if ($visitor === null) return;

		$this['ip_address'] = $visitor['ip_address'];

		if (!$this->name AND $visitor['name']) {
			$this['name'] = $visitor['name'];
		}
		if (!$this->email AND $visitor['email']) {
			$this['email'] = $visitor['email'];
		}
	}

	public function getContentHtml()
	{
		return Markdown::format(htmlspecialchars($this->content, \ENT_NOQUOTES, 'UTF-8'));
	}

	public function getContentHtmlPlain()
	{
		return nl2br(htmlspecialchars($this->content));
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

	public function getPersonId()
	{
		if ($this->person) {
			return $this->person->getId();
		}

		return 0;
	}

	/**
	 * Get the entity this comment is attached to. This is a standardized way to fetch the
	 * entity when you might not know the $comment->XXX to use.
	 *
	 * @return mixed
	 */
	abstract function getObject();


	public function getObjectType()
	{
		return Util::getBaseClassname($this->getObject());
	}


	/**
	 * Get the "content-type" of the object on this comment
	 *
	 * @return string
	 */
	public function getObjectContentType()
	{
		return $this->getObject()->getTableName();
	}
}
