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

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Visitor records try to identify a user long-term across many visits by
 * placing a cookie that doesn't expire. This can be used by some tracking,
 * but it's also used by things like anonymous voting to prevent repeat votes and such.
 *
 * A visitor is 1) Someone who has the correct visitor code or 2) someone who
 * is using the same IP address within 1 day with same browser useragent
 *
 * It's sortof like a session except its not used for anything dangerous like granting
 * access to things.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Visitor")
 * @ORM_Mapping\Table(name="visitors", indexes={
 *     @ORM_Mapping\Index(name="date_last_idx", columns={"date_last"})
 * })
 */
class Visitor extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id;

	/**
	 * The authcode to verify an id
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="auth", type="string", length=15)
	 */
	protected $auth;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * The users IP address
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="ip_address", type="string", length=80)
	 */
	protected $ip_address;

	/**
	 * The users user agent string
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="user_agent", type="string", length=255)
	 */
	protected $user_agent = '';

	/**
	 * The page the user came from
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="ref_page", type="string", length=255)
	 */
	protected $ref_page = '';

	/**
	 * The page the user came from
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="landing_page", type="string", length=255)
	 */
	protected $landing_page = '';

	/**
	 * The last page the user was on
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="last_page", type="string", length=255)
	 */
	protected $last_page = '';

	/**
	 * The users name. Sometimes we might ask the users name, so we can
	 * save it in the visitor record for future reference
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="name", type="string", length=255)
	 */
	protected $name = '';

	/**
	 * The users email, like the name above
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="email", type="string", length=255)
	 */
	protected $email = '';

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_last",type="datetime")
	 */
	protected $date_last;

	protected $_changed_last_page = false;
	protected $is_new = false;

	public function __construct()
	{
		$this->is_new = true;
		$this->auth = Strings::random(15, Strings::CHARS_KEY);
		$this->date_created = new \DateTime();
		$this->date_last = new \DateTime();
	}

	public function isNew()
	{
		return $this->is_new;
	}



	/**
	 * Gets the ID for this vis. It's an encoded ID and an authcode.
	 *
	 * @return string
	 */
	public function getVisitorCode()
	{
		$id_enc = Util::baseEncode($this->id, Util::BASE36_ALPHABET);
		return $id_enc . '-' . $this->auth;
	}


	/**
	 * Set the last page
	 * @param string $page
	 * @return void
	 */
	public function setLastPage($page)
	{
		if ($page != $this->last_page) {
			$this->setModelField('last_page', $page);
			$this->_changed_last_page = true;
		}
	}

	public function hasChangedLastPage()
	{
		return $this->_changed_last_page;
	}



	/**
	 * Check a vis code against some kind o finput to see
	 * if they match.
	 *
	 * @return bool
	 */
	public function checkVisitorCode($vis_code)
	{
		return ($this->getVisitorCode() === $vis_code);
	}



	public function setPerson(Person $person = null)
	{
		if (!$person) {
			$this->setPersonId(0);
		} else {
			$this->setPersonId($person['id']);
		}
	}

	public function setPersonId($person_id)
	{
		if ($person_id) {
			$this->person = App::getEntityRepository('DeskPRO:Person')->find($person_id);
		} else {
			$this->person = null;
		}
	}

	public function getPersonId()
	{
		if ($this->person) {
			return $this->person['id'];
		}
		return 0;
	}

	public function updateLastTime()
	{
		$this->date_last = new \DateTime();
	}

	public static function getIdFromCode($vis_code)
	{
		if (!strpos($vis_code, '-')) return null;

		list ($vis_id, ) = explode('-', $vis_code, 2);
		$vis_id = Util::baseDecode($vis_id, Util::BASE36_ALPHABET);

		return $vis_id;
	}
}
