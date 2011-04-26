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

namespace Application\DeskPRO\Search;

use Orb\Util\CapabilityInformerInterface;

use \Application\DeskPRO\App;
use \Symfony\Component\DependencyInjection\ContainerInterface;

use Application\DeskPRO\Search\SearcherResult\ResultSet;
use Application\DeskPRO\Search\SearcherResult\ResultInterface;

/**
 * Search adapter
 */
abstract class AbstractAdapter implements CapabilityInformerInterface
{
	/**#@+
	 * Capability constants for use with CapabilityInformerInterface
	 */
	const CAP_CONTENT            = 'searcher_content';
	const CAP_CONTENT_LABELS     = 'searcher_content_labels';
	const CAP_TICKETS            = 'searcher_tickets';
	const CAP_TICKETS_SIMILAR    = 'searcher_tickets_similar';
	/**#@-*/

	/**#@+
	 * Standard ContentType constants
	 */
	const TYPE_ARTICLE            = 'Article';
	const TYPE_DOWNLOAD           = 'Download';
	const TYPE_IDEA               = 'Idea';
	const TYPE_NEWS               = 'News';
	const TYPE_TICKET             = 'Ticket';
	const TYPE_TICKET_MESSAGE     = 'TicketMessage';
	/**#@-*/

	/**
	 * Get a content searcher.
	 *
	 * Factory method.
	 *
	 * @return \Application\DeskPRO\Search\Searcher\ContentSearcherInterface
	 */
	abstract public function getContentSearcher();


	/**
	 * Get a ticket searcher.
	 *
	 * Factory method.
	 *
	 * @return \Application\DeskPRO\Search\Searcher\ContentSearcherInterface
	 */
	abstract public function getTicketSearcher();


	/**
	 * Get the contenttype handler for a specific type.
	 *
	 * Factory method.
	 *
	 * @param string $type
	 * @return \Application\DeskPRO\Search\ContentType\ContentTypeInterface
	 */
	abstract public function getContentType($type);


	/**
	 * Convert a result into its real object.
	 * 
	 * This is a shortcut of getting the content type for the result, and then
	 * using resultToObject on it.
	 *
	 * @param \Application\DeskPRO\Search\SearcherResult\ResultInterface $result
	 * @return mixed
	 */
	public function getResultObject(ResultInterface $result)
	{
		$type_name = $result->getContentType();
		$type = $this->getContentTypeName($type);

		$object = $type->resultToObject($result);

		return $object;
	}


	/**
	 * Convert an entire result set into an array of real objects.
	 *
	 * This is a shortcut for converting all results into objects.
	 *
	 * @param \Application\DeskPRO\Search\SearcherResult\ResultSet $result_set
	 * @return array
	 */
	public function getResultSetObjects(ResultSet $result_set)
	{
		#------------------------------
		# Sort results into types
		#------------------------------

		// We do this because the ContenTypes are created to efficiently
		// handle fetching multiple items at once. So we can fetch each of
		// the same type of content with a single query each

		$result_set_typed = array();

		foreach ($result_set->getResults() as $result) {
			$type_name = $result->getContentTypeName();

			if (!isset($result_set_typed[$type_name])) {
				$result_set_typed[$type_name][$result->getId()] = $result;
			}
		}

		#------------------------------
		# Get objects for each type
		#------------------------------

		$objects_typed = array();
		foreach ($result_set_typed as $type => $results) {

			$type = $this->getContentType($type_name);
			$objects = $type->resultsToObjects($results);

			if ($objects) {
				$objects_typed[$type] = $objects;
			}
		}

		#------------------------------
		# Now we have to construct the final array in the correct order
		#------------------------------

		$objects = array();

		foreach ($result_set->getResults() as $result) {
			$type_name = $result->getContentTypeName();
			$obj_id = $result->getId();

			if (!isset($objects_typed[$type_name])) continue;
			if (!isset($objects_typed[$type_name][$obj_id])) continue;

			$objects[] = $objects_typed[$type_name][$obj_id];
		}

		return $objects;
	}
}