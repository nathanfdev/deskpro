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
use Application\DeskPRO\Entity\Ticket;

class Subject implements LogActionInterface
{
	protected $old_subject;
	protected $new_subject;

	public function __construct($old_subject, $new_subject)
	{
		$this->old_subject = $old_subject;
		$this->new_subject = $new_subject;
	}

	public function getLogName()
	{
		return 'changed_subject';
	}

	public function getLogDetails()
	{
		return array(
			'old_subject' => $this->old_subject,
			'new_subject' => $this->new_subject,
		);
	}

	public function getEventType()
	{
		return 'property';
	}
}
