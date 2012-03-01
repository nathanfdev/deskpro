<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Tickets\TicketChangeInspector\LogActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class Person implements LogActionInterface
{
	protected $old_person;
	protected $new_person;

	public function __construct($old_person, $new_person)
	{
		$this->old_person = $old_person;
		$this->new_person = $new_person;
	}

	public function getLogName()
	{
		return 'changed_person';
	}

	public function getLogDetails()
	{
		return array(
			'id_before' => $this->old_person['id'],
			'id_after'  => $this->new_person['id'],

			'old_person_id'     => $this->old_person['id'],
			'old_person_name'   => $this->old_person['display_name'],
			'old_person_email'  => $this->old_person['primary_email_address'],
			'new_person_id'     => $this->new_person['id'],
			'new_person_name'   => $this->new_person['display_name'],
			'new_person_email'  => $this->new_person['primary_email_address'],
		);
	}

	public function getEventType()
	{
		return 'property';
	}
}
