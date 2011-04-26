<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Search
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Search\Searcher;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Person;
use \Application\DeskPRO\Entity\Ticket;

use \Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interface for 'ContentSearcher'
 *
 * The content searcher searches: tickets, ticket messages
 */
interface TicketSearcherInterface
{
	/**
	 * Sets the person context. This is where permissions should be fetched from.
	 *
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @return void
	 */
	public function setPersonContext(Person $person);

	
	/**
	 * A natural text query
	 *
	 * @param  $query
	 * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
	 */
	public function query($query);


	/**
	 * Fetch tickets similar to this ticket
	 *
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
	 */
	public function similar(Ticket $ticket);
}