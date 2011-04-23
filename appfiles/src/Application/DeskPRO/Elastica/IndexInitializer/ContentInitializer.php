<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Elastica
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Elastica\IndexInitializer;

use Orb\Log\Logger;
use Application\DeskPRO\App;
use Application\DeskPRO\Elastica\Type\AbstractType;

class ContentInitializer extends AbstractInitializer
{
	public function run()
	{
		#------------------------------
		# Recreate index
		#------------------------------

		$index = $this->manager->getIndex('content');
		try {
			$index->delete();
			$this->logger->log('Deleted old index', Logger::INFO);
		} catch (\Elastica_Exception_Response $e) {
			// probably means it didnt exist to begin with
			$this->logger->log('Exception while deleting old index. Probably can be ignored. Message: ' . $e->getMessage(), Logger::NOTICE);
		}

		try {
			$index->create();
			$this->logger->log('Created index', Logger::INFO);
		} catch (\Elastica_Exception_Response $e) {
			$this->logger->log('Could not create index. Aborting. Error: ' . $e->getMessage(), Logger::ERR);
			return 0;
		}

		#------------------------------
		# Run through each content type
		#------------------------------

		$time_start = microtime(true);
		$total = 0;
		try {
			$total += $this->runForType(App::get('deskpro.elastica.types.article'),  'DeskPRO:Article');
			$total += $this->runForType(App::get('deskpro.elastica.types.download'), 'DeskPRO:Download');
			$total += $this->runForType(App::get('deskpro.elastica.types.idea'),     'DeskPRO:Idea');
			$total += $this->runForType(App::get('deskpro.elastica.types.news'),     'DeskPRO:News');
		} catch (\Exception $e) {
			$this->logger->log('Exception: ' . $e->getMessage(), Logger::ERR);
			throw $e;
		}

		$time = sprintf("%.5f", microtime(true)-$time_start);
		$this->logger->log("Indexed $total items in $time seconds", Logger::INFO);

		return $total;
	}

	public function runForType(AbstractType $type, $entity_name)
	{
		$table_name = App::getOrm()->getClassMetadata($entity_name)->getTableName();

		$count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM $table_name");
		if (!$count) {
			$this->logger->log("No $entity_name objects", Logger::INFO);
		}

		$time_start = microtime(true);
		$this->logger->log("START $entity_name ($count objects)", Logger::INFO);

		$per_page = 25;
		$pages = ceil($count / $per_page);

		for ($i = 0; $i < $pages; $i++) {
			$documents = array();
			$offset = $i * $per_page;

			$objects = App::getOrm()->createQuery("
				SELECT o
				FROM $entity_name o
				ORDER BY o.id
			")->setMaxResults($per_page)->setFirstResult($offset)->execute();

			foreach ($objects as $object) {
				$doc = $type->transformToDocument($object);
				$documents[] = $doc;
			}

			$this->manager->getClient()->addDocuments($documents);
			$this->logger->log("--- Inserted batch $i of $pages", Logger::INFO);
		}

		$time = sprintf("%.5f", microtime(true)-$time_start);
		$this->logger->log("END $entity_name (took $time seconds)", Logger::INFO);

		return $count;
	}
}