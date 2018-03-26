<?php

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
    protected function doPopulate($options, \Closure $loggerClosure = null)
    {
        $manager = $this->managerRegistry->getManagerForClass($this->objectClass);

        $queryBuilder = $this->createQueryBuilder($options['query_builder_method']);
        $nbObjects    = $this->countObjects($queryBuilder);
        $offset       = $options['offset'];

        for (; $offset < $nbObjects; $offset += $options['batch_size']) {
            $sliceSize = $options['batch_size'];
            try {
                $objects   = $this->fetchSlice($queryBuilder, $options['batch_size'], $offset);
                $sliceSize = count($objects);
                $objects   = $this->filterObjects($options, $objects);

                if (!empty($objects)) {
                    $this->objectPersister->insertMany($objects);
                }
            } catch (BulkResponseException $e) {
                if (!$options['ignore_errors']) {
                    throw $e;
                }

                if (null !== $loggerClosure) {
                    $loggerClosure(
                        $options['batch_size'],
                        $nbObjects,
                        sprintf('<error>%s</error>', $e->getMessage())
                    );
                }
            }

            if ($options['clear_object_manager']) {
                $manager->clear();
            }

            usleep($options['sleep']);

            if (null !== $loggerClosure) {
                $loggerClosure($sliceSize, $nbObjects);
            }

            if ($options['single_batch']) {
                break;
            }
        }
    }

    protected function configureOptions()
    {
        parent::configureOptions();

        $this->resolver->setDefaults([
            'single_batch' => false,
        ]);
    }

    public function getCounts()
    {
        return $this->countObjects($this->createQueryBuilder('createSearchQueryBuilder'));
    }
}
