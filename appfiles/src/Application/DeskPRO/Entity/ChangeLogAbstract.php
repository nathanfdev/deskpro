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

/**
 * Tracks changes on objects
 *
 * @ORM_Mapping\MappedSuperclass
 */
abstract class ChangeLogAbstract extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * Who was responsible for the change.
	 *
	 * Null without is_system means we don't know (ie user was deleted). Otherwise, we should always
	 * have a person.
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * The users name and email address. This copy is saved incase the user is deleted, we'll at least
	 * have the name.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="action_type", type="string", length=255)
	 */
	protected $person_record = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="action_type", type="string", length=40)
	 */
	protected $action_type;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="details", type="array")
	 */
	protected $details = array();

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}

	public function getPersonId()
	{
		if ($this->person) {
			return $this->person['id'];
		}

		return 0;
	}
}
