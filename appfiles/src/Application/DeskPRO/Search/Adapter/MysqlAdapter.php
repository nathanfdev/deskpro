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
use Symfony\Component\DependencyInjection\ContainerInterface;

use Application\DeskPRO\Search\EntityListener;

use Application\DeskPRO\Search\Searcher\Mysql\ContentSearcher;
use Application\DeskPRO\Search\Searcher\Mysql\TicketSearcher;

use Application\DeskPRO\Search\SearcherResult\ResultSet;
use Application\DeskPRO\Search\SearcherResult\ResultInterface;

use Orb\Util\Strings;

/**
 * Search adapter
 */
class MysqlAdapter extends AbstractAdapter
{
	public static $capabilities = array(
		'searcher_content', 'searcher_content_labels',
		'searcher_tickets',
	);

	public function __construct()
	{
		$this->addContentTypeMap('Application\\DeskPRO\\Entity\\Article', 'article');
		$this->addContentTypeMap('Application\\DeskPRO\\Entity\\Download', 'download');
		$this->addContentTypeMap('Application\\DeskPRO\\Entity\\Idea', 'idea');
		$this->addContentTypeMap('Application\\DeskPRO\\Entity\\News', 'news');
		$this->addContentTypeMap('Application\\DeskPRO\\Entity\\Ticket', 'ticket');
		$this->addContentTypeMap('Application\\DeskPRO\\Entity\\TicketMessage', 'ticket_message');
	}


	/**
	 * Delete the specified docs from the index
	 *
	 * @param  $documents
	 * @return void
	 */
	public function deleteDocumentsFromIndex(array $documents)
	{
		foreach ($documents as $doc) {
			App::getDb()->delete('content_search', array(
				'object_type' => $doc->getContentTypeName(),
				'object_id'   => $doc->getId()
			));
		}
	}


	/**
	 * Update the search index with the specified docs
	 *
	 * @param  $documents
	 * @return void
	 */
	public function updateDocumentsInIndex(array $documents)
	{
		foreach ($documents as $doc) {
			$data = $doc->getData();

			App::getDb()->executeUpdate("
				REPLACE INTO content_search
				SET object_type = ?, object_id = ?, content = ?
			", array($doc->getContentTypeName(), $doc->getId(), $data['content']));

			unset($data['content']);

			foreach ($data as $k => $v) {
				App::getDb()->executeUpdate("
					REPLACE INTO content_search_attribute
					SET object_type = ?, object_id = ?, attribute_id = ?, content = ?
				", array($doc->getContentTypeName(), $doc->getId(), $k, $v));
			}
		}
	}


	/**
	 * Create a new instance of a contenttype object.
	 *
	 * Factory method.
	 *
	 * @param string $type_name
	 * @return \Application\DeskPRO\Search\ContentType\ContentTypeInterface
	 */
	protected function createContentType($type_name)
	{
		// All of our contenttype classes are dumb, so they dont need
		// any special initialization. We can simply initialize them with the classname.

		$type_name = str_replace('_', '-', $type_name);
		$type_name = ucfirst(Strings::dashToCamelCase($type_name));

		$classname = 'Application\\DeskPRO\\Search\\ContentType\\Mysql\\' . $type_name;
		$obj = new $classname();

		return $obj;
	}

	/**
	 * Get a ticket searcher.
	 *
	 * Factory method.
	 *
	 * @return \Application\DeskPRO\Search\Searcher\ContentSearcherInterface
	 */
	public function getTicketSearcher()
	{
		$searcher = new ContentSearcher();
		$searcher->setPersonContext($this->getPersonContext());

		return $searcher;
	}

	/**
	 * Get a content searcher.
	 *
	 * Factory method.
	 *
	 * @return \Application\DeskPRO\Search\Searcher\ContentSearcherInterface
	 */
	public function getContentSearcher()
	{
		$searcher = new ContentSearcher();
		$searcher->setPersonContext($this->getPersonContext());

		return $searcher;
	}


	/**
	 * Labels are added to the fulltext index and then fetched with a fulltext match
	 * in "boolean" mode, which is one of the only ways to efficiently fetch labels
	 * with intersections or unions (ie. content with two labels, or with one label but without another).
	 *
	 * But certain words are stripped for stop words, and mysql doesn't handle dashes very well,
	 * and when fetching labels we don't want to confuse them with other words. So
	 * we "encode" them as these hashes, so we can search for "+lbl1232984rf" specifically.
	 *
	 * @param  $label
	 * @return string
	 */
	public static function encodeLabel($label)
	{
		$label = "lbl" . md5(strtolower(trim($label)));
		return $label;
	}
}
