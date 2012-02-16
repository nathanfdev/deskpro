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

class Urgency implements LogActionInterface
{
	protected $old_urgency;
	protected $new_urgency;
	protected $reset_next_reply = null;

	public function __construct($old_urgency, $new_urgency, $reset_next_reply = null)
	{
		$this->old_urgency = $old_urgency;
		$this->new_urgency = $new_urgency;
		$this->reset_next_reply = $reset_next_reply;
	}

	public function getLogName()
	{
		return 'changed_urgency';
	}

	public function getLogDetails()
	{
		return array(
			'id_before' => $this->old_urgency ?: null,
			'id_after'  => $this->new_urgency ?: null,

			'old_urgency' => $this->old_urgency,
			'new_urgency' => $this->new_urgency,
			'reset_next_reply' => $this->reset_next_reply
		);
	}

	public function getEventType()
	{
		return 'property';
	}
}
