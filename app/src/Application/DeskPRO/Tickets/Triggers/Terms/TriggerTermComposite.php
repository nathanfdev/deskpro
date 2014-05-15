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

namespace Application\DeskPRO\Tickets\Triggers\Terms;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;

class TriggerTermComposite implements TriggerTermInterface, \Countable
{
	const OP_AND = 'AND';
	const OP_OR  = 'OR';

	/**
	 * @var TriggerTermInterface[]
	 */
	private $terms = array();

	/**
	 * @var string
	 */
	private $op = 'AND';


	/**
	 * @param TriggerTermInterface[] $terms
	 * @param string $op
	 */
	public function __construct(array $terms = array(), $op = self::OP_AND)
	{
		$this->setAll($terms);
		$this->setOperator($op);
	}


	/**
	 * Change the logic operator between AND/OR ('all must match' versus 'any match')
	 *
	 * @param string $op
	 */
	public function setOperator($op)
	{
		$this->op = (strtoupper($op) == self::OP_AND ? self::OP_AND : self::OP_OR);
	}


	/**
	 * @return string
	 */
	public function getOperator()
	{
		return $this->op;
	}


	/**
	 * @param TriggerTermInterface $term
	 */
	public function add(TriggerTermInterface $term)
	{
		$this->terms[] = $term;
	}


	/**
	 * @param TriggerTermInterface[] $terms
	 */
	public function setAll(array $terms)
	{
		$this->terms = array();
		foreach ($terms as $t) {
			$this->add($t);
		}
	}


	/**
	 * @return TriggerTermInterface[]
	 */
	public function getAll()
	{
		return $this->terms;
	}


	/**
	 * {@inheritDoc}
	 */
	public function isTriggerMatch(Ticket $ticket, ExecutorContextInterface $context)
	{
		if (!$this->terms) {
			return true;
		}

		if ($this->op == self::OP_AND) {
			foreach ($this->terms as $t) {
				if (!$t->isTriggerMatch($ticket, $context)) {
					return false;
				}
			}

			return true;
		} else {
			foreach ($this->terms as $t) {
				if ($t->isTriggerMatch($ticket, $context)) {
					return true;
				}
			}

			return false;
		}
	}


	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->terms);
	}
}