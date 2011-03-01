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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Templates used in the system
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="phrases")
 */
class Phrase extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The language this phrase belongs to
	 *
	 * @var Style
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Language")
	 * @orm:JoinColumn(name="language_id", referencedColumnName="id")
	 */
	protected $language;

	/**
	 * The name of the phrase
	 *
	 * @var string
	 * @orm:Id
	 * @orm:Column(name="name", type="string", length=255)
	 */
	protected $name = null;

	/**
	 * Phrases can belong to groups. The group is the string
	 * before the first dot in the name. deskpro.profile, the group is 'deskpro'
	 *
	 * @var string
	 * @orm:Index
	 * @orm:Column(name="groupname", type="string", length=255, nullable=true)
	 */
	protected $groupname;

	/**
	 * @var string
	 * @orm:Column(name="phrase", type="text")
	 */
	protected $phrase;

	/**
	 * @var \DateTime
	 * @orm:Column(name="created_at",type="datetime")
	 */
	protected $created_at;

	/**
	 * @var \DateTime
	 * @orm:Column(name="updated_at",type="datetime")
	 */
	protected $updated_at;

	public function __construct()
	{
		$this->created_at = $this->updated_at = new \DateTime();
	}

	public function setName($name)
	{
		$this->name = $name;
		$dotpos = strpos($this->name, '.');
		if ($dotpos) {
			$this->groupname = substr($this->name, 0, $dotpos);
		} else {
			$this->groupname = null;
		}
	}

	/** @orm:PreUpdate */
	public function incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}
}