<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage WorkerProcess
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\Logger;

/**
 * This cleans up various temporary data
 */
class SearchIndexUpdate extends AbstractJob
{
	const DEFAULT_INTERVAL = 60; // 1 min

	/**
	 * @var \Application\DeskPRO\Queue\Queue
	 */
	protected $queue;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var \Application\DeskPRO\DBAL\Connection
	 */
	protected $db;

	public function run()
	{
		$this->em = App::getContainer()->getEm();
		$this->db = $this->em->getConnection();
		$this->queue = App::getContainer()->getQueue('search_object_update');

		$batch = $this->queue->receive(20);
		while (count($batch)) {
			$update = array();
			$delete = array();

			foreach ($batch as $info) {
				$op = isset($info->op) ? $info->op : 'update';

				if ($op == 'update') {
					$entity = $this->em->find($info->entity_type, array('id' => $info->id));
					if ($entity) {
						$update[] = $entity;
					}
				} else {
					$doc = new \Application\DeskPRO\Search\Indexer\Document($info->id, $info->entity_type);
					$delete[] = $doc;
				}
				$this->queue->deleteMessage($info);
			}

			if ($update) {
				App::getContainer()->getSearchAdapter()->updateObjectsInIndex($update);
			}
			if ($delete) {
				App::getContainer()->getSearchAdapter()->deleteDocumentsFromIndex($delete);
			}

			$batch = $this->queue->receive(20);
		}
	}
}
