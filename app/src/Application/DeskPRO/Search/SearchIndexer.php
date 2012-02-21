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

use Application\DeskPRO\App;
use Doctrine\ORM\EntityManager;
use Application\DeskPRO\Queue\Queue;

use Orb\Util\Arrays;

/**
 * When something needs to be indexed, index it through this
 */
class SearchIndexer
{
	/**
	 * Entity manager
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * Plain database connection for raw queries
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	/**
	 * @var \Application\DeskPRO\Queue\Queue
	 */
	protected $queue;

	public function __construct(EntityManager $em, Queue $queue)
	{
		$this->em = $em;
		$this->db = $em->getConnection();
		$this->queue = $queue;
	}

	public function update($object, $op = 'update')
	{
		// These flags are used in the importer
		if (isset($GLOBALS['DP_INDEX_REALTIME'])) {
			$this->updateNow($object, $content_type, $op);
			return;
		}
		if (isset($GLOBALS['DP_INDEX_NOINDEX'])) {
			return;
		}

		$content_type = App::getContainer()->getSearchAdapter()->getContentTypeForObject($object);

		$queue->send(array('entity_type' => $content_type, 'id' => $object->getId(), 'op' => $op));
	}

	public function updateNow($object, $op = 'update')
	{
		if ($op == 'update') {
			App::getContainer()->getSearchAdapter()->updateObjectsInIndex(array($object));
		} else {
			App::getContainer()->getSearchAdapter()->deleteObjectsInIndex(array($object));
		}
	}
}
