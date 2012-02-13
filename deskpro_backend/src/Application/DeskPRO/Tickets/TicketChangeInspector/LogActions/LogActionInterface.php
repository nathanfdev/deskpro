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

interface LogActionInterface
{
	/**
	 * This should get a normalized plain PHP array of information
	 * about the action or change. These are usually pushed into
	 * a phrase or template for rendering
	 *
	 * The details here should be complete, enough information to show a summary/descripton
	 * without having to lookup the actual record. For example, if a category is deleted,
	 * this details array still has the 'title' so we can properly show the summary.
	 *
	 * @return array
	 */
	public function getLogDetails();

	/**
	 * Get the name of this log item
	 *
	 * @return string
	 */
	public function getLogName();

	/**
	 * The type of action this represents
	 */
	public function getEventType();
}
