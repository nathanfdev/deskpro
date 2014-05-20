<?php

namespace Application\DeskPRO\NewSearch\Provider;

use FOS\ElasticaBundle\Doctrine\ORM\Provider;
use Elastica\Exception\Bulk\ResponseException as BulkResponseException;

/**
 * DeskPRO Doctrine Provider
 *
 * Extends the Doctrine provider to provide a helper method for counting
 * and enables limiting the population process with the help of a new
 * option "limit".
 *
 * Also adds better garbage collection procedure.
 *
 * @package DeskPRO
 */
class Doctrine extends Provider
{
    /**
     * @see FOS\ElasticaBundle\Provider\ProviderInterface::populate()
     */
    public function populate(\Closure $loggerClosure = null, array $options = array())
    {
        $queryBuilder = $this->createQueryBuilder();
        $nbObjects = $this->countObjects($queryBuilder);

        $offset = isset($options['offset']) ? intval($options['offset']) : 0;
        $limit = isset($options['limit']) ? intval($options['limit']) : -1;
        $sleep = isset($options['sleep']) ? intval($options['sleep']) : 0;

        $batchSize = isset($options['batch-size']) ? intval($options['batch-size']) : $this->options['batch_size'];
        $ignoreErrors = isset($options['ignore-errors']) ? $options['ignore-errors'] : $this->options['ignore_errors'];

        if ($limit == -1) {
            $cutoff = $nbObjects;
        } else {
            $cutoff = $limit;
        }

        for (; $offset < $cutoff; $offset += $batchSize) {

            if ($loggerClosure) {
                $stepStartTime = microtime(true);
            }

            $objects = $this->fetchSlice($queryBuilder, $batchSize, $offset);

            if (!$ignoreErrors) {
                $this->objectPersister->insertMany($objects);
            } else {
                try {
                    $this->objectPersister->insertMany($objects);
                } catch(BulkResponseException $e) {
                    if ($loggerClosure) {
                        $loggerClosure(sprintf('<error>%s</error>',$e->getMessage()));
                    }
                }
            }

            if ($loggerClosure) {
                $stepNbObjects = count($objects);
                $stepCount = $stepNbObjects + $offset;
                $percentComplete = 100 * $stepCount / $nbObjects;
                $timeDifference = microtime(true) - $stepStartTime;
                $objectsPerSecond = $timeDifference ? ($stepNbObjects / $timeDifference) : $stepNbObjects;
                $loggerClosure(sprintf('%0.1f%% (%d/%d), %d objects/s %s', $percentComplete, $stepCount, $nbObjects, $objectsPerSecond, $this->getMemoryUsage()));
            }

            if ($this->options['clear_object_manager']) {

                $this->managerRegistry->getManagerForClass($this->objectClass)->clear();
                $this->managerRegistry->getManagerForClass($this->objectClass)->clearRepositoryCache();

                $objects = null;
                $stepCount = null;
                $stepNbObjects = null;
                $percentComplete = null;
                $timeDifference = null;
                $objectsPerSecond = null;

                gc_collect_cycles();
            }

            usleep($sleep);

        }
    }

    public function getCounts()
    {
        return $this->countObjects($this->createQueryBuilder());
    }
} 