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

namespace Application\DeskPRO\TicketLayout\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Exception\NotImplementedException;
use Orb\Util\Util;

abstract class AbstractTicketLayoutTerm implements TicketLayoutTermInterface
{
	const OP_IS  = 'is';
	const OP_NOT = 'not';

	/**
	 * @var
	 */
	protected $op;

	/**
	 * @var array
	 */
	protected $options;


	/**
	 * @param string $op
	 * @param array  $options
	 */
	public function __construct($op, array $options)
	{
		$this->op      = $op;
		$this->options = $options;
	}


	/**
	 * Gets the type name of the criteria
	 *
	 * @return string
	 */
	public function getTermType()
	{
		return Util::getBaseClassname($this);
	}


	/**
	 * Gets criteria operator (is, is not, etc).
	 *
	 * @return string
	 */
	public function getTermOperator()
	{
		return $this->op;
	}


	/**
	 * Get's an array of options
	 *
	 * @return array
	 */
	public function getTermOptions()
	{
		return $this->options;
	}

	/**
	 * Used on the server-side to check if the term matches.
	 *
	 * @param Ticket $ticket
	 * @return bool
	 */
	public function isTicketMatch(Ticket $ticket)
	{
		// Cant be abstract in older versions of php see https://bugs.php.net/bug.php?id=43200
		throw new NotImplementedException;
	}

	/**
	 * Should return a JS function that accepts a ticket object and returns true/false
	 * depending on if the term passes/fails.
	 *
	 * @return string
	 */
	public function compileJsCheck()
	{
		// Cant be abstract in older versions of php see https://bugs.php.net/bug.php?id=43200
		throw new NotImplementedException;
	}
}