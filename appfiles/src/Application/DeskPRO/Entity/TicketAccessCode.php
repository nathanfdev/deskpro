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

use Orb\Util\Util;
use Orb\Util\Strings;

/**
 * For each participant on a ticket, they get an access code.
 * The access code lets the user access this specific ticket without
 * logging in. It's used in links to the ticket, as well as in emails
 * when the 'code' scheme is being used.
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketAccessCode")
 * @orm:Table(name="ticket_access_codes")
 */
class TicketAccessCode extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @orm:ManyToOne(targetEntity="Ticket")
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id")
	 */
	protected $ticket;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * @var int
	 * @orm:Column(name="auth", type="string", length=5)
	 */
	protected $auth;

	public function __construct()
	{
		$this->auth = Strings::random(5, Strings::CHARS_ALPHA_IU);
	}



	/**
	 * Encodes the ticket ID and the auth into a single string.
	 *
	 * @return string
	 */
	public function getAccessCode()
	{
		$str .= Util::baseEncode($this->id, 'letters');
		$str .= $this->auth;

		return $str;
	}



	/**
	 * Decoes an access code into a ticket id and the standalone code. You can look
	 * up the record later to verify, get the user etc.
	 *
	 * @param  $access_code
	 * @return array
	 */
	public static function decodeAccessCode($access_code)
	{
		if (strlen($access_code) < 6) return false;

		$matches = Strings::extractRegexMatch('#^(.+)(.{5})$#', $access_code, -1);
		if (!$matches) return false;

		list (, $access_code_id, $auth) = $matches;

		$access_code_id = Util::baseDecode($access_code_id, 'letters');

		return array(
			'access_code_id' => $access_code_id,
			'auth'           => $auth
		);
	}
}