<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @subpackage UserBundle
 */

namespace Application\UserBundle\Tickets;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

class NewParticipant implements \ArrayAccess
{
	/** @var array */
	protected static $prop_names = array(
		'first_name' => 1, 'last_name' => 1, 'email' => 1,
	);

	/** @var string */
	public $first_name;
	/** @var string */
	public $last_name;
	/** @var string */
	public $email;

	/** @var \Application\DeskPRO\Entity\Ticket */
	protected $ticket;

	public function __construct(Ticket $ticket)
	{
		$this->ticket = $ticket;
	}

	public function save()
	{
		// User already on the tikcet, dont do anything
		if ($this->ticket->findUserByEmail($this->email)) {
			return;
		}

		$ticket = $this->ticket;
		$part_person = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($this->email);

		if (!$part_person) {
			$part_person = new Person();
			$part_person->addEmailAddressString($this->email);
			$part_person['first_name'] = $this->first_name;
			$part_person['last_name'] = $this->last_name;
		}

		if (!$part_person['first_name'] AND $this->first_name) {
			$part_person['first_name'] = $this->first_name;
		}
		if (!$part_person['last_name'] AND $this->last_name) {
			$part_person['last_name'] = $this->last_name;
		}

		$part_email = $part_person->findEmailAddress($this->email);

		$ticket->addParticipant($part_person);

		App::getOrm()->transactional(function($em) use ($part_person, $part_email, $ticket) {
			$em->persist($part_person);
			$em->persist($ticket);
			$em->flush();
		});
	}

	public function offsetExists($offset)        { return (isset(self::$prop_names[$offset]) && isset($this->$offset)); }
    public function offsetGet($offset)           { if (isset(self::$prop_names[$offset])) return $this->$offset; }
    public function offsetSet($offset, $value)   { if (isset(self::$prop_names[$offset])) $this->$offset = $value; }
    public function offsetUnset($offset)         { if (isset(self::$prop_names[$offset])) $this->$offset = null; }
}