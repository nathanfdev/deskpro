<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\NewTicket;

use Application\DeskPRO\Entity;

/**
 * This wraps up the 'person' data of a newticket.
 */
class PersonProps
{
	/**
	 * A real person object, represents a logged in user
	 * if the user is logged in. Otherwise this should be null for a guest
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 */
	public $person_obj;

	public $name = '';
	public $email = '';

	public function __construct(Entity\Person $person = null)
	{
		$this->person_obj = $person;

		if ($person) {
			$this->first_name = $person['first_name'];
			$this->last_name  = $person['last_name'];
			if ($person['first_name'] && $person['last_name']) {
				$this->name = $this->first_name . ' ' . $this->last_name;
			} elseif ($person['name']) {
				$this->name = $person['name'];
			} else {
				$this->name = '';
			}

			$this->email = $person->getPrimaryEmailAddress();
		}
	}
}
