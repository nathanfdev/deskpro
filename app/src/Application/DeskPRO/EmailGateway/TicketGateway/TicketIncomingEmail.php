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
 * @category EmailGateway
 */

namespace Application\DeskPRO\EmailGateway\TicketGateway;

/**
 * A simple struct for keeping track of some ticket email properties
 */
class TicketIncomingEmail
{
	/**
	 * @var \Application\DeskPRO\EmailGateway\Reader\AbstractReader
	 */
	public $reader;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket|null
	 */
	public $ticket;

	/**
	 * @var \Application\DeskPRO\Entity\Person|null
	 */
	public $person;

	/**
	 * @var \Application\DeskPRO\Entity\Person|null
	 */
	public $tac_person;

	/**
	 * @var bool
	 */
	public $is_bounce;

	/**
	 * @var bool
	 */
	public $force_reply_cutter = false;

	/**
	 * @var array|null
	 */
	public $reply_actions;

	/**
	 * @var string
	 */
	public $email_body_html;

	/**
	 * @var string
	 */
	public $email_body_text;

	/**
	 * @var bool
	 */
	public $is_dp3_reply = false;
}