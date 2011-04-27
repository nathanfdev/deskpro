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

namespace Application\DeskPRO\Search\ContentType;

use \Application\DeskPRO\Search\SearcherResult\ResultInterface;
use \Application\DeskPRO\Search\Indexer\DocumentInterface;

/**
 * A content type is a type of indexed content, such as an artile or ticket or ticket message.
 * These are basically just transformers.
 *
 * Note: These classes are lazy-initialized, and are initialized once. If a transformer requires state for some reason,
 * make sure it's able to reset or delegate to some worker class instead.
 */
interface ContentTypeInterface
{
	/**
	 * Convert a result from a search into the real content object.
	 *
	 * @param \Application\DeskPRO\Search\SearcherResult\ResultInterface $result
	 * @return mixed
	 */
	public function resultToObject(ResultInterface $result);

	
	/**
	 * Converts many results of this type into real objects.
	 *
	 * Usually its more efficient to fetch multiple objects at once, so
	 * the implementation of a searcher will usually request many at once.
	 *
	 * IMPORTANT: Array should be keyed by the object ID.
	 *
	 * @param \Application\DeskPRO\Search\SearcherResult\ResultInterface[] $result
	 * @return array
	 */
	public function resultsToObjects(array $results);

	
	/**
	 * Transforms an object into a document, suitable for indexing.
	 *
	 * @param mixed $object
	 * @return \Application\DeskPRO\Search\Indexer\DocumentInterface
	 */
	public function objectToDocument($object);
}