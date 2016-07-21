<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\DeskPRO\NewSearch\Provider;

use Elastica\Exception\Bulk\ResponseException as BulkResponseException;
use FOS\ElasticaBundle\Doctrine\ORM\Provider;

/**
 * DeskPRO Doctrine Provider.
 *
 * Extends the Doctrine provider to provide a helper method for counting
 * and enables limiting the population process with the help of a new
 * option "limit".
 *
 * Also adds better garbage collection procedure.
 */
class Doctrine extends Provider
{
    /**
     * @see FOS\ElasticaBundle\Provider\ProviderInterface::populate()
     */
    public function populate(\Closure $loggerClosure = null, array $options = array())
    {
        $queryBuilder = $this->createQueryBuilder('createSearchQueryBuilder');
        $nbObjects    = $this->countObjects($queryBuilder);
        $options      = $this->resolveOptions($options);

        $offset = $options['offset'];
        $limit  = $options['limit'];
        $sleep  = $options['sleep'];

        $batchSize    = $options['batch_size'];
        $ignoreErrors = $options['ignore_errors'];

        if ($limit == -1) {
            $cutoff = $nbObjects;
        } else {
            $cutoff = $limit;
        }

        for (; $offset < $cutoff; $offset += $batchSize) {
            if ($loggerClosure) {
                $stepStartTime = microtime(true);
            }

            $objects       = $this->fetchSlice($queryBuilder, $batchSize, $offset);
            $stepNbObjects = count($objects);

            if (!$ignoreErrors) {
                $this->objectPersister->insertMany($objects);
            } else {
                try {
                    $this->objectPersister->insertMany($objects);
                } catch (BulkResponseException $e) {
                    if ($loggerClosure) {
                        // function ($increment, $totalObjects, $message = null)
                        $loggerClosure($stepNbObjects, $nbObjects, sprintf('<error>%s</error>', $e->getMessage()));
                    }
                }
            }

            if ($loggerClosure) {
                // function ($increment, $totalObjects, $message = null)
                $loggerClosure($stepNbObjects, $nbObjects, '');
            }

            if ($options['clear_object_manager']) {
                $this->managerRegistry->getManagerForClass($this->objectClass)->clear();

                $objects          = null;
                $stepCount        = null;
                $stepNbObjects    = null;
                $percentComplete  = null;
                $timeDifference   = null;
                $objectsPerSecond = null;

                gc_collect_cycles();
            }

            usleep($sleep);
        }
    }

    protected function configureOptions()
    {
        parent::configureOptions();

        $this->resolver->setDefaults(array(
            'limit' => -1,
        ));
    }

    public function getCounts()
    {
        return $this->countObjects($this->createQueryBuilder('createSearchQueryBuilder'));
    }
}
