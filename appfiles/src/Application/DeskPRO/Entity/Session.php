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

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Active user sessions
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Session")
 * @orm:Table(name="sessions", indexes={
 *     @orm:Index(name="date_last_idx", columns={"date_last","is_person"})
 * })
 */
class Session extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer")
	 */
	protected $id;

	/**
	 * The authcode for the session to verify an id
	 *
	 * @var string
	 * @orm:Column(name="auth", type="string", length=15)
	 */
	protected $auth;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\Visitor
	 * @orm:ManyToOne(targetEntity="Visitor", fetch="EAGER")
	 * @orm:JoinColumn(name="visitor_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $visitor = null;

	/**
	 * @var string
	 * @orm:Column(name="data", type="text")
	 */
	protected $data = '';

	/**
	 * @var string
	 * @orm:Column(name="is_person", type="boolean")
	 */
	protected $is_person = false;

	/**
	 * For agents, if they are available for chat or away
	 * @var string
	 * @orm:Column(name="active_status", type="string", length=15)
	 */
	protected $active_status = '';

	/**
	 * @var int
	 * @orm:Column(name="page_count", type="integer")
	 */
	protected $page_count = 0;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_last",type="datetime")
	 */
	protected $date_last;

	public function __construct()
	{
		$this->auth = Strings::random(15, Strings::CHARS_KEY);
		$this->date_created = new \DateTime();
		$this->date_last = new \DateTime();
	}



	/**
	 * Gets the session ID for this session. It's an encoded ID and an authcode.
	 *
	 * @return string
	 */
	public function getSessionCode()
	{
		$id_enc = Util::baseEncode($this->id, Util::BASE36_ALPHABET);
		return $id_enc . '-' . $this->auth;
	}



	/**
	 * Check a session code against some kind o finput to see
	 * if they match.
	 *
	 * @return bool
	 */
	public function checkSessionCode($session_code)
	{
		return ($this->getSessionCode() === $session_code);
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
			$this->is_person = true;
			$this->person = App::getEntityRepository('DeskPRO:Person')->find($person_id);
		} else {
			$this->is_person = false;
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

	
	/**
	 * A secret hash of this session key with the app secret.
	 *
	 * Most notably used as the "proxy key"
	 *
	 * @param  string $secret Another component to add to the hash
	 * @return string
	 */
	public function getSessionSecret($secret = '')
	{
		return md5($this->id . $this->auth . App::getAppSecret() . $secret);
	}

	

	public function updateLastTime()
	{
		$this->date_last = new \DateTime();
	}

	public static function getIdFromCode($sess_code)
	{
		if (!strpos($sess_code, '-')) return null;

		list ($session_id, ) = explode('-', $sess_code, 2);
		$session_id = Util::baseDecode($session_id, Util::BASE36_ALPHABET);

		return $session_id;
	}
}