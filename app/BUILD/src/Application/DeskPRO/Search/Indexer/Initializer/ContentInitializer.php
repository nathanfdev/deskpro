<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Search\Indexer\Initializer;

use Application\DeskPRO\App;
use Application\DeskPRO\Log\Logger;

abstract class ContentInitializer extends AbstractInitializer
{
    abstract public function preRun();

    public function run()
    {
        $this->preRun();

        //------------------------------
        // Run through each content type
        //------------------------------

        $time_start = microtime(true);
        $total      = 0;
        try {
            $total += $this->runForType('article',  'DeskPRO:Article');
            $total += $this->runForType('download', 'DeskPRO:Download');
            $total += $this->runForType('feedback', 'DeskPRO:Feedback');
            $total += $this->runForType('news',     'DeskPRO:News');
            $total += $this->runForType('topic',    'DeskPRO:Topic');
        } catch (\Exception $e) {
            $this->logger->log('Exception: '.$e->getMessage(), Logger::ERR);
            throw $e;
        }

        $time = sprintf('%.5f', microtime(true) - $time_start);
        $this->logger->log("Indexed $total items in $time seconds", Logger::INFO);

        return $total;
    }

    public function runForType($type_name, $entity_name)
    {
        $table_name = App::getOrm()->getClassMetadata($entity_name)->getTableName();

        $count = App::getDb()->fetchColumn("SELECT COUNT(*) FROM $table_name");
        if (!$count) {
            $this->logger->log("No $entity_name objects", Logger::INFO);
        }

        $time_start = microtime(true);
        $this->logger->log("START $entity_name ($count objects)", Logger::INFO);

        $per_page = 25;
        $pages    = ceil($count / $per_page);

        for ($i = 0; $i < $pages; ++$i) {
            $offset = $i * $per_page;

            $objects = App::getOrm()->createQuery("
                SELECT o
                FROM $entity_name o
                ORDER BY o.id
            ")->setMaxResults($per_page)->setFirstResult($offset)->execute();

            $this->adapter->updateObjectsInIndex($objects);
            $this->logger->log("--- Inserted batch $i of $pages", Logger::INFO);

            App::getOrm()->clear();
        }

        $time = sprintf('%.5f', microtime(true) - $time_start);
        $this->logger->log("END $entity_name (took $time seconds)", Logger::INFO);

        return $count;
    }
}
