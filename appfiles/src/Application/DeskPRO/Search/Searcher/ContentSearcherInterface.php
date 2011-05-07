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
 * The content searcher searches: articles, downloads, ideas, news
 */
interface ContentSearcherInterface
{
	/**
	 * A natural text query
	 *
	 * @param  $query
	 * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
	 */
	public function query($query);


	/**
	 * Fetch lablled content
	 *
	 * @param  $labels
	 * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
	 */
	public function labelled(array $labels);

	/**
	 * Find articles that are similar to a ticket
	 * 
	 * @param \Application\DeskPRO\Entity\Ticket $ticket
	 * @return void
	 */
	public function similarArticleToTicket(Ticket $ticket);
}