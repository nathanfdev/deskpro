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

namespace Application\DeskPRO\Tickets\TicketMerge\Property;


use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Ticket;

use Orb\Util\Arrays;

/**
 * A property is something that can be merged in a ticket
 */
abstract class PropertyAbstract
{
	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $ticket;

	/**
	 * @var \Application\DeskPRO\Entity\Ticket
	 */
	protected $other_ticket;

	/**
	 * @var string
	 */
	protected $strategy = null;

	/**
	 * @var array
	 */
	protected $strategy_options = array();

	const STRATEGY_LEFT     = 'left';
	const STRATEGY_RIGHT    = 'right';
	const STRATEGY_COMBINE  = 'merge';


	public function __construct(Ticket $ticket, Ticket $other_ticket)
	{
		$this->ticket = $ticket;
		$this->other_ticket = $other_ticket;
	}

	
	/**
	 * Merge the two tickets
	 */
	abstract public function merge();


	/**
	 * Set the merge strategy (how to handle conflicts)
	 * 
	 * @param string $strategy
	 * @return void
	 */
	public function setStrategy($strategy, array $options = array())
	{
		$this->strategy = $strategy;
		$this->options = $options;
	}

	
	/**
	 * @return string
	 */
	public function getStrategy()
	{
		return $this->strategy;
	}


	/**
	 * Get a strategy option
	 *
	 * @param string $name Name of the option
	 * @param string $default The default value if it wasnt set
	 * @return mixed
	 */
	public function getStrategyOption($name, $default = null)
	{
		return isset($this->strategy_options[$name]) ? $this->strategy_options[$name] : $default;
	}
}