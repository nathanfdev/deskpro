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

use Application\DeskPRO\App;

/**
 * For each participant on a ticket, they get an access code. Normally user
 * participants dont use the TAC because they all share the public TAC that is set
 * on the ticket itself via CC'ing. But agents always use a TAC.
 *
 * So there's TAC's (this) and PTAC's (public ticket access code) that is attached to the ticket.
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
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $ticket;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person;

	/**
	 * @var int
	 * @orm:Column(name="auth", type="string", length=20)
	 */
	protected $auth;

	public function __construct()
	{
		$len = App::getSetting('core_tickets.tac_auth_code_len');
		$this->auth = Strings::random($len, Strings::CHARS_KEY);
	}


	/**
	 * Encodes the ticket ID and the auth into a single string.
	 *
	 * @return string
	 */
	public function getAccessCode()
	{
		$str = Util::baseEncode($this->id, 'letters');
		$str .= $this->auth;

		return $str;
	}


	/**
	 * Get the Message-ID field for an email regarding this ticket, witht he
	 * embedded TAC code.
	 *
	 * @return string
	 */
	public function getUniqueEmailMessageId()
	{
		$uid = 'TAC-' . $this->getAccessCode() . '.';
		$uid .= uniqid('', true) . '-' . App::getSetting('core.site_id');
		$uid .= '@' . md5(App::getSetting('core.site_url', 'deskpro'));

		return $uid;
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
		$len = App::getSetting('core_tickets.tac_auth_code_len');

		if (strlen($access_code) < ($len+1)) return false;

		$matches = Strings::extractRegexMatch('#^(.+)(.{'.$len.'})$#', $access_code, -1);
		if (!$matches) return false;

		list (, $access_code_id, $auth) = $matches;

		$access_code_id = Util::baseDecode($access_code_id, 'letters');

		return array(
			'access_code_id' => $access_code_id,
			'auth'           => $auth
		);
	}
}