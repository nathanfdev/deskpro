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

namespace Application\DeskPRO\Search\Adapter;

use Orb\Util\CapabilityInformerInterface;

use Application\DeskPRO\App;
use Application\DeskPRO\People\PersonContextInterface;
use Application\DeskPRO\Entity\Person;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Application\DeskPRO\Search\EntityListener;

use Application\DeskPRO\Search\SearcherResult\ResultSet;
use Application\DeskPRO\Search\SearcherResult\ResultInterface;

/**
 * Search adapter
 */
abstract class AbstractAdapter implements CapabilityInformerInterface, PersonContextInterface
{
	public static $capabilities = array();
	
	/**#@+
	 * Capability constants for use with CapabilityInformerInterface
	 */
	const CAP_CONTENT                             = 'searcher_content';
	const CAP_CONTENT_LABELS                      = 'searcher_content_labels';
	const CAP_CONTENT_SIMILAR_ARTICLES            = 'searcher_content_similar';
	const CAP_TICKETS                             = 'searcher_tickets';
	const CAP_TICKETS_SIMILAR                     = 'searcher_tickets_similar';
	/**#@-*/

	/**#@+
	 * Standard ContentType constants
	 */
	const TYPE_ARTICLE            = 'article';
	const TYPE_DOWNLOAD           = 'download';
	const TYPE_IDEA               = 'idea';
	const TYPE_NEWS               = 'news';
	const TYPE_TICKET             = 'ticket';
	const TYPE_TICKET_MESSAGE     = 'ticketMessage';
	/**#@-*/

	/**
	 * @var array
	 */
	protected $class_to_contenttype = array();

	/**
	 * An array of already initialized contenttypes
	 * @var array
	 */
	protected $contenttypes = array();

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;


	/**
	 * Check if this object is capable of a specific thing
	 *
	 * @param  mixed $capability
	 * @return bool
	 */
	public function isCapable($capability)
	{
		return in_array($capability, static::$capabilities);
	}


	/**
	 * Returns an array of all capabilities
	 *
	 * @return array
	 */
	public function getCapabilities()
	{
		return static::$capabilities;
	}


	/**
	 * @param \Application\DeskPRO\Entity\Person $person
	 */
	public function setPersonContext(Person $person)
	{
		$this->person = $person;
	}


	/**
	 * Get person context
	 * 
	 * @return \Application\DeskPRO\Entity\Person
	 */
	public function getPersonContext()
	{
		if (!$this->person) {
			return App::getCurrentPerson();
		}

		return $this->person;
	}


	/**
	 * Get the map of classes to contenttypes
	 *
	 * @return array
	 */
	public function getContentTypeMap()
	{
		return $this->class_to_contenttype;
	}

	
	/**
	 * Adds a mapping that maps a class to a contenttype
	 *
	 * @param string $class The full class name
	 * @param string $type_name The type name
	 */
	public function addContentTypeMap($class, $type_name)
	{
		$this->class_to_contenttype[$class] = $type_name;
	}


	/**
	 * Get the contenttype name we have mapped to an object
	 *
	 * @return string
	 */
	public function getContentTypeNameForObject($object)
	{
		$class = get_class($object);
		if (!isset($this->class_to_contenttype[$class])) {
			throw new \InvalidArgumentException("Unknown contenttype for supplied object");
		}

		return $this->class_to_contenttype[$class];
	}


	/**
	 * Get the actual contenttype handler for the object
	 *
	 * @return \Application\DeskPRO\Search\ContentType\ContentTypeInterface
	 */
	public function getContentTypeForObject($object)
	{
		return $this->getContentType($this->getContentTypeNameForObject($object));
	}


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
	 * @param string $type_name
	 * @return \Application\DeskPRO\Search\ContentType\ContentTypeInterface
	 */
	public function getContentType($type_name)
	{
		if (isset($this->contenttypes[$type_name])) {
			return $this->contenttypes[$type_name];
		}

		$this->contenttypes[$type_name] = $this->createContentType($type_name);

		return $this->contenttypes[$type_name];
	}

	
	/**
	 * Create a new instance of a contenttype object.
	 *
	 * Factory method.
	 *
	 * @param string $type_name
	 * @return \Application\DeskPRO\Search\ContentType\ContentTypeInterface
	 */
	abstract protected function createContentType($type_name);


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
	 * @param bool $full_info True to return a final array of full info: array('object' => object, 'type' => 'contenttype', 'result' => result)
	 * @return array
	 */
	public function getResultSetObjects(ResultSet $result_set, $full_info = false)
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
				$result_set_typed[$type_name] = array();
			}
			
			$result_set_typed[$type_name][$result->getId()] = $result;
		}

		#------------------------------
		# Get objects for each type
		#------------------------------

		$objects_typed = array();
		foreach ($result_set_typed as $type_name => $results) {

			$type = $this->getContentType($type_name);
			$objects = $type->resultsToObjects($results);

			if ($objects) {
				$objects_typed[$type_name] = $objects;
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

			if ($full_info) {
				$objects[] = array(
					'object' => $objects_typed[$type_name][$obj_id],
					'type'   => $type_name,
					'result' => $result
				);
			} else {
				$objects[] = $objects_typed[$type_name][$obj_id];
			}
		}

		return $objects;
	}


	/**
	 * Update the search index with the specified objects
	 *
	 * @param  $objects
	 * @return void
	 */
	public function updateObjectsInIndex(array $objects)
	{
		$documents = array();

		foreach ($objects as $object) {
			$doc = $this->getContentTypeForObject($object)->objectToDocument($object);
			$documents[] = $doc;
		}

		$this->updateDocumentsInIndex($documents);
	}


	/**
	 * Delete the specified docs from the objects
	 *
	 * @param  $objects
	 * @return void
	 */
	public function deleteObjectsFromIndex(array $objects)
	{
		$documents = array();

		foreach ($objects as $object) {
			$doc = $this->getContentTypeForObject($object)->objectToDocument($object);
			$documents[] = $doc;
		}

		$this->deleteDocumentsFromIndex($documents);
	}


	/**
	 * Update the search index with the specified docs
	 *
	 * @param  $documents
	 * @return void
	 */
	abstract public function updateDocumentsInIndex(array $documents);

	
	/**
	 * Delete the specified docs from the index
	 *
	 * @param  $documents
	 * @return void
	 */
	abstract public function deleteDocumentsFromIndex(array $documents);
}