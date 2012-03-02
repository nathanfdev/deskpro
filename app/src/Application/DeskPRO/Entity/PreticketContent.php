<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Tickets that were in the process of being created but were never finished,
 * or that were solved with auto-search.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="pretickets_content")
 */
class PreticketContent extends \Application\DeskPRO\Domain\DomainObject
{
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
	 * The department ID the ticket was in
	 *
	 * @ORM_Mapping\Column(name="department_id", type="integer")
	 */
	protected $department_id = 0;

	/**
	 * The subject
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="subject", type="string", length=255)
	 */
	protected $subject = '';

	/**
	 * The message
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="message", type="text")
	 */
	protected $message = '';

	/**
	 * Other raw form data
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="data", type="array")
	 */
	protected $data = array();

	/**
	 * If the person marked the ticket as solved after reading some content.
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_solved", type="boolean")
	 */
	protected $is_solved = false;

	/**
	 * Array of array(type,id) that the user said didnt answer their article.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="unsolved_content", type="array")
	 */
	protected $unsolved_content = array();

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="object_type", type="string", length=100, nullable=true)
	 */
	protected $object_type = null;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="object_id", type="integer", nullable=true)
	 */
	protected $object_id = null;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}

	/**
	 * @static
	 * @param Person $person
	 * @param bool $use_request Use the current request to set visitor (and thus ip etc)
	 * @return \Application\DeskPRO\Entity\CommentAbstract
	 */
	public static function newForPerson(Person $person, $use_request = true)
	{
		$preticket = new static();

		if (!$person->isGuest()) {
			$preticket->person = $person;
		}

		if ($use_request) {
			$preticket->visitor = App::getSession()->getVisitor();
		}

		return $preticket;
	}

	public function setContentObject($obj)
	{
		$this->object_type = $obj->getContentType();
		$this->object_id   = $obj->getId();
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
}
