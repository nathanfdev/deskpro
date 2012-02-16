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

class ParticipantRemoved implements LogActionInterface
{
	protected $part;

	public function __construct($part)
	{
		$this->part = $part;
	}

	public function getLogName()
	{
		return 'participant_removed';
	}

	public function getLogDetails()
	{
		return array(
			'id_before'  => $this->part->id,
			'id_after' => null,

			'person_id' => $this->part->id,
			'name' => $this->part->getDisplayName(),
			'email' => $this->part->getPrimaryEmailAddress()
		);
	}

	public function getEventType()
	{
		return 'property';
	}
}
