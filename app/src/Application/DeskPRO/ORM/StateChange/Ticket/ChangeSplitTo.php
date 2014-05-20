<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @category Entities
 */

namespace Application\DeskPRO\ORM\StateChange\Ticket;

use Application\DeskPRO\ORM\StateChange\ChangeInterface;
use Application\DeskPRO\ORM\StateChange\NonStateTrackingInterface;

class ChangeSplitTo implements ChangeInterface, NonStateTrackingInterface
{
	/**
	 * @var string
	 */
	private $field_id;

	/**
	 * @var int
	 */
	private $new_ticket_id;

	/**
	 * @var array
	 */
	private $message_ids;

	/**
	 * @param $field_id
	 * @param $new_ticket_id
	 * @param array $message_ids
	 */
	public function __construct($field_id, $new_ticket_id, array $message_ids = array())
	{
		$this->field_id      = $field_id;
		$this->new_ticket_id = $new_ticket_id;
		$this->message_ids   = $message_ids;
	}


	/**
	 * @return string
	 */
	public function getField()
	{
		return $this->field_id;
	}


	/**
	 * @return array
	 */
	public function getOld()
	{
		return null;
	}


	/**
	 * @return array
	 */
	public function getNew()
	{
		return array(
			'field_id'      => $this->field_id,
			'new_ticket_id' => $this->new_ticket_id,
			'message_ids'   => $this->message_ids
		);
	}


	/**
	 * @return array
	 */
	public function getMessageIds()
	{
		return $this->message_ids;
	}


	/**
	 * @return int
	 */
	public function getNewTicketId()
	{
		return $this->new_ticket_id;
	}


	/**
	 * @return bool
	 */
	public function isSame()
	{
		return false;
	}


	/**
	 * @return bool
	 */
	public function isCollection()
	{
		return false;
	}


	/**
	 * @return bool
	 */
	public function isEntity()
	{
		return false;
	}
}