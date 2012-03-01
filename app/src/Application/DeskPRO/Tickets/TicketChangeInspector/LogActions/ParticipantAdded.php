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

class ParticipantAdded implements LogActionInterface
{
	protected $part;

	public function __construct($part)
	{
		$this->part = $part;
	}

	public function getLogName()
	{
		return 'participant_added';
	}

	public function getLogDetails()
	{
		return array(
			'id_before' => null,
			'id_after'  => $this->part->id,

			'person_id' => $this->part->person ? $this->part->person->id : 0,
			'name'      => $this->part->getDisplayName(),
			'email'     => $this->part->getPrimaryEmailAddress(),
			'is_agent'  => $this->part->person ? $this->part->person->is_agent : false
		);
	}

	public function getEventType()
	{
		return 'property';
	}
}
