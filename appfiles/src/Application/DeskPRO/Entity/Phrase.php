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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Templates used in the system
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Phrase")
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="phrases")
 */
class Phrase extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;
	
	/**
	 * The language this phrase belongs to
	 *
	 * @var Language
	 * @ORM_Mapping\ManyToOne(targetEntity="Language")
	 * @ORM_Mapping\JoinColumn(name="language_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $language;

	/**
	 * The name of the phrase
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="name", type="string", length=255)
	 */
	protected $name = null;

	/**
	 * Phrases can belong to groups. The group is the string
	 * before the first dot in the name. deskpro.profile, the group is 'deskpro'
	 *
	 * @var string
	 * @ORM_Mapping\Index
	 * @ORM_Mapping\Column(name="groupname", type="string", length=255, nullable=true)
	 */
	protected $groupname;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="phrase", type="text")
	 */
	protected $phrase;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="created_at",type="datetime")
	 */
	protected $created_at;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="updated_at",type="datetime")
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

	/** @ORM_Mapping\PreUpdate */
	public function incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}
}