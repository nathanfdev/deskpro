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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Interface for 'ContentSearcher'
 *
 * The content searcher searches: articles, downloads, feedback, news
 */
interface ContentSearcherInterface
{
	/**
	 * A natural text query
	 *
	 * @param  $query
	 * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
	 */
	public function query($query_text, $per_page = 25, $page = 1, array $limit_types = null);

	/**
	 * Fetch lablled content
	 *
	 * @param  $labels
	 * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
	 */
	public function labelled(array $labels, array $limit_types = null);

	/**
	 * Find content similar to $content.
	 *
	 * @param string $content
	 * @param array $in_types Types you want to search in, or null for all
	 * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
	 */
	public function similarContent($content, array $in_types = null);

	/**
	 * Results for the "omnisearch" search box
	 *
	 * @param string $content
	 * @return \Application\DeskPRO\Search\SearcherResult\ResultSet
	 */
	public function omnisearch($query_text);
}
