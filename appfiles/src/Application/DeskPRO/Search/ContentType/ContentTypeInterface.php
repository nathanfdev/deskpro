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
 */
interface ContentTypeInterface
{
	/**
	 * Convert a result from a search into the real content object.
	 *
	 * @param \Application\DeskPRO\Search\SearcherResult\ResultInterface $result
	 * @return mixed
	 */
	public function resultToObject(ResultInterface $document);

	
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
	public function resultsToObjects(array $documents);

	
	/**
	 * Transforms an object into a document, suitable for indexing.
	 *
	 * @param mixed $object
	 * @return \Application\DeskPRO\Search\Indexer\DocumentInterface
	 */
	public function objectToDocument($object);

	/**
	 * Get the content ID of an object. This should be like objectToDocument and fetching the ID,
	 * but this is used in cases where we only need the ID.
	 * 
	 * @param  mixed $object
	 * @return mixed
	 */
	public function getObjectId($object);
}