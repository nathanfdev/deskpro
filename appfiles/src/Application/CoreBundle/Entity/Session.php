<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Active user sessions
 *
 * @orm:Entity
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="sessions")
 */
class Session extends \DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;


	/**
	 * The authcode for the session to verify an id
	 *
	 * @var string
	 * @orm:Column(name="password", type="string", length=15)
	 */
	protected $auth = null;


	/**
	 * The user this session belong to
	 *
	 * @var int
	 * @orm:Column(name="user_id", type="integer", nullable=true)
	 */
	protected $user_id = null;


	/**
	 * @var string
	 * @orm:Column(name="data", type="text")
	 */
	protected $data = '';


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
		$this->auth = Strings::random(15, Strings::CHARS_ALPHA_I);
	}



	/**
	 * Gets the session ID for this session. It's an encoded ID and an authcode.
	 *
	 * @return string
	 */
	public function getSessionId()
	{
		$id_enc = Util::baseEncode($this->id, Util::BASE36_ALPHABET);
		return $id_enc . '-' . $this->auth;
	}


	
	/** @orm:PrePersist */
	public function incCreatedAt()
	{
		$this->created_at = $this->updated_at = new \DateTime();
	}

	/** @orm:PreUpdate */
	public function incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}
}