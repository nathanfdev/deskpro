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

/**
 * For each participant on a ticket, they get an access code.
 * The access code lets the user access this specific ticket without
 * logging in. It's used in links to the ticket, as well as in emails
 * when the 'code' scheme is being used.
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TicketAcccessCode")
 * @orm:Table(name="ticket_access_codes")
 */
class TicketAccessCode extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Ticket")
	 * @orm:JoinColumn(name="ticket_id", referencedColumnName="id")
	 */
	protected $ticket;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Person")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * @var int
	 * @orm:Column(name="code", type="string", length=5)
	 */
	protected $code;

	public function __construct()
	{
		$this->code = \Orb\Util\Strings::random(5, \Orb\Util\Strings::CHARS_ALPHA_IU);
	}



	/**
	 * Encodes the ticket ID and the code into a single string.
	 *
	 * @return string
	 */
	public function getAccessCode()
	{
		$str = \Orb\Util\Util::baseEncode($this->ticket['id'], 'letters');
		$str .= $this->code;

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
		if (strlen($access_code) < 5) return false;

		list (, $ticket_id, $code) = \Orb\Util\Strings::extractRegexMatch('#^(.)(.{5})$#', $access_code, -1);

		return array(
			'ticket_id' => $ticket_id,
			'code' => $code
		);
	}
}